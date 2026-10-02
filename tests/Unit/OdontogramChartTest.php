<?php

namespace Tests\Unit;

use App\Helpers\OdontogramChart;
use App\Models\OdontogramFinding;
use PHPUnit\Framework\TestCase;

/**
 * Geometri + notasi chart odontogram: FDI → Universal/Palmer, pemetaan zona
 * permukaan, dan arah rotasi gigi pada lengkung.
 */
class OdontogramChartTest extends TestCase
{
    public function test_permanent_tooth_notation_matches_fdi_universal_and_palmer(): void
    {
        $central = OdontogramChart::meta('11');
        $this->assertSame('incisor', $central['type']);
        $this->assertSame('Central Incisor', $central['type_label']);
        $this->assertSame('8', $central['universal']);
        $this->assertSame('1UR', $central['palmer']);
        $this->assertFalse($central['is_primary']);

        $upperLeftCanine = OdontogramChart::meta('23');
        $this->assertSame('canine', $upperLeftCanine['type']);
        $this->assertSame('11', $upperLeftCanine['universal']);
        $this->assertSame('3UL', $upperLeftCanine['palmer']);

        // Geraham pertama kiri bawah (36) = gigi ke-19 pada sistem Universal.
        $molar = OdontogramChart::meta('36');
        $this->assertSame('molar', $molar['type']);
        $this->assertSame('19', $molar['universal']);
        $this->assertSame('6LL', $molar['palmer']);

        // Gigi paling distal kanan bawah (48) = nomor Universal terakhir.
        $this->assertSame('32', OdontogramChart::meta('48')['universal']);
        $this->assertSame('8LR', OdontogramChart::meta('48')['palmer']);
        $this->assertSame('Third Molar', OdontogramChart::meta('48')['type_label']);
    }

    public function test_deciduous_teeth_use_letter_notation(): void
    {
        $tooth = OdontogramChart::meta('55');
        $this->assertTrue($tooth['is_primary']);
        $this->assertSame('molar', $tooth['type']);
        $this->assertSame('Second Molar', $tooth['type_label']);
        // Universal gigi sulung mulai dari molar kedua: 55 = A, 51 = E.
        $this->assertSame('A', $tooth['universal']);
        $this->assertSame('EUR', $tooth['palmer']);

        $this->assertSame('First Molar', OdontogramChart::meta('54')['type_label']);
        $this->assertSame('B', OdontogramChart::meta('54')['universal']);
        $this->assertSame('DUR', OdontogramChart::meta('54')['palmer']);
        $this->assertSame('E', OdontogramChart::meta('51')['universal']);
        $this->assertSame('AUR', OdontogramChart::meta('51')['palmer']);
        $this->assertSame('J', OdontogramChart::meta('61')['universal']);
        $this->assertSame('T', OdontogramChart::meta('81')['universal']);
        $this->assertSame('ELR', OdontogramChart::meta('85')['palmer']);
    }

    public function test_zone_to_surface_mapping_follows_the_arch(): void
    {
        // Kanan-atas: mesial menghadap garis tengah (sisi kanan chart), palatal di dalam.
        $right = OdontogramChart::meta('11')['surfaces'];
        $this->assertSame('mesial', $right['right']);
        $this->assertSame('distal', $right['left']);
        $this->assertSame('buccal', $right['top']);
        $this->assertSame('palatal', $right['bottom']);
        $this->assertSame('incisal', $right['center']);

        // Kiri-atas: kebalikannya.
        $left = OdontogramChart::meta('21')['surfaces'];
        $this->assertSame('distal', $left['right']);
        $this->assertSame('mesial', $left['left']);

        // Bawah: sisi dalam disebut lingual (bukan palatal).
        $lower = OdontogramChart::meta('36')['surfaces'];
        $this->assertSame('lingual', $lower['bottom']);
        $this->assertSame('buccal', $lower['top']);
        $this->assertSame('occlusal', $lower['center']);
    }

    public function test_positions_cover_every_seeded_tooth(): void
    {
        $permanent = collect(['upper', 'lower'])
            ->flatMap(fn ($arch) => array_column(OdontogramChart::positions($arch), 'fdi'))
            ->sort()
            ->values()
            ->all();

        $expected = collect(OdontogramFinding::PERMANENT)->flatten()->sort()->values()->all();
        $this->assertSame($expected, $permanent);

        $deciduous = collect(['upper', 'lower'])
            ->flatMap(fn ($arch) => array_column(OdontogramChart::positions($arch, true), 'fdi'))
            ->sort()
            ->values()
            ->all();

        $expectedPrimary = collect(OdontogramFinding::DECIDUOUS)->flatten()->sort()->values()->all();
        $this->assertSame($expectedPrimary, $deciduous);
    }

    public function test_tooth_rotation_points_the_root_at_the_arch_centre(): void
    {
        $this->assertSame(0.0, OdontogramChart::angle(280.0, 52.0));
        $this->assertSame(180.0, OdontogramChart::angle(280.0, 668.0));
        $this->assertSame(-90.0, OdontogramChart::angle(95.0, 360.0));
        $this->assertSame(90.0, OdontogramChart::angle(531.0, 360.0));
    }

    public function test_fill_prefers_surface_specific_condition_and_falls_back_to_whole(): void
    {
        $zoneMap = ['36' => ['occlusal' => 'caries', 'whole' => 'mobile']];

        $this->assertSame(OdontogramChart::FILLS['caries'], OdontogramChart::fillFor($zoneMap, '36', 'occlusal'));
        // Zona tanpa kondisi spesifik memakai kondisi 'whole'.
        $this->assertSame(OdontogramChart::FILLS['mobile'], OdontogramChart::fillFor($zoneMap, '36', 'mesial'));
        // Gigi tanpa temuan = warna default.
        $this->assertSame(OdontogramChart::DEFAULT_FILL, OdontogramChart::fillFor($zoneMap, '11', 'mesial'));
    }

    public function test_zone_map_keeps_the_latest_finding(): void
    {
        $findings = [
            (object) ['fdi' => '11', 'surface' => 'whole', 'condition' => 'sound', 'created_at' => '2026-01-01 08:00:00'],
            (object) ['fdi' => '11', 'surface' => 'whole', 'condition' => 'crown', 'created_at' => '2026-01-02 08:00:00'],
            (object) ['fdi' => '36', 'surface' => null, 'condition' => 'caries', 'created_at' => '2026-01-01 09:00:00'],
        ];

        $map = OdontogramChart::zoneMap($findings);

        $this->assertSame('crown', $map['11']['whole']);
        // surface null = seluruh gigi.
        $this->assertSame('caries', $map['36']['whole']);
    }
}
