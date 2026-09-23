<?php

namespace App\Services\SatuSehat;

use App\Models\Examination;
use App\Models\OdontogramFinding;
use App\Models\OralHealthIndex;
use App\Models\RadiologyOrder;
use Carbon\Carbon;

/**
 * Payload FHIR R4 SATUSEHAT khusus layanan gigi (Modul Gigi / "Rawat Jalan Gigi").
 *
 * Seluruh kode mengikuti lampiran terminologi resmi SATUSEHAT Platform:
 * - Nomenklatur Gigi FDI (bodySite)          → Lampiran 1/3
 * - Permukaan Gigi (component)               → Lampiran 4
 * - Keadaan Gigi (component)                 → Lampiran 5
 * - Bahan Restorasi / Restorasi / Protesa    → Lampiran 6/7/8/9
 * - OHIS, DMF-T, occlusi, torus, palatum     → Tabel 4/5
 *
 * Pemetaan kode selaras agar payload lolos validasi SSP, bukan kode karangan.
 */
class SatuSehatDental
{
    public const SYSTEM_KEMKES = 'http://terminology.kemkes.go.id/CodeSystem/clinical-term';

    public const SYSTEM_SNOMED = 'http://snomed.info/sct';

    public const SYSTEM_LOINC = 'http://loinc.org';

    public const SYSTEM_UCUM = 'http://unitsofmeasure.org';

    public const SYSTEM_CATEGORY = 'http://terminology.hl7.org/CodeSystem/observation-category';

    /** Nomenklatur gigi FDI → SNOMED CT (SATUSEHAT Lampiran 3). */
    public const TOOTH_SNOMED = [
        '11' => ['422653006', 'Structure of permanent maxillary right central incisor tooth'],
        '12' => ['424877001', 'Structure of permanent maxillary right lateral incisor tooth'],
        '13' => ['860767006', 'Structure of permanent maxillary right canine tooth'],
        '14' => ['57826002', 'Structure of permanent maxillary right first premolar tooth'],
        '15' => ['36492000', 'Structure of permanent maxillary right second premolar tooth'],
        '16' => ['865995000', 'Structure of permanent maxillary right first molar tooth'],
        '17' => ['863902006', 'Structure of permanent maxillary right second molar tooth'],
        '18' => ['68085002', 'Structure of permanent maxillary right third molar tooth'],
        '21' => ['424399000', 'Structure of permanent maxillary left central incisor tooth'],
        '22' => ['423185002', 'Structure of permanent maxillary left lateral incisor tooth'],
        '23' => ['860780009', 'Structure of permanent maxillary left canine tooth'],
        '24' => ['61897005', 'Structure of permanent maxillary left first premolar tooth'],
        '25' => ['23226009', 'Structure of permanent maxillary left second premolar tooth'],
        '26' => ['865988009', 'Structure of permanent maxillary left first molar tooth'],
        '27' => ['863901004', 'Structure of permanent maxillary left second molar tooth'],
        '28' => ['87704003', 'Structure of permanent maxillary left third molar tooth'],
        '31' => ['425106001', 'Structure of permanent mandibular left central incisor tooth'],
        '32' => ['423331005', 'Structure of permanent mandibular left lateral incisor tooth'],
        '33' => ['860782001', 'Structure of permanent mandibular left canine tooth'],
        '34' => ['2400006', 'Structure of permanent mandibular left first premolar tooth'],
        '35' => ['24573005', 'Structure of permanent mandibular left second premolar tooth'],
        '36' => ['866006002', 'Structure of permanent mandibular left first molar tooth'],
        '37' => ['863898000', 'Structure of permanent mandibular left second molar tooth'],
        '38' => ['74344005', 'Structure of permanent mandibular left third molar tooth'],
        '41' => ['424575004', 'Structure of permanent mandibular right central incisor tooth'],
        '42' => ['423937004', 'Structure of permanent mandibular right lateral incisor tooth'],
        '43' => ['860785004', 'Structure of permanent mandibular right canine tooth'],
        '44' => ['80140008', 'Structure of permanent mandibular right first premolar tooth'],
        '45' => ['8873007', 'Structure of permanent mandibular right second premolar tooth'],
        '46' => ['866005003', 'Structure of permanent mandibular right first molar tooth'],
        '47' => ['863899008', 'Structure of permanent mandibular right second molar tooth'],
        '48' => ['38994002', 'Structure of permanent mandibular right third molar tooth'],
        '51' => ['88824007', 'Structure of deciduous maxillary right central incisor tooth'],
        '52' => ['65624003', 'Structure of deciduous maxillary right lateral incisor tooth'],
        '53' => ['30618001', 'Structure of deciduous maxillary right canine tooth'],
        '54' => ['17505006', 'Structure of deciduous maxillary right first molar tooth'],
        '55' => ['27855007', 'Structure of deciduous maxillary right second molar tooth'],
        '61' => ['51678005', 'Structure of deciduous maxillary left central incisor tooth'],
        '62' => ['43622005', 'Structure of deciduous maxillary left lateral incisor tooth'],
        '63' => ['73937000', 'Structure of deciduous maxillary left canine tooth'],
        '64' => ['45234009', 'Structure of deciduous maxillary left first molar tooth'],
        '65' => ['51943008', 'Structure of deciduous maxillary left second molar tooth'],
        '71' => ['89552004', 'Structure of deciduous mandibular left central incisor tooth'],
        '72' => ['14770005', 'Structure of deciduous mandibular left lateral incisor tooth'],
        '73' => ['43281008', 'Structure of deciduous mandibular left canine tooth'],
        '74' => ['38896004', 'Structure of deciduous mandibular left first molar tooth'],
        '75' => ['49330006', 'Structure of deciduous mandibular left second molar tooth'],
        '81' => ['67834006', 'Structure of deciduous mandibular right central incisor tooth'],
        '82' => ['22445006', 'Structure of deciduous mandibular right lateral incisor tooth'],
        '83' => ['6062009', 'Structure of deciduous mandibular right canine tooth'],
        '84' => ['58646007', 'Structure of deciduous mandibular right first molar tooth'],
        '85' => ['61868007', 'Structure of deciduous mandibular right second molar tooth'],
    ];

