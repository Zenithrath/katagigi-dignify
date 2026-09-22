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
        ];

        if (! empty($patient->phone)) {
            $payload['telecom'] = [[
                'system' => 'phone',
                'value' => $patient->phone,
                'use' => 'mobile',
            ]];
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

        return $payload;
    }

    public static function practitionerRef(?string $ihsId): ?string
    {
        return $ihsId ? 'Practitioner/'.$ihsId : null;
    }

    /**
     * Identifier Encounter memakai system resmi per organisasi:
     * http://sys-ids.kemkes.go.id/encounter/{organization-ihs-number}
     * dengan value = nomor visit lokal.
     */
    public static function encounter(object $visit, string $patientIhsId, ?string $practitionerRef): ?array
    {
        if (! $patientIhsId) {
            return null;
        }

        $payload = [
            'resourceType' => 'Encounter',
            'status' => 'finished',
            'class' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'AMB',
                'display' => 'ambulatory',
            ],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'participant' => [
                ['type' => [[
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                    'code' => 'ATND',
                    'display' => 'attender',
                ]]],
            ],
            'period' => ['start' => Carbon::parse($visit->visit_date)->toIso8601String()],
        ];

        if ($practitionerRef) {
            $payload['participant'][0]['individual'] = ['reference' => $practitionerRef];
        }

        $orgId = config('satusehat.org_id');
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
     * bodySite gigi: SNOMED CT concept region gigi (F mouth structure) berbasis
     * FDI. Pemetaan konsep gigi tunggal FDI 11-48; nilai lain → Coding FDI
     * generik sebagai fallback (tidak memblokir pengiriman).
     */
    public static function bodySiteTooth(?string $fdi): ?array
    {
        $fdi = trim((string) $fdi);
        if ($fdi === '' || $fdi === '0') {
            return null;
        }

        $snomed = [
            '11' => '245581009', '12' => '245582002', '13' => '245583007', '14' => '245584001',
            '15' => '245585000', '16' => '245586004', '17' => '245587009', '18' => '245588004',
            '21' => '245589007', '22' => '245590001', '23' => '245591002', '24' => '245592009',
            '25' => '245593004', '26' => '245594005', '27' => '245595006', '28' => '245596007',
            '31' => '245597008', '32' => '245598003', '33' => '245599006', '34' => '245600001',
            '35' => '245601002', '36' => '245602009', '37' => '245603004', '38' => '245604005',
            '41' => '245605006', '42' => '245606007', '43' => '245607003', '44' => '245608008',
            '45' => '245609000', '46' => '245610004', '47' => '245611000', '48' => '245612007',
        ];

        $coding = [];
        if (isset($snomed[$fdi])) {
            $coding[] = [
                'system' => self::SYSTEM_SNOMED_TOOTH,
                'code' => $snomed[$fdi],
                'display' => 'Tooth region ('.$fdi.' FDI)',
            ];
        } else {
            // Fallback: gigi susu/di luar konsep tunggal → kirim sebagai
            // coding lokal FDI agar tetap terlacak.
            $coding[] = [
                'system' => 'http://terminology.kemkes.go.id/CodeSystem/tooth-fdi',
                'code' => $fdi,
                'display' => 'Gigi FDI '.$fdi,
            ];
        }

        return ['coding' => $coding];
    }
}
