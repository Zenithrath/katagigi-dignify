<?php

namespace App\Services\SatuSehat;

use App\Models\SatuSehatSyncLog;
use App\Models\Visit;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SatuSehatService extends Service
{
    public function isEnabled(): bool
    {
        return (bool) config('satusehat.enabled')
            && config('satusehat.client_id')
            && config('satusehat.client_secret');
    }

    public function ensureEnabled(): void
    {
        if (! $this->isEnabled()) {
            throw new Exception('SATUSEHAT belum dikonfigurasi (isi kredensial sandbox dulu).', 422);
        }
    }

    public function token(): string
    {
        $this->ensureEnabled();

        return Cache::remember('satusehat:token', 50 * 60, function () {
            $response = Http::asForm()->post(config('satusehat.auth_url').'/accesstoken', [
                'grant_type' => 'client_credentials',
                'client_id' => config('satusehat.client_id'),
                'client_secret' => config('satusehat.client_secret'),
            ]);

            if (! $response->successful()) {
                throw new Exception('Gagal autentikasi SATUSEHAT: '.$response->status(), 500);
            }

            return $response->json('access_token');
        });
    }

    /**
     * Sinkron satu visit SIGNED: Patient → Encounter → Condition* → Procedure*.
     * Selalu mencatat ke satusehat_sync_logs (SUCCESS/FAILED/SKIPPED).
     */
    public function syncVisit(Visit $visit): array
    {
        $this->ensureEnabled();

        $visit->loadMissing(['patient', 'doctor', 'diagnoses', 'treatments']);
        $summary = ['success' => 0, 'failed' => 0, 'skipped' => 0];

        // 1. Patient (butuh IHS dulu untuk referensi resource lain).
        // Consent: UU PDP — tanpa persetujuan pasien data tidak boleh dikirim.
        if (empty($visit->patient->satusehat_consent)) {
            $this->log($visit, 'Patient', $visit->patient_id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'Pasien belum memberikan persetujuan SATUSEHAT (consent).');
            $summary['skipped']++;

            return $summary;
        }
        $patientPayload = SatuSehatPayload::patient($visit->patient);
        $patientIhsId = $visit->patient->ihs_id;
        if (! $patientPayload) {
            $this->log($visit, 'Patient', $visit->patient_id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'Data pasien belum lengkap (NIK 16 digit + nama + tanggal lahir).');
            $summary['skipped']++;

            return $summary;
        }
        $patientResult = $this->post('Patient', $patientPayload, $visit, $visit->patient_id);
        $this->tally($summary, $patientResult);
        $patientIhsId = $patientResult['external_id'] ?? $patientIhsId;
        if (! $patientIhsId) {
            return $summary;
        }
        // MPI: simpan IHS yang dikembalikan server agar sinkron berikutnya
        // merujuk ID nasional yang sama (bukan buat pasien baru).
        if ($patientResult['status'] === SatuSehatSyncLog::STATUS_SUCCESS && $visit->patient->ihs_id !== $patientIhsId) {
            $visit->patient->update(['ihs_id' => $patientIhsId]);
        }

        // 2. Encounter.
        $encounterPayload = SatuSehatPayload::encounter(
            $visit,
            $patientIhsId,
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null)
        );
        if (! $encounterPayload) {
            $this->log($visit, 'Encounter', $visit->id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'IHS pasien belum ada.');
            $summary['skipped']++;

            return $summary;
        }
        $encounterResult = $this->post('Encounter', $encounterPayload, $visit, $visit->id);
        $this->tally($summary, $encounterResult);
        if (empty($encounterResult['external_id'])) {
            return $summary;
        }

        // 3. Condition per diagnosis ICD-10.
        foreach ($visit->diagnoses as $diagnosis) {
            $payload = SatuSehatPayload::condition($diagnosis, $patientIhsId, $encounterResult['external_id']);
            if (! $payload) {
                $this->log($visit, 'Condition', $diagnosis->id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'Bukan ICD-10.');
                $summary['skipped']++;

                continue;
            }
            $this->tally($summary, $this->post('Condition', $payload, $visit, $diagnosis->id));
        }

        // 4. Procedure per tindakan ICD-9.
        foreach ($visit->treatments as $treatment) {
            $payload = SatuSehatPayload::procedure($treatment, $patientIhsId, $encounterResult['external_id'], (string) $visit->visit_date);
            if (! $payload) {
                $this->log($visit, 'Procedure', $treatment->id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'Bukan ICD-9.');
                $summary['skipped']++;

                continue;
            }
            $this->tally($summary, $this->post('Procedure', $payload, $visit, $treatment->id));
        }

        // 5. Observation tekanan darah (LOINC 8480-6 / 8462-4).
        $bpPayloads = SatuSehatPayload::bloodPressureObservations(
            $visit->examination?->blood_pressure,
            $patientIhsId,
            $encounterResult['external_id'],
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null),
            (string) $visit->visit_date
        );
        foreach ($bpPayloads ?? [] as $i => $payload) {
            $localId = $visit->examination?->id.'-bp-'.($i + 1);
            $this->tally($summary, $this->post('Observation', $payload, $visit, $localId));
        }

        // 5b. Tanda vital lain (pulse / suhu / RR / kehamilan) — tabel vital_signs.
        $visit->loadMissing('vitalSign');
        $vitalPayloads = SatuSehatPayload::vitalSignObservations(
            $visit->vitalSign,
            $patientIhsId,
            $encounterResult['external_id'],
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null),
            (string) $visit->visit_date
        );
        foreach ($vitalPayloads as $i => $payload) {
            $this->tally($summary, $this->post('Observation', $payload, $visit, $visit->vitalSign?->id.'-vs-'.($i + 1)));
        }

        // 5c. MedicationRequest per resep.
        $visit->loadMissing('prescriptions.items');
        foreach ($visit->prescriptions as $prescription) {
            $payload = SatuSehatPayload::medicationRequest(
                $prescription,
                $patientIhsId,
                $encounterResult['external_id'],
                SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null)
            );
            if (! $payload) {
                $this->log($visit, 'MedicationRequest', $prescription->id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'Item resep kosong / referensi belum lengkap.');
                $summary['skipped']++;

                continue;
            }
            $this->tally($summary, $this->post('MedicationRequest', $payload, $visit, $prescription->id));
        }

        // 6. Lampirkan daftar diagnosis ke Encounter (diagnosis.condition).
        $this->updateEncounterDiagnoses($visit, $encounterResult['external_id'], $summary);

        return $summary;
    }

    /**
     * Update Encounter dengan daftar Condition yang sudah terbit.
     * Gagal update tidak menggagalkan sinkron utama — hanya tercatat FAILED.
     */
    private function updateEncounterDiagnoses(Visit $visit, string $encounterId, array &$summary): void
    {
        $conditionIds = SatuSehatSyncLog::query()
            ->where('visit_id', $visit->id)
            ->where('resource_type', 'Condition')
            ->where('status', SatuSehatSyncLog::STATUS_SUCCESS)
            ->orderBy('created_at')
            ->pluck('external_id')
            ->filter()
            ->values();

        if ($conditionIds->isEmpty()) {
            return;
        }

        $log = $this->log($visit, 'Encounter', $visit->id, null, SatuSehatSyncLog::STATUS_PENDING, null);
        try {
            $response = Http::withToken($this->token())
                ->timeout(30)
                ->put(config('satusehat.base_url').'/fhir-r4/v1/Encounter/'.$encounterId, [
                    'diagnosis' => $conditionIds
                        ->map(fn ($id) => ['condition' => ['reference' => 'Condition/'.$id], 'use' => ['coding' => [[
                            'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                            'code' => 'AD',
                            'display' => 'Admission diagnosis',
                        ]]]])
                        ->all(),
                ]);

            $log->increment('attempts');
            if ($response->successful()) {
                $log->update(['status' => SatuSehatSyncLog::STATUS_SUCCESS, 'response' => $response->json()]);
                $summary['success']++;
            } else {
                $log->update(['status' => SatuSehatSyncLog::STATUS_FAILED, 'response' => $response->json(), 'error' => 'HTTP '.$response->status()]);
                $summary['failed']++;
            }
        } catch (Throwable $th) {
            $this->writeLog('SatuSehatService::updateEncounterDiagnoses', $th);
            $log->update(['status' => SatuSehatSyncLog::STATUS_FAILED, 'error' => $th->getMessage()]);
            $summary['failed']++;
        }
    }

    private function tally(array &$summary, array $result): void
    {
        $summary[$result['status'] === SatuSehatSyncLog::STATUS_SUCCESS ? 'success' : 'failed']++;
    }

    private function post(string $resource, array $payload, Visit $visit, ?string $localId): array
    {
        $log = $this->log($visit, $resource, $localId, $payload, SatuSehatSyncLog::STATUS_PENDING, null);

        try {
            $response = Http::withToken($this->token())
                ->timeout(30)
                ->post(config('satusehat.base_url').'/fhir-r4/v1/'.$resource, $payload);

            $log->increment('attempts');
            if ($response->successful()) {
                $externalId = $response->json('id');
                $log->update([
                    'status' => SatuSehatSyncLog::STATUS_SUCCESS,
                    'external_id' => $externalId,
                    'response' => $response->json(),
                ]);

                return ['status' => SatuSehatSyncLog::STATUS_SUCCESS, 'external_id' => $externalId];
            }

            $log->update([
                'status' => SatuSehatSyncLog::STATUS_FAILED,
                'response' => $response->json(),
                'error' => 'HTTP '.$response->status(),
            ]);

            return ['status' => SatuSehatSyncLog::STATUS_FAILED, 'external_id' => null];
        } catch (Throwable $th) {
            $this->writeLog('SatuSehatService::post', $th);
            $log->update([
                'status' => SatuSehatSyncLog::STATUS_FAILED,
                'error' => $th->getMessage(),
            ]);

            return ['status' => SatuSehatSyncLog::STATUS_FAILED, 'external_id' => null];
        }
    }

    private function log(Visit $visit, string $resource, ?string $localId, ?array $payload, string $status, ?string $error): SatuSehatSyncLog
    {
        return SatuSehatSyncLog::create([
            'id' => (string) Str::uuid(),
            'patient_id' => $visit->patient_id,
            'visit_id' => $visit->id,
            'resource_type' => $resource,
            'local_id' => $localId,
            'status' => $status,
            'request' => $payload,
            'error' => $error,
            'attempts' => 0,
        ]);
    }
}