    /** Keadaan gigi (Lampiran 5) — hanya kondisi yang punya padanan resmi. */
    private const CONDITION_CODES = [
        'sound' => ['162005007', 'No tooth problem'],
        'caries' => ['80967001', 'Dental caries'],
        'filled' => ['287451003', 'Tooth cavity drilled and filled'],
        'missing' => ['234948008', 'Tooth absent'],
        'root' => ['66569006', 'Retained dental root'],
        'fracture' => ['278590005', 'Fractured dental crown'],
    ];

    /** Restorasi (Lampiran 7). */
    private const RESTORATION_CODES = [
        'crown' => ['272289008', 'Crown'],
        'implant' => ['468993001', 'Dental implant system'],
        'filled' => ['278550007', 'Dental filling present'],
    ];

    /** Protesa (Lampiran 9). */
    private const PROSTHESIS_CODES = [
        'denture' => ['272256008', 'Partial denture'],
    ];

    /** Permukaan gigi (Lampiran 4); insisal memakai zona 'O' seperti odontogram Kemkes. */
    private const SURFACE_CODES = [
        'mesial' => ['710099007', 'Mesial'],
        'occlusal' => ['257885003', 'Occlusion-action'],
        'incisal' => ['257885003', 'Occlusion-action'],
        'distal' => ['46053002', 'Distal'],
        'buccal' => ['302990001', 'Buccalis'],
        'lingual' => ['255579002', 'Palatal-lingual'],
        'palatal' => ['255579002', 'Palatal-lingual'],
    ];

    /** Bahan restorasi (Lampiran 6) — dicocokkan dari teks material bebas. */
    private const MATERIAL_HINTS = [
        'amalgam' => ['256447001', 'Amalgam (silver) dental filling material'],
        'gic' => ['256454007', 'Glass-ionomer dental material'],
        'glass' => ['256454007', 'Glass-ionomer dental material'],
        'silika' => ['256454007', 'Glass-ionomer dental material'],
        'komposit' => ['256452006', 'Composite dental filling material'],
        'composite' => ['256452006', 'Composite dental filling material'],
        'porselen' => ['256480008', 'Dental porcelain material'],
        'porcelain' => ['256480008', 'Dental porcelain material'],
        'zircon' => ['261253002', 'Ceramic'],
        'keramik' => ['261253002', 'Ceramic'],
        'ceramic' => ['261253002', 'Ceramic'],
    ];

    private const CODE_ODONTOGRAM = ['OC000061', 'Pemeriksaan Odontogram'];

