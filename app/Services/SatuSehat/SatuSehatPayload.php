<?php

namespace App\Services\SatuSehat;

use Carbon\Carbon;

/**
 * Penyusun payload FHIR R4 SATUSEHAT untuk layanan gigi.
 * Mengembalikan null bila data belum memenuhi syarat wajib —
 * pemanggil mencatat SKIPPED beserta alasannya (kesiapan Fase 1).
 */
class SatuSehatPayload
{
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

        return [
            'resourceType' => 'Patient',
            'identifier' => [
                [
                    'system' => 'http://sys-ids.kemkes.go.id/nik',
                    'value' => (string) $patient->nik,
                ],
            ],
            'active' => true,
            'name' => [['text' => $patient->name]],
            'gender' => ($patient->gender ?? 'MALE') === 'FEMALE' ? 'female' : 'male',
            'birthDate' => $birthDate,
        ];
    }

    public static function practitionerRef(?string $ihsId): ?string
    {
        return $ihsId ? 'Practitioner/'.$ihsId : null;
    }

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
            'period' => ['start' => Carbon::parse($visit->visit_date)->toIso8601String()],
        ];

        if ($practitionerRef) {
            $payload['participant'] = [['individual' => ['reference' => $practitionerRef]]];
        }
        if (config('satusehat.org_id')) {
            $payload['serviceProvider'] = ['reference' => 'Organization/'.config('satusehat.org_id')];
        }

        return $payload;
    }

    public static function condition(object $diagnosis, string $patientIhsId, string $encounterId): ?array
    {
        if (($diagnosis->system ?? null) !== 'ICD10' || empty($diagnosis->code)) {
            return null;
        }

        return [
            'resourceType' => 'Condition',
            'clinicalStatus' => ['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                'code' => 'active',
            ]]],
            'category' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                'code' => 'encounter-diagnosis',
            ]]]],
            'code' => ['coding' => [[
                'system' => 'http://hl7.org/fhir/sid/icd-10',
                'code' => $diagnosis->code,
                'display' => $diagnosis->display ?? $diagnosis->code,
            ]]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
        ];
    }

    public static function procedure(object $treatment, string $patientIhsId, string $encounterId, string $performed): ?array
    {
        if (($treatment->system ?? null) !== 'ICD9' || empty($treatment->code)) {
            return null;
        }

        return [
            'resourceType' => 'Procedure',
            'status' => 'completed',
            'code' => ['coding' => [[
                'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
                'code' => $treatment->code,
                'display' => $treatment->procedure ?? $treatment->code,
            ]]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'performedDateTime' => Carbon::parse($performed)->toIso8601String(),
        ];
    }
}
