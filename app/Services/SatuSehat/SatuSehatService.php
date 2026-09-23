<?php

namespace App\Services\SatuSehat;

use App\Models\RadiologyOrder;
use App\Models\SatuSehatCredential;
use App\Models\SatuSehatSyncLog;
use App\Models\Visit;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SatuSehatService extends Service
{
    /**
     * Konfigurasi runtime aktif (Fase 2.1): di-resolve dari kredensial cabang
     * visit yang disinkronkan; fallback ke .env global bila cabang belum punya.
     *
     * @var array{client_id: string, client_secret: string, base_url: string, auth_url: string, org_id: ?string}|null
     */
    private ?array $runtime = null;

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

    /**
     * Kredensial efektif untuk satu cabang: baris satusehat_credentials aktif,
     * atau konfigurasi .env global bila cabang belum punya kredensial sendiri.
     */
    public function resolveConfig(?string $branchId): array
    {
        $credential = SatuSehatCredential::forBranch($branchId);
        if ($credential) {
            $urls = SatuSehatCredential::BASE_URLS[$credential->environment] ?? SatuSehatCredential::BASE_URLS[SatuSehatCredential::ENV_SANDBOX];

            return [
                'client_id' => $credential->client_id,
                'client_secret' => $credential->client_secret,
                'base_url' => $urls['base'],
                'auth_url' => $urls['auth'],
                'org_id' => $credential->organization_id,
            ];
        }

        return [
            'client_id' => (string) config('satusehat.client_id'),
            'client_secret' => (string) config('satusehat.client_secret'),
            'base_url' => (string) config('satusehat.base_url'),
            'auth_url' => (string) config('satusehat.auth_url'),
            'org_id' => config('satusehat.org_id') ?: null,
        ];
    }

    public function token(): string
    {
        $config = $this->runtime ?? $this->resolveConfig(null);
        if (! $config['client_id'] || ! $config['client_secret']) {
            throw new Exception('SATUSEHAT belum dikonfigurasi (isi kredensial sandbox dulu).', 422);
        }

        // Token dikunci per client_id: kredensial cabang berbeda tidak saling
        // menimpa cache (2 cabang = 2 token berjalan paralel).
        return Cache::remember('satusehat:token:'.$config['client_id'], 50 * 60, function () use ($config) {
            $response = Http::asForm()->post($config['auth_url'].'/accesstoken', [
                'grant_type' => 'client_credentials',
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
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

        // Fase 2.1: kredensial & base URL mengikuti cabang visit, bukan .env global.
        $this->runtime = $this->resolveConfig($visit->branch_id);

        $visit->loadMissing(['patient', 'patient.address', 'doctor', 'diagnoses', 'treatments']);
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

        // 2. Encounter (identifier pakai org IHS dari kredensial cabang bila ada).
        $encounterPayload = SatuSehatPayload::encounter(
            $visit,
            $patientIhsId,
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null),
            $this->runtime['org_id'] ?? null
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
        foreach ($bpPayloads ?? [] as $payload) {
            $this->tally($summary, $this->post('Observation', $payload, $visit, 'bp-'.$this->measurementKey($payload)));
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
        foreach ($vitalPayloads as $payload) {
            $this->tally($summary, $this->post('Observation', $payload, $visit, 'vs-'.$this->measurementKey($payload)));
        }

        // 5d. Odontogram per gigi: Observation OC000061 + bodySite FDI + komponen.
        $visit->loadMissing(['odontogramFindings', 'oralHealthIndex']);
        $dentalPayloads = SatuSehatDental::odontogramObservations(
            $visit->odontogramFindings,
            $patientIhsId,
            $encounterResult['external_id'],
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null),
            (string) $visit->visit_date
        );
        foreach ($dentalPayloads as $payload) {
            $this->tally($summary, $this->post('Observation', $payload, $visit, 'odontogram-'.$this->measurementKey($payload)));
        }

        // 5e. OHI-S + hitung gigi D/M/F (decayed/missing/filled).
        $ohiPayloads = SatuSehatDental::oralHealthObservations(
            $visit->oralHealthIndex,
            $patientIhsId,
            $encounterResult['external_id'],
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null),
            (string) $visit->visit_date
        );
        foreach ($ohiPayloads as $payload) {
            $this->tally($summary, $this->post('Observation', $payload, $visit, 'ohis-'.$this->measurementKey($payload)));
        }

        // 5f. Oklusi/torus/palatum/diastema/relasi sentral (Fase 3.2).
        if ($oralExam = SatuSehatDental::oralExamObservation(
            $visit->examination,
            $patientIhsId,
            $encounterResult['external_id'],
            SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null),
            (string) $visit->visit_date
        )) {
            $this->tally($summary, $this->post('Observation', $oralExam, $visit, 'oral-exam'));
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

        // 5g. AllergyIntolerance dari anamnesis (bila pasien punya alergi).
        $visit->loadMissing('anamnesis');
        if ($visit->anamnesis && ($allergyPayload = SatuSehatDental::allergyIntolerance($visit->anamnesis, $patientIhsId))) {
            $this->tally($summary, $this->post('AllergyIntolerance', $allergyPayload, $visit, 'allergy-'.$visit->anamnesis->id));
        }

        // 6. ServiceRequest → Media → DiagnosticReport per order radiologi
        // COMPLETED (Fase 3.3; ServiceRequest prasyarat report di SSP).
        $visit->loadMissing('radiologyOrders');
        foreach ($visit->radiologyOrders->where('status', RadiologyOrder::STATUS_COMPLETED) as $order) {
            $serviceRequestResult = $this->post(
                'ServiceRequest',
                SatuSehatDental::serviceRequest($order, $patientIhsId, $encounterResult['external_id'], SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null)),
                $visit,
                'sr-'.$order->id
            );
            $this->tally($summary, $serviceRequestResult);

            $reportPayload = SatuSehatDental::diagnosticReport(
                $order,
                $patientIhsId,
                $encounterResult['external_id'],
                SatuSehatPayload::practitionerRef($visit->doctor->ihs_id ?? null)
            );
            // Report berbasis order: rujuk ServiceRequest yang barusan terbit.
            if ($reportPayload && ! empty($serviceRequestResult['external_id'])) {
                $reportPayload['basedOn'] = [['reference' => 'ServiceRequest/'.$serviceRequestResult['external_id']]];
            }
            if (! $reportPayload) {
                $this->log($visit, 'DiagnosticReport', $order->id, null, SatuSehatSyncLog::STATUS_SKIPPED, 'Hasil baca (result_text) belum ada.');
                $summary['skipped']++;

                continue;
            }

            // Media dikirim dulu agar DiagnosticReport bisa merujuk id-nya.
            $mediaId = null;
            if ($order->result_path && Storage::disk('local')->exists($order->result_path)) {
                $mime = match (pathinfo($order->result_path, PATHINFO_EXTENSION)) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    'dcm' => null, // DICOM tidak dikirim
                    default => 'image/png',
                };
                if ($mime) {
                    $mediaResult = $this->post('Media', SatuSehatDental::mediaPayload(
                        $order,
                        $patientIhsId,
                        $encounterResult['external_id'],
                        Storage::disk('local')->get($order->result_path),
                        $mime
                    ), $visit, 'media-'.$order->id);
                    $this->tally($summary, $mediaResult);
                    $mediaId = $mediaResult['external_id'] ?? null;
                }
            }

            $reportResult = $this->post('DiagnosticReport', $reportPayload, $visit, $order->id);
            $this->tally($summary, $reportResult);
            if ($reportResult['status'] === SatuSehatSyncLog::STATUS_SUCCESS) {
                $order->update(['satusehat_diagnostic_report_id' => $reportResult['external_id']]);
            }
        }

        // 7. Lampirkan daftar diagnosis ke Encounter (diagnosis.condition).
        $this->updateEncounterDiagnoses($visit, $encounterResult['external_id'], $summary);

        return $summary;
    }

    /**
     * Update Encounter dengan daftar Condition yang sudah terbit.
     * Gagal update tidak menggagalkan sinkron utama — hanya tercatat FAILED.
     * Idempoten: daftar identik dengan kiriman sukses terakhir tidak dikirim ulang.
     */
    private function updateEncounterDiagnoses(Visit $visit, string $encounterId, array &$summary): void
    {
        // Referensi diambil dari baris log yang sukses; satu Condition bisa punya
        // beberapa baris log sukses, jadi id-nya wajib di-unique-kan.
        $conditionIds = SatuSehatSyncLog::query()
            ->where('visit_id', $visit->id)
            ->where('resource_type', 'Condition')
            ->where('status', SatuSehatSyncLog::STATUS_SUCCESS)
            ->orderBy('created_at')
            ->pluck('external_id')
            ->filter()
            ->unique()
            ->values();

        if ($conditionIds->isEmpty()) {
            return;
        }

        $body = [
            'diagnosis' => $conditionIds
                ->map(fn ($id) => ['condition' => ['reference' => 'Condition/'.$id], 'use' => ['coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/diagnosis-role',
                    'code' => 'AD',
                    'display' => 'Admission diagnosis',
                ]]]])
                ->all(),
        ];

        $localId = $visit->id.'-diagnosis';
        $log = $this->existingLog($visit, 'Encounter', $localId)
            ?? $this->log($visit, 'Encounter', $localId, null, SatuSehatSyncLog::STATUS_PENDING, null);

        if ($log->status === SatuSehatSyncLog::STATUS_SUCCESS && $log->request == $body) {
            $summary['success']++;

            return;
        }

        $log->update(['request' => $body, 'status' => SatuSehatSyncLog::STATUS_PENDING, 'error' => null]);

        try {
            $response = Http::withToken($this->token())
                ->timeout(30)
                ->put(($this->runtime['base_url'] ?? config('satusehat.base_url')).'/fhir-r4/v1/Encounter/'.$encounterId, $body);

            $log->increment('attempts');
            if ($response->successful()) {
                $log->update([
                    'status' => SatuSehatSyncLog::STATUS_SUCCESS,
                    'external_id' => $encounterId,
                    'response' => $response->json(),
                ]);
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
        $key = match ($result['status']) {
            SatuSehatSyncLog::STATUS_SUCCESS => 'success',
            // Ditunda karena rate-limit — bukan gagal; diulang lewat retry.
            SatuSehatSyncLog::STATUS_PENDING => 'skipped',
            default => 'failed',
        };
        $summary[$key]++;

        // Backoff: SSP meminta jeda sebelum request berikutnya.
        if (! empty($result['retry_after'])) {
            sleep(min((int) $result['retry_after'], 15));
        }
    }

    /**
     * Kirim satu resource secara idempoten.
     *
     * Satu baris log = satu resource di SSP, dipakai ulang antar percobaan
     * (kolom `attempts` mencatat berapa kali dicoba). Sinkron ulang karena itu aman:
     * - isi identik dengan kiriman sukses → tidak ada HTTP sama sekali;
     * - isi berubah setelah sukses → PUT ke resource yang sama, bukan POST baru;
     * - percobaan sebelumnya gagal → baris yang sama dikirim ulang (tidak menumpuk log).
     */
    private function post(string $resource, array $payload, Visit $visit, ?string $localId): array
    {
        $log = $this->existingLog($visit, $resource, $localId)
            ?? $this->log($visit, $resource, $localId, null, SatuSehatSyncLog::STATUS_PENDING, null);

        if ($log->status === SatuSehatSyncLog::STATUS_SUCCESS && $log->external_id && $log->request == $payload) {
            return ['status' => SatuSehatSyncLog::STATUS_SUCCESS, 'external_id' => $log->external_id];
        }

        $existingId = $log->status === SatuSehatSyncLog::STATUS_SUCCESS ? $log->external_id : null;
        $log->update(['request' => $payload, 'status' => SatuSehatSyncLog::STATUS_PENDING, 'error' => null]);

        try {
            $url = ($this->runtime['base_url'] ?? config('satusehat.base_url')).'/fhir-r4/v1/'.$resource.($existingId ? '/'.$existingId : '');
            $client = Http::withToken($this->token())->timeout(30);
            $response = $existingId ? $client->put($url, $payload) : $client->post($url, $payload);

            $log->increment('attempts');
            if ($response->successful()) {
                $externalId = $response->json('id') ?? $existingId;
                $log->update([
                    'status' => SatuSehatSyncLog::STATUS_SUCCESS,
                    'external_id' => $externalId,
                    'response' => $response->json(),
                ]);

                return ['status' => SatuSehatSyncLog::STATUS_SUCCESS, 'external_id' => $externalId];
            }

            // Fase 2.2: rate-limit SSP — 429 ditunda ulang dengan backoff,
            // bukan gagal permanen; resource berikutnya menunggu lebih lama.
            if ($response->status() === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?: 10);
                $log->update(['status' => SatuSehatSyncLog::STATUS_PENDING, 'error' => 'HTTP 429 rate-limited, dicoba ulang otomatis.']);

                return ['status' => SatuSehatSyncLog::STATUS_PENDING, 'external_id' => null, 'retry_after' => $retryAfter];
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

    /** Baris log milik satu resource (data lokal) pada visit ini, bila sudah ada. */
    private function existingLog(Visit $visit, string $resource, ?string $localId): ?SatuSehatSyncLog
    {
        if ($localId === null) {
            return null;
        }

        return SatuSehatSyncLog::query()
            ->where('visit_id', $visit->id)
            ->where('resource_type', $resource)
            ->where('local_id', $localId)
            ->first();
    }

    /**
     * Kunci identitas local untuk resource hasil pengukuran: kode pengukuran
     * + lokasi tubuh (FDI) agar satu gigi satu Observation yang sama saat diubah.
     */
    private function measurementKey(array $payload): string
    {
        $code = $payload['code']['coding'][0]['code'] ?? 'observation';
        $site = $payload['bodySite']['coding'][0]['code'] ?? null;

        return $site === null ? $code : $code.'-'.$site;
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