    private const CODE_OTHER_ORAL = ['OC000060', 'Kondisi Gigi dan Mulut Lainnya'];

    private const COMPONENT_SURFACE = [self::SYSTEM_LOINC, '32889-8', 'Surface [Identifier] Tooth'];

    private const COMPONENT_CONDITION = [self::SYSTEM_SNOMED, '278544002', 'Tooth finding'];

    private const COMPONENT_MATERIAL = [self::SYSTEM_SNOMED, '432680005', 'Dental filling material'];

    private const COMPONENT_RESTORATION = [self::SYSTEM_SNOMED, '251335007', 'Dental restoration or prosthesis shade'];

    private const COMPONENT_PROSTHESIS = [self::SYSTEM_SNOMED, '256509009', 'Maxillofacial prosthesis and appliance material'];

    /**
     * Observation odontogram: satu resource per gigi (bodySite FDI + komponen).
     *
     * @param  iterable<OdontogramFinding>  $findings
     * @return list<array>
     */
    public static function odontogramObservations(
        iterable $findings,
        string $patientIhsId,
        string $encounterId,
        ?string $practitionerRef,
        string $recordedAt,
    ): array {
        $byTooth = collect($findings)->groupBy('fdi');
        $effective = self::effective($recordedAt);
        $payloads = [];
        $other = [];

        foreach ($byTooth as $fdi => $toothFindings) {
            $components = [];
            $conditions = $toothFindings->pluck('condition')->unique();

            foreach ($toothFindings->pluck('surface')->filter()->unique() as $surface) {
                if ($surface === 'whole') {
                    continue;
                }
                if ($code = self::SURFACE_CODES[$surface] ?? null) {
                    $components[] = self::component(self::COMPONENT_SURFACE, [self::coding(self::SYSTEM_SNOMED, $code)]);
                }
            }

            foreach ($conditions as $condition) {
                if ($code = self::CONDITION_CODES[$condition] ?? null) {
                    $components[] = self::component(self::COMPONENT_CONDITION, [self::coding(self::SYSTEM_SNOMED, $code)]);
                } else {
                    // Kondisi tanpa padanan resmi dilaporkan lewat field "lainnya".
                    $other[] = $fdi.' — '.$condition;
                }

                if ($code = self::RESTORATION_CODES[$condition] ?? null) {
                    $components[] = self::component(self::COMPONENT_RESTORATION, [self::coding(self::SYSTEM_SNOMED, $code)]);
                }

                if ($code = self::PROSTHESIS_CODES[$condition] ?? null) {
                    $components[] = self::component(self::COMPONENT_PROSTHESIS, [self::coding(self::SYSTEM_SNOMED, $code)]);
                }
            }

            $material = $toothFindings->pluck('material')->filter()->first();
            if ($material) {
                $components[] = self::component(self::COMPONENT_MATERIAL, [self::materialCoding((string) $material)]);
            }

            $payloads[] = array_filter([
                'resourceType' => 'Observation',
                'status' => 'final',
                'category' => [['coding' => [[
                    'system' => self::SYSTEM_CATEGORY,
                    'code' => 'exam',
                    'display' => 'Exam',
                ]]]],
                'code' => ['coding' => [self::coding(self::SYSTEM_KEMKES, self::CODE_ODONTOGRAM)]],
                'subject' => ['reference' => 'Patient/'.$patientIhsId],
                'encounter' => ['reference' => 'Encounter/'.$encounterId],
                'effectiveDateTime' => $effective,
                'performer' => $practitionerRef ? [['reference' => $practitionerRef]] : null,
                'valueBoolean' => true,
                'bodySite' => SatuSehatPayload::bodySiteTooth((string) $fdi),
                'component' => $components ?: null,
            ], fn ($value) => $value !== null);
        }

        if ($other !== []) {
            $payloads[] = [
                'resourceType' => 'Observation',
                'status' => 'final',
                'category' => [['coding' => [[
                    'system' => self::SYSTEM_CATEGORY,
                    'code' => 'exam',
                    'display' => 'Exam',
                ]]]],
                'code' => ['coding' => [self::coding(self::SYSTEM_KEMKES, self::CODE_OTHER_ORAL)]],
                'subject' => ['reference' => 'Patient/'.$patientIhsId],
                'encounter' => ['reference' => 'Encounter/'.$encounterId],
                'effectiveDateTime' => $effective,
                'valueString' => implode('; ', $other),
            ];
        }

        return $payloads;
    }

