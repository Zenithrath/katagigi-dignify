<?php

namespace Tests\Unit;

use App\Models\OdontogramFinding;
use App\Models\OralHealthIndex;
use App\Services\SatuSehat\SatuSehatDental;
use App\Services\SatuSehat\SatuSehatPayload;
use PHPUnit\Framework\TestCase;

/**
 * Payload FHIR odontogram & OHI-S mengikuti lampiran terminologi SATUSEHAT
 * (Modul Gigi) — kode diuji agar tidak ada regresi "kode karangan".
 */
class SatuSehatDentalTest extends TestCase
{
    private function finding(string $fdi, ?string $surface, string $condition, ?string $material = null): object
    {
        return (object) [
            'fdi' => $fdi,
            'surface' => $surface,
            'condition' => $condition,
            'material' => $material,
        ];
    }

    public function test_tooth_snomed_table_covers_every_fdi_tooth(): void
    {
        $expected = OdontogramFinding::allTeeth();
        sort($expected);

        // Kunci array numerik dikembalikan PHP sebagai int — samakan ke string.
        $mapped = array_map('strval', array_keys(SatuSehatDental::TOOTH_SNOMED));
        sort($mapped);

        $this->assertSame($expected, $mapped);
        $this->assertSame('422653006', SatuSehatDental::TOOTH_SNOMED['11'][0]);
        $this->assertSame('866006002', SatuSehatDental::TOOTH_SNOMED['36'][0]);
        $this->assertSame('61868007', SatuSehatDental::TOOTH_SNOMED['85'][0]);
    }

    public function test_body_site_uses_official_snomed_and_still_falls_back(): void
    {
        $site = SatuSehatPayload::bodySiteTooth('11');
        $this->assertSame('http://snomed.info/sct', $site['coding'][0]['system']);
        $this->assertSame('422653006', $site['coding'][0]['code']);
        $this->assertSame('Structure of permanent maxillary right central incisor tooth', $site['coding'][0]['display']);

        // Gigi tak dikenal tetap terkirim sebagai kode FDI lokal.
        $fallback = SatuSehatPayload::bodySiteTooth('99');
        $this->assertSame('99', $fallback['coding'][0]['code']);

        $this->assertNull(SatuSehatPayload::bodySiteTooth(''));
    }

    public function test_odontogram_observation_maps_code_components_and_body_site(): void
    {
        $payloads = SatuSehatDental::odontogramObservations([
            $this->finding('36', 'occlusal', 'caries'),
            $this->finding('36', 'mesial', 'filled', 'Komposit'),
        ], 'P-1', 'E-1', 'Practitioner/D1', '2026-09-22 10:00:00');

        $this->assertCount(1, $payloads);
        $payload = $payloads[0];

        $this->assertSame('Observation', $payload['resourceType']);
        $this->assertSame('OC000061', $payload['code']['coding'][0]['code']);
        $this->assertSame('exam', $payload['category'][0]['coding'][0]['code']);
        $this->assertTrue($payload['valueBoolean']);
        $this->assertSame('866006002', $payload['bodySite']['coding'][0]['code']);
        $this->assertSame([['reference' => 'Practitioner/D1']], $payload['performer']);

        $codes = array_column(array_column(array_column($payload['component'], 'valueCodeableConcept'), 'coding'), 0);
        $flattened = array_map(fn (array $coding) => $coding['code'], $codes);
        // Permukaan (O/M), keadaan (karies + tambalan), restorasi (filling present), bahan (komposit).
        $this->assertContains('257885003', $flattened);
        $this->assertContains('710099007', $flattened);
        $this->assertContains('80967001', $flattened);
        $this->assertContains('287451003', $flattened);
        $this->assertContains('278550007', $flattened);
        $this->assertContains('256452006', $flattened);
    }

    public function test_restoration_and_prosthesis_components_are_emitted(): void
    {
        $payload = SatuSehatDental::odontogramObservations([
            $this->finding('11', 'whole', 'crown'),
            $this->finding('12', 'whole', 'implant'),
            $this->finding('13', 'whole', 'denture'),
        ], 'P-1', 'E-1', null, '2026-09-22 10:00:00');

        $flattened = collect($payload)
            ->flatMap(fn (array $row) => $row['component'] ?? [])
            ->pluck('valueCodeableConcept.coding')
            ->flatten(1)
            ->pluck('code')
            ->all();

        $this->assertContains('272289008', $flattened); // Crown (restorasi)
        $this->assertContains('468993001', $flattened); // Dental implant system
        $this->assertContains('272256008', $flattened); // Partial denture
    }

    public function test_unmapped_condition_is_reported_as_other_oral_condition(): void
    {
        $payloads = SatuSehatDental::odontogramObservations([
            $this->finding('41', 'whole', 'mobile'),
        ], 'P-1', 'E-1', null, '2026-09-22 10:00:00');

        $this->assertCount(2, $payloads);
        $this->assertSame('OC000061', $payloads[0]['code']['coding'][0]['code']);
        $this->assertSame('OC000060', $payloads[1]['code']['coding'][0]['code']);
        $this->assertStringContainsString('41', $payloads[1]['valueString']);
        $this->assertStringContainsString('mobile', $payloads[1]['valueString']);
    }

    public function test_ohis_and_dmf_observations_use_official_codes(): void
    {
        $ohi = new OralHealthIndex([
            'ohis_debris' => 1.5,
            'ohis_calculus' => 1.0,
            'ohis_total' => 2.5,
            'd_count' => 3,
            'm_count' => 1,
            'f_count' => 2,
        ]);

        $payloads = SatuSehatDental::oralHealthObservations($ohi, 'P-1', 'E-1', 'Practitioner/D1', '2026-09-22 10:00:00');

        $codes = array_map(fn (array $row) => $row['code']['coding'][0]['code'], $payloads);
        $this->assertContains('OC000056', $codes); // total debris
        $this->assertContains('OC000057', $codes); // total kalkulus
        $this->assertContains('OC000058', $codes); // total OHIS
        $this->assertContains('251319000', $codes); // decayed count
        $this->assertContains('251317003', $codes); // missing count
        $this->assertContains('251318008', $codes); // filled count

        $ohis = collect($payloads)->firstWhere('code.coding.0.code', 'OC000058');
        $this->assertSame(2.5, $ohis['valueQuantity']['value']);
        $this->assertSame('{score}', $ohis['valueQuantity']['code']);
        $this->assertSame('OI000030', $ohis['interpretation'][0]['coding'][0]['code']);
    }

    public function test_ohis_interpretation_thresholds(): void
    {
        $this->assertSame('OI000029', OralHealthIndex::interpretationFor(1.2)[0]);
        $this->assertSame('OI000030', OralHealthIndex::interpretationFor(1.3)[0]);
        $this->assertSame('OI000031', OralHealthIndex::interpretationFor(3.1)[0]);
    }

    public function test_oral_health_observations_skip_when_absent(): void
    {
        $this->assertSame([], SatuSehatDental::oralHealthObservations(null, 'P-1', 'E-1', null, '2026-09-22'));
    }
}
