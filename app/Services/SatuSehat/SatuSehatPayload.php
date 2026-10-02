<?php

namespace App\Services\SatuSehat;

use Carbon\Carbon;

/**
 * Penyusun payload FHIR R4 SATUSEHAT untuk layanan gigi.
 * Mengembalikan null bila data belum memenuhi syarat wajib —
 * pemanggil mencatat SKIPPED beserta alasannya (kesiapan Fase 1).
 *
 * Sistem identifier mengikuti dokumentasi resmi SATUSEHAT:
 * - NIK   : https://fhir.kemkes.go.id/id/nik
 * - IHS   : https://fhir.kemkes.go.id/id/patient-ihs-number
 * - Encounter lokal : http://sys-ids.kemkes.go.id/encounter/{org-ihs}
 * - Observation vital : LOINC 8480-6 (sistolik) / 8462-4 (diastolik)
 */
class SatuSehatPayload
{
    public const SYSTEM_NIK = 'https://fhir.kemkes.go.id/id/nik';

    public const SYSTEM_PATIENT_IHS = 'https://fhir.kemkes.go.id/id/patient-ihs-number';

    public const SYSTEM_ICD10 = 'http://hl7.org/fhir/sid/icd-10';

    public const SYSTEM_ICD9CM = 'http://hl7.org/fhir/sid/icd-9-cm';

    public const SYSTEM_SNOMED_TOOTH = 'http://snomed.info/sct';

    public const SYSTEM_LOINC = 'http://loinc.org';