    /**
     * Observation OHI-S + hitung gigi D/M/F (Tabel 4 & 5 panduan Gigi).
     *
     * @return list<array>
     */
    public static function oralHealthObservations(
        ?OralHealthIndex $ohi,
        string $patientIhsId,
        string $encounterId,
        ?string $practitionerRef,
        string $recordedAt,
    ): array {
        if (! $ohi) {
            return [];
        }

        $effective = self::effective($recordedAt);
        $base = static fn (array $code): array => array_filter([
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [['coding' => [[
                'system' => self::SYSTEM_CATEGORY,
                'code' => 'exam',
                'display' => 'Exam',
            ]]]],
            'code' => ['coding' => [self::coding(self::SYSTEM_KEMKES, $code)]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'effectiveDateTime' => $effective,
            'performer' => $practitionerRef ? [['reference' => $practitionerRef]] : null,
        ], fn ($value) => $value !== null);

        $score = static fn (float $value): array => [
            'value' => round($value, 2),
            'unit' => 'score',
            'system' => self::SYSTEM_UCUM,
            'code' => '{score}',
        ];

        $payloads = [];

        if ($ohi->ohis_debris !== null) {
            $payloads[] = $base(['OC000056', 'Skor Total Debris Indeks']) + ['valueQuantity' => $score((float) $ohi->ohis_debris)];
        }
        if ($ohi->ohis_calculus !== null) {
            $payloads[] = $base(['OC000057', 'Skor Total Kalkulus Indeks']) + ['valueQuantity' => $score((float) $ohi->ohis_calculus)];
        }
        if ($ohi->ohis_total !== null) {
            $payloads[] = $base(['OC000058', 'Skor Total Oral Hygiene Index Simplified (OHIS)']) + [
                'valueQuantity' => $score((float) $ohi->ohis_total),
                'interpretation' => [['coding' => [self::coding(self::SYSTEM_KEMKES, OralHealthIndex::interpretationFor((float) $ohi->ohis_total))]]],
            ];
        }

        // Hitung gigi decayed/missing/filled (SNOMED 251319000/251317003/251318008).
        foreach ([
            ['251319000', 'Decayed tooth count', $ohi->d_count],
            ['251317003', 'Missing tooth count', $ohi->m_count],
            ['251318008', 'Filled tooth count', $ohi->f_count],
        ] as [$code, $display, $value]) {
            if ($value === null) {
                continue;
            }
            $payloads[] = array_filter([
                'resourceType' => 'Observation',
                'status' => 'final',
                'category' => [['coding' => [[
                    'system' => self::SYSTEM_CATEGORY,
                    'code' => 'exam',
                    'display' => 'Exam',
                ]]]],
                'code' => ['coding' => [self::coding(self::SYSTEM_SNOMED, [$code, $display])]],
                'subject' => ['reference' => 'Patient/'.$patientIhsId],
                'encounter' => ['reference' => 'Encounter/'.$encounterId],
                'effectiveDateTime' => $effective,
                'performer' => $practitionerRef ? [['reference' => $practitionerRef]] : null,
                'valueString' => (string) (int) $value,
            ], fn ($v) => $v !== null);
        }

        return $payloads;
    }

    /**
     * Observation kondisi mulut lainnya (oklusi, torus, palatum, diastema,
     * relasi sentral) — dikirim sebagai satu Observation OC000060
     * "Kondisi Gigi dan Mulut Lainnya" dengan valueString terstruktur.
     */
    public static function oralExamObservation(
        ?Examination $examination,
        string $patientIhsId,
        string $encounterId,
        ?string $practitionerRef,
        string $recordedAt,
    ): ?array {
        if (! $examination) {
            return null;
        }

        $labels = [
            'Oklusi' => Examination::OCCLUSIONS[$examination->occlusion] ?? null,
            'Torus' => Examination::TORUS[$examination->torus] ?? null,
            'Palatum' => Examination::PALATUM[$examination->palatum] ?? null,
            'Diastema' => Examination::DIASTEMA[$examination->diastema] ?? null,
            'Relasi molar' => Examination::ANGLE_CLASSES[$examination->molar_relation] ?? null,
            'Relasi kaninus' => Examination::ANGLE_CLASSES[$examination->canine_relation] ?? null,
        ];
        $parts = collect($labels)->filter()->map(fn ($label, $key) => "$key: $label")->values();
        if ($examination->other_oral_findings) {
            $parts[] = 'Lainnya: '.$examination->other_oral_findings;
        }
        if ($parts->isEmpty()) {
            return null;
        }

        $payload = [
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [['coding' => [[
                'system' => self::SYSTEM_CATEGORY,
                'code' => 'exam',
                'display' => 'Exam',
            ]]]],
            'code' => ['coding' => [self::coding(self::SYSTEM_KEMKES, self::CODE_OTHER_ORAL)]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'effectiveDateTime' => self::effective($recordedAt),
            'valueString' => implode('; ', $parts->all()),
        ];

        if ($practitionerRef) {
            $payload['performer'] = [['reference' => $practitionerRef]];
        }

        return $payload;
    }

    /**
     * Payload FHIR Media untuk berkas hasil radiologi (PNG/JPG).
     * DICOM tidak dikirim (opsional di roadmap) — hanya gambar.
     */
    public static function mediaPayload(RadiologyOrder $order, string $patientIhsId, string $encounterId, string $binary, string $mime): array
    {
        return [
            'resourceType' => 'Media',
            'status' => 'completed',
            'type' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/media-type',
                'code' => 'image',
                'display' => 'Image',
            ]],
            'modality' => [[
                'system' => 'http://dicom.nema.org/resources/ontology/DCM',
                'code' => $order->modality,
            ]],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'createdDateTime' => Carbon::parse($order->performed_at ?? $order->created_at)->setTimezone('+07:00')->toIso8601String(),
            'content' => [
                'contentType' => $mime,
                'data' => base64_encode($binary),
                'title' => 'radiologi-'.$order->id.'.'.pathinfo($order->result_path, PATHINFO_EXTENSION),
            ],
        ];
    }

    /**
     * Payload FHIR DiagnosticReport dari hasil baca dokter.
     * Dilengkapi Media hasil imaging bila tersedia.
     */
    public static function diagnosticReport(RadiologyOrder $order, string $patientIhsId, string $encounterId, ?string $practitionerRef, ?string $mediaId = null): ?array
    {
        if (empty($order->result_text)) {
            return null;
        }

        $payload = [
            'resourceType' => 'DiagnosticReport',
            'status' => 'final',
            'category' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                'code' => 'RAD',
                'display' => 'Radiology',
            ]]]],
            'code' => [
                'coding' => [[
                    'system' => 'http://loinc.org',
                    'code' => '39764-4',
                    'display' => 'Disease x-ray panel',
                ]],
                'text' => 'Radiologi '.($order->body_site ?: RadiologyOrder::MODALITIES[$order->modality] ?? $order->modality),
            ],
            'subject' => ['reference' => 'Patient/'.$patientIhsId],
            'encounter' => ['reference' => 'Encounter/'.$encounterId],
            'effectiveDateTime' => Carbon::parse($order->performed_at ?? $order->created_at)->setTimezone('+07:00')->toIso8601String(),
            'conclusion' => $order->result_text,
        ];

        if ($practitionerRef) {
            $payload['resultsInterpreter'] = [['reference' => $practitionerRef]];
        }
        if ($mediaId) {
            $payload['media'] = [['link' => ['reference' => 'Media/'.$mediaId, 'display' => 'Hasil imaging']]];
        }

        return $payload;
    }

    private static function coding(string $system, array $code): array
    {
        return ['system' => $system, 'code' => $code[0], 'display' => $code[1]];
    }

    /** Kode bahan restorasi dari teks bebas; teks tetap dibawa agar tidak hilang. */
    private static function materialCoding(string $material): array
    {
        $needle = strtolower($material);
        foreach (self::MATERIAL_HINTS as $keyword => $code) {
            if (str_contains($needle, $keyword)) {
                return self::coding(self::SYSTEM_SNOMED, $code) + ['text' => $material];
            }
        }

        return ['text' => $material];
    }

    private static function component(array $code, array $values): array
    {
        return [
            'code' => ['coding' => [self::coding($code[0], [$code[1], $code[2]])]],
            'valueCodeableConcept' => ['coding' => $values],
        ];
    }

    private static function effective(string $recordedAt): string
    {
        return Carbon::parse($recordedAt)->setTimezone('+07:00')->toIso8601String();
    }
}