    public static function patient(object $patient): ?array
    {
        if (empty($patient->nik) || strlen((string) $patient->nik) !== 16) {
            return null;
        }
        if (empty($patient->name)) {
            return null;
        }
        try {
            $birthDate = Carbon::parse($patient->birthdate)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }

        $payload = [
            'resourceType' => 'Patient',
            'identifier' => [
                [
                    'use' => 'official',
                    'system' => self::SYSTEM_NIK,
                    'value' => (string) $patient->nik,
                ],
            ],
            'active' => true,
            'name' => [['use' => 'official', 'text' => $patient->name]],
            'gender' => ($patient->gender ?? 'MALE') === 'FEMALE' ? 'female' : 'male',
            'birthDate' => $birthDate,
            // Mandatory per docs SSP: kelahiran tunggal default.
            'multipleBirthBoolean' => false,
            // Mandatory per docs SSP: bahasa komunikasi pasien.
            'communication' => [[
                'language' => ['coding' => [[
                    'system' => 'urn:ietf:bcp:47',
                    'code' => 'id-ID',
                    'display' => 'Indonesian',
                ]]],
                'preferred' => true,
            ]],
        ];

        // Catatan: IHS TIDAK dikirim dalam identifier. POST Patient dengan NIK
        // yang sudah terdaftar di MPI mengembalikan IHS yang sama (idempoten);
        // menambahkan identifier IHS justru mengubah payload antar percobaan
        // dan memicu PUT tidak perlu.

        if (! empty($patient->phone)) {
            $payload['telecom'] = [[
                'system' => 'phone',
                'value' => $patient->phone,
                'use' => 'mobile',
            ]];
        }

        // Status perkawinan (v3-MaritalStatus) bila tercatat.
        if (! empty($patient->marital_status)) {
            $maritalLabels = ['S' => 'Never Married', 'M' => 'Married', 'W' => 'Separated', 'D' => 'Widowed'];
            $payload['maritalStatus'] = ['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-MaritalStatus',
                'code' => $patient->marital_status,
                'display' => $maritalLabels[$patient->marital_status] ?? $patient->marital_status,
            ]]];
        }

        if (! empty($patient->birth_place)) {
            $payload['extension'] = [[
                'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/birthPlace',
                'valueAddress' => [
                    'city' => $patient->birth_place,
                    'country' => 'ID',
                ],
            ]];
        }

        // Fase 4.1: Address dengan kode wilayah Kemendagri (bila terisi).
        if ($address = $patient->address ?? null) {
            $payload['address'] = [self::address($address)];
        }

        return $payload;
    }

    public static function practitionerRef(?string $ihsId): ?string
    {
        return $ihsId ? 'Practitioner/'.$ihsId : null;
    }

    /**
     * Address FHIR sesuai panduan SATUSEHAT: extension administrativeCode
     * berupa extension NESTED berisi kode Kemendagri per level wilayah
     * (province/city/district/village + rt/rw opsional).
     */
    public static function address(object $address): array
    {
        $payload = array_filter([
            'use' => 'home',
            'line' => [trim(($address->street ?? '').' '.($address->tonarigumi ?? ''))] ?: null,
            'city' => $address->regency ?? null,
            'district' => $address->district ?? null,
            'state' => $address->province ?? null,
            'postalCode' => $address->zip_code ?? null,
            'country' => 'ID',
        ], fn ($v) => $v !== null && $v !== []);

        // Kode Kemendagri: region_code bisa berisi kode level apa pun —
        // dipetakan ke level sesuai panjang digit (2/4/6/10).
        $code = trim((string) ($address->region_code ?? ''));
        if ($code !== '') {
            $level = match (strlen($code)) {
                2 => 'province',
                4 => 'city',
                6 => 'district',
                default => 'village',
            };
            $nested = collect([
                'province' => strlen($code) >= 2 ? substr($code, 0, 2) : null,
                'city' => strlen($code) >= 4 ? substr($code, 0, 4) : null,
                'district' => strlen($code) >= 6 ? substr($code, 0, 6) : null,
                'village' => strlen($code) >= 10 ? $code : null,
            ])->filter()
                ->map(fn ($value, $url) => ['url' => $url, 'valueCode' => (string) $value])
                ->values()
                ->all();

            $payload['extension'] = [[
                'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode',
                'extension' => $nested,
            ]];
        }

        return $payload;
    }

    /**
     * Identifier Encounter memakai system resmi per organisasi:
     * http://sys-ids.kemkes.go.id/encounter/{organization-ihs-number}
     * dengan value = nomor visit lokal.
     * Mandatory per docs SSP: statusHistory (arrived→finished) + classHistory.
     */
    public static function encounter(object $visit, string $patientIhsId, ?string $practitionerRef, ?string $orgId = null): ?array
    {
        if (! $patientIhsId) {
            return null;
        }

        $start = Carbon::parse($visit->visit_date)->setTimezone('+00:00')->toIso8601String();
        $end = Carbon::parse($visit->visit_date)->endOfDay()->setTimezone('+00:00')->toIso8601String();

        $payload = [
            'resourceType' => 'Encounter',
            'status' => 'finished',
            // Mandatory: riwayat status arrived → in-progress → finished.
            'statusHistory' => [
                [
                    'status' => 'arrived',
                    'period' => ['start' => $start, 'end' => $start],
                ],
                [
                    'status' => 'in-progress',
                    'period' => ['start' => $start, 'end' => $end],
                ],
                [
                    'status' => 'finished',
                    'period' => ['start' => $end, 'end' => $end],
                ],
            ],
            'class' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'AMB',
                'display' => 'ambulatory',
            ],
            // Mandatory: riwayat klasifikasi kunjungan.
            'classHistory' => [
                [
                    'class' => [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                        'code' => 'AMB',
                        'display' => 'ambulatory',
                    ],
                    'period' => ['start' => $start, 'end' => $end],
                ],
            ],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'participant' => [
                ['type' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                    'code' => 'ATND',
                    'display' => 'attender',
                ]]],
            ],
            'period' => ['start' => $start, 'end' => $end],
        ];

        if ($practitionerRef) {
            $payload['participant'][0]['individual'] = ['reference' => $practitionerRef];
        }

        // serviceType rawat jalan gigi (SNOMED 84687003 dental care, opsional
        // tapi memberi konteks layanan gigi pada Encounter).
        $payload['serviceType'] = ['coding' => [[
            'system' => 'http://snomed.info/sct',
            'code' => '84687003',
            'display' => 'Dental care',
        ]]];

        $orgId = $orgId ?: config('satusehat.org_id');
        if ($orgId) {
            $payload['identifier'] = [[
                'system' => 'http://sys-ids.kemkes.go.id/encounter/'.$orgId,
                'use' => 'official',
                'value' => $visit->visit_number ?? (string) $visit->id,
            ]];
            $payload['serviceProvider'] = ['reference' => 'Organization/'.$orgId];
        }

        return $payload;
    }

    /**
     * Condition: clinicalStatus, category encounter-diagnosis, verificationStatus
     * (confirmed), bodySite SNOMED CT bila gigi spesifik (mis. 33185008 = upper
     * right first molar region).
     */
    public static function condition(object $diagnosis, string $patientIhsId, string $encounterId): ?array
    {
        if (($diagnosis->system ?? null) !== 'ICD10' || empty($diagnosis->code)) {
            return null;
        }

        $payload = [
            'resourceType' => 'Condition',
            'clinicalStatus' => ['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                'code' => 'active',
            ]]],
            'verificationStatus' => ['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                'code' => 'confirmed',
            ]]],
            'category' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                'code' => 'encounter-diagnosis',
            ]]]],
            'code' => ['coding' => [[
                'system' => self::SYSTEM_ICD10,
                'code' => $diagnosis->code,
                'display' => $diagnosis->display ?? $diagnosis->code,
            ]]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
        ];

        $bodySite = self::bodySiteTooth($diagnosis->tooth_fdi ?? null);
        if ($bodySite) {
            $payload['bodySite'] = [$bodySite];
        }

        return $payload;
    }

    /**
     * Procedure: bodySite gigi SNOMED bila ada; performedDateTime pakai
     * zona waktu +07:00 (WIB) sesuai format yang diterima SSP.
     */
    public static function procedure(object $treatment, string $patientIhsId, string $encounterId, string $performed): ?array
    {
        if (($treatment->system ?? null) !== 'ICD9' || empty($treatment->code)) {
            return null;
        }

        $payload = [
            'resourceType' => 'Procedure',
            'status' => 'completed',
            'code' => ['coding' => [[
                'system' => self::SYSTEM_ICD9CM,
                'code' => $treatment->code,
                'display' => $treatment->procedure ?? $treatment->code,
            ]]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'performedDateTime' => Carbon::parse($performed)->setTimezone('+07:00')->toIso8601String(),
        ];

        $bodySite = self::bodySiteTooth($treatment->tooth_fdi ?? null);
        if ($bodySite) {
            $payload['bodySite'] = [$bodySite];
        }

        return $payload;
    }

    /**
     * Observation tekanan darah dari kolom examinations.blood_pressure
     * ("120/80"). Sistolik LOINC 8480-6, diastolik LOINC 8462-4,
     * panel induk LOINC 85354-9. Nilai tidak valid → null (dilewati).
     */
    public static function bloodPressureObservations(?string $bloodPressure, string $patientIhsId, string $encounterId, ?string $practitionerRef, string $recordedAt): ?array
    {
        if (empty($bloodPressure)) {
            return null;
        }
        if (! preg_match('/^(\d{1,3})\s*\/\s*(\d{1,3})$/', trim($bloodPressure), $m)) {
            return null;
        }
        [$all, $systolic, $diastolic] = $m;
        if ((int) $systolic < 50 || (int) $systolic > 300 || (int) $diastolic < 30 || (int) $diastolic > 200) {
            return null;
        }

        $effective = Carbon::parse($recordedAt)->setTimezone('+07:00')->toIso8601String();
        $quantity = static fn (int $value): array => [
            'value' => $value,
            'unit' => 'mm[Hg]',
            'system' => 'http://unitsofmeasure.org',
            'code' => 'mm[Hg]',
        ];
        $member = static fn (string $loinc, string $display, int $value): array => [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                'code' => 'vital-signs',
                'display' => 'Vital Signs',
            ]]]],
            'code' => ['coding' => [[
                'system' => self::SYSTEM_LOINC,
                'code' => $loinc,
                'display' => $display,
            ]]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'effectiveDateTime' => $effective,
            'valueQuantity' => $quantity($value),
        ];
        if ($practitionerRef) {
            $memberSystolic = $member('8480-6', 'Systolic blood pressure', (int) $systolic);
            $memberSystolic['performer'] = [['reference' => $practitionerRef]];
            $memberDiastolic = $member('8462-4', 'Diastolic blood pressure', (int) $diastolic);
            $memberDiastolic['performer'] = [['reference' => $practitionerRef]];

            return [$memberSystolic, $memberDiastolic];
        }

        return [
            $member('8480-6', 'Systolic blood pressure', (int) $systolic),
            $member('8462-4', 'Diastolic blood pressure', (int) $diastolic),
        ];
    }

    /**
     * Observation tanda vital lain (pulse, temperature, respiratory rate).
     * LOINC: 8867-4 heart rate, 8310-5 body temperature, 9279-1 respiratory rate.
     * Pregnancy status LOINC 82810-3 → Observation valueString (opsional).
     */
    public static function vitalSignObservations(?object $vitals, string $patientIhsId, string $encounterId, ?string $practitionerRef, string $recordedAt): array
    {
        if (! $vitals) {
            return [];
        }

        $effective = Carbon::parse($recordedAt)->setTimezone('+07:00')->toIso8601String();
        $out = [];

        $quantityObservation = function (string $loinc, string $display, float|int $value, string $unit, string $ucum) use ($patientIhsId, $encounterId, $practitionerRef, $effective): array {
            $obs = [
                'resourceType' => 'Observation',
                'status' => 'final',
                'category' => [['coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => 'vital-signs',
                    'display' => 'Vital Signs',
                ]]]],
                'code' => ['coding' => [[
                    'system' => self::SYSTEM_LOINC,
                    'code' => $loinc,
                    'display' => $display,
                ]]],
                'subject' => ['reference' => 'Patient/'.$patientIhsId],
                'encounter' => ['reference' => 'Encounter/'.$encounterId],
                'effectiveDateTime' => $effective,
                'valueQuantity' => [
                    'value' => $value,
                    'unit' => $unit,
                    'system' => 'http://unitsofmeasure.org',
                    'code' => $ucum,
                ],
            ];
            if ($practitionerRef) {
                $obs['performer'] = [['reference' => $practitionerRef]];
            }

            return $obs;
        };

        if (! empty($vitals->pulse_bpm)) {
            $out[] = $quantityObservation('8867-4', 'Heart rate', (int) $vitals->pulse_bpm, '/min', '/min');
        }
        if (! empty($vitals->temperature_c)) {
            $out[] = $quantityObservation('8310-5', 'Body temperature', (float) $vitals->temperature_c, 'Cel', 'Cel');
        }
        if (! empty($vitals->respiratory_rate)) {
            $out[] = $quantityObservation('9279-1', 'Respiratory rate', (int) $vitals->respiratory_rate, '/min', '/min');
        }
        if (! empty($vitals->pregnancy_status)) {
            // Kode terminologi Kemenkes (bukan teks bebas): PREGNANT=OI000011,
            // NOT_PREGNANT=OI000012, UNSURE=OI000013.
            $pregnancyCodes = [
                'PREGNANT' => ['OI000011', 'Hamil'],
                'NOT_PREGNANT' => ['OI000012', 'Tidak Hamil'],
                'UNSURE' => ['OI000013', 'Tidak Diketahui'],
            ];
            $code = $pregnancyCodes[$vitals->pregnancy_status] ?? null;
            if ($code) {
                $obs = [
                    'resourceType' => 'Observation',
                    'status' => 'final',
                    'category' => [['coding' => [[
                        'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                        'code' => 'vital-signs',
                        'display' => 'Vital Signs',
                    ]]]],
                    'code' => ['coding' => [[
                        'system' => self::SYSTEM_LOINC,
                        'code' => '82810-3',
                        'display' => 'Pregnancy status',
                    ]]],
                    'subject' => ['reference' => 'Patient/'.$patientIhsId],
                    'encounter' => ['reference' => 'Encounter/'.$encounterId],
                    'effectiveDateTime' => $effective,
                    'valueCodeableConcept' => ['coding' => [[
                        'system' => 'http://terminology.kemkes.go.id/CodeSystem/oi',
                        'code' => $code[0],
                        'display' => $code[1],
                    ]]],
                ];
                if ($practitionerRef) {
                    $obs['performer'] = [['reference' => $practitionerRef]];
                }
                $out[] = $obs;
            }
        }

        return $out;
    }

    /**
     * MedicationRequest dari prescription + items.
     * status active, intent order, dispenseRequest.quantity = total quantity item.
     * KFA code → medicationCodeableConcept.identifier (sistem lokal KFA).
     */
    public static function medicationRequest(object $prescription, string $patientIhsId, string $encounterId, ?string $practitionerRef): ?array
    {
        $items = collect($prescription->items ?? []);
        if ($items->isEmpty() || empty($patientIhsId) || empty($encounterId)) {
            return null;
        }

        $first = $items->first();
        $medicationText = $first->medicine_name;
        $kfa = $first->kfa_code;

        $quantityTotal = max(1, (int) $items->sum('quantity'));

        $dosage = [];
        if ($first->dosage || $first->frequency || $first->duration) {
            $dosage[] = [
                'text' => trim(($first->dosage ? 'Dosage: '.$first->dosage : '').' '
                    .($first->frequency ? 'Frequency: '.$first->frequency : '').' '
                    .($first->duration ? 'Duration: '.$first->duration : '').' '
                    .($first->instruction ?: '')),
                'timing' => $first->frequency ? [['repeat' => ['frequency' => [1], 'period' => 1, 'periodUnit' => 'd']]] : null,
                'route' => $first->route ? [['coding' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/route-codes',
                    'code' => $first->route,
                    'display' => $first->route,
                ]]]] : null,
                'doseAndRate' => $first->dosage ? [[
                    'doseQuantity' => ['value' => (float) preg_replace('/[^0-9.]/', '', $first->dosage) ?: null, 'unit' => preg_replace('/^[0-9.\s]+/', '', $first->dosage) ?: null],
                ]] : null,
            ];
            $dosage = array_map(fn ($d) => array_filter($d, fn ($v) => $v !== null), $dosage);
        }

        $payload = [
            'resourceType' => 'MedicationRequest',
            'status' => 'active',
            'intent' => 'order',
            'priority' => 'routine',
            'medicationCodeableConcept' => [
                'text' => $medicationText,
                'coding' => $kfa ? [[
                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/kfa',
                    'code' => $kfa,
                    'display' => $medicationText,
                ]] : [],
            ],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'authoredOn' => Carbon::parse($prescription->prescribed_at ?? now())->setTimezone('+07:00')->toIso8601String(),
            'requester' => $practitionerRef ? ['reference' => $practitionerRef] : null,
            'dispenseRequest' => [
                'quantity' => [
                    'value' => $quantityTotal,
                    'unit' => 'TAB',
                    'system' => 'http://unitsofmeasure.org',
                    'code' => 'TAB',
                ],
            ],
        ];

        if ($dosage !== []) {
            $payload['dosageInstruction'] = $dosage;
        }

        return array_filter($payload, fn ($v) => $v !== null);
    }

    /**
     * bodySite gigi: SNOMED CT sesuai lampiran terminologi SATUSEHAT Gigi
     * (FDI 11–48 + 51–85 → nomenklatur resmi Kemenkes).
     * Gigi di luar tabel → coding FDI lokal sebagai fallback yang tetap terlacak.
     */
    public static function bodySiteTooth(?string $fdi): ?array
    {
        $fdi = trim((string) $fdi);
        if ($fdi === '' || $fdi === '0') {
            return null;
        }

        $tooth = SatuSehatDental::TOOTH_SNOMED[$fdi] ?? null;
        if ($tooth) {
            return ['coding' => [[
                'system' => self::SYSTEM_SNOMED_TOOTH,
                'code' => $tooth[0],
                'display' => $tooth[1],
            ]]];
        }

        return ['coding' => [[
            'system' => 'http://terminology.kemkes.go.id/CodeSystem/tooth-fdi',
            'code' => $fdi,
            'display' => 'Gigi FDI '.$fdi,
        ]]];
    }
}
