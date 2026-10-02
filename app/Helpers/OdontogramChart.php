<?php

namespace App\Helpers;

use App\Models\OdontogramFinding;

/**
 * Geometri + notasi chart odontogram (lengkung anatomis).
 *
 * Satu sumber kebenaran untuk:
 * - posisi gigi pada lengkung (koordinat SVG) dan rotasinya,
 * - bentuk anatomis per tipe gigi (akar + 4 zona sisi + zona tengah),
 * - pemetaan zona → nama permukaan FDI (mesial/distal/bukal/palatal/lingual/…),
 * - notasi FDI → Universal + Palmer + nama anatomi (untuk tooltip).
 *
 * Dipakai partial chart (blade) dan diuji unit (lihat OdontogramChartTest).
 */
class OdontogramChart
{
    /** Titik pusat lengkung (koordinat viewBox). */
    public const CENTER_X = 280.0;

    public const CENTER_Y = 360.0;

    /** viewBox lengkap kedua lengkung. */
    public const VIEW_BOX_BOTH = '0 0 626 740';

    public const VIEW_BOX_UPPER = '20 0 580 350';

    public const VIEW_BOX_LOWER = '20 370 580 380';

    /** Skala gigi sulung relatif gigi permanen. */
    public const DECIDUOUS_SCALE = 0.72;

    /** Warna zona per kondisi — diselaraskan dengan {@see OdontogramFinding::CHART_COLORS}. */
    public const FILLS = [
        'sound' => '#A7F3D0',
        'caries' => '#FCA5A5',
        'filled' => '#93C5FD',
        'crown' => '#FCD34D',
        'missing' => '#E2E8F0',
        'implant' => '#C4B5FD',
        'denture' => '#5EEAD4',
        'root' => '#FDBA74',
        'mobile' => '#FDE047',
        'fracture' => '#FDA4AF',
    ];

    public const DEFAULT_FILL = '#FFFFFF';

    /** Nama anatomi per digit terakhir FDI (permanen). */
    private const TYPE_LABELS = [
        1 => 'Central Incisor',
        2 => 'Lateral Incisor',
        3 => 'Canine',
        4 => 'First Premolar',
        5 => 'Second Premolar',
        6 => 'First Molar',
        7 => 'Second Molar',
        8 => 'Third Molar',
    ];

    /** Digit 4/5 gigi sulung adalah molar primer, bukan premolar. */
    private const DECIDUOUS_LABELS = [4 => 'First Molar', 5 => 'Second Molar'] + self::TYPE_LABELS;

    /** Posisi gigi permanen pada lengkung: FDI => [x, y]. */
    private const ARCH = [
        '18' => [95.0, 300.0], '17' => [105.0, 240.0], '16' => [124.0, 182.0], '15' => [152.0, 134.0],
        '14' => [188.0, 96.0], '13' => [226.0, 70.0], '12' => [262.0, 56.0], '11' => [296.0, 52.0],
        '21' => [330.0, 52.0], '22' => [364.0, 56.0], '23' => [400.0, 70.0], '24' => [438.0, 96.0],
        '25' => [474.0, 134.0], '26' => [502.0, 182.0], '27' => [521.0, 240.0], '28' => [531.0, 300.0],
        '48' => [95.0, 420.0], '47' => [105.0, 480.0], '46' => [124.0, 538.0], '45' => [152.0, 586.0],
        '44' => [188.0, 624.0], '43' => [226.0, 650.0], '42' => [262.0, 664.0], '41' => [296.0, 668.0],
        '31' => [330.0, 668.0], '32' => [364.0, 664.0], '33' => [400.0, 650.0], '34' => [438.0, 624.0],
        '35' => [474.0, 586.0], '36' => [502.0, 538.0], '37' => [521.0, 480.0], '38' => [531.0, 420.0],
    ];

    /**
     * Gigi sulung memakai posisi gigi permanen yang sepadan (5 gigi terdalam
     * tiap kuadran), lalu diperkecil — cukup akurat secara anatomis dan
     * menghindari duplikasi koordinat.
     *
     * @var array<string, string>
     */
    private const DECIDUOUS_ANCHORS = [
        '55' => '15', '54' => '14', '53' => '13', '52' => '12', '51' => '11',
        '61' => '21', '62' => '22', '63' => '23', '64' => '24', '65' => '25',
        '85' => '45', '84' => '44', '83' => '43', '82' => '42', '81' => '41',
        '71' => '31', '72' => '32', '73' => '33', '74' => '34', '75' => '35',
    ];

    /**
     * Bentuk anatomis per tipe: path akar + 4 path sisi + rect zona tengah.
     *
     * @var array<string, array{root: string, top: string, bottom: string, left: string, right: string, center: array{x: int, y: int, w: int, h: int, rx: int}}>
     */
    private const SHAPES = [
        'incisor' => [
            'root' => 'M -8,10 C -10,20 -5,32 0,34 C 5,32 10,20 8,10 Z',
            'top' => 'M -12,-10 C -6,-12 6,-12 12,-10 L 10,-3 L -10,-3 Z',
            'bottom' => 'M -10,3 L 10,3 L 12,10 C 6,12 -6,12 -12,10 Z',
            'left' => 'M -12,-10 L -10,-3 L -10,3 L -12,10 Z',
            'right' => 'M 12,-10 L 10,-3 L 10,3 L 12,10 Z',
            'center' => ['x' => -10, 'y' => -3, 'w' => 20, 'h' => 6, 'rx' => 1],
        ],
        'canine' => [
            'root' => 'M -9,10 C -12,24 -6,38 0,40 C 6,38 12,24 9,10 Z',
            'top' => 'M -11,-7 L 0,-13 L 11,-7 L 7,-3 L -7,-3 Z',
            'bottom' => 'M -7,3 L 7,3 L 11,7 L 0,11 L -11,7 Z',
            'left' => 'M -11,-7 L -7,-3 L -7,3 L -11,7 Z',
            'right' => 'M 11,-7 L 7,-3 L 7,3 L 11,7 Z',
            'center' => ['x' => -7, 'y' => -3, 'w' => 14, 'h' => 6, 'rx' => 2],
        ],
        'premolar' => [
            'root' => 'M -10,10 C -13,22 -8,34 0,36 C 8,34 13,22 10,10 Z',
            'top' => 'M -12,-10 C -6,-13 6,-13 12,-10 L 8,-4 L -8,-4 Z',
            'bottom' => 'M -8,4 L 8,4 L 12,10 C 6,13 -6,13 -12,10 Z',
            'left' => 'M -12,-10 L -8,-4 L -8,4 L -12,10 Z',
            'right' => 'M 12,-10 L 8,-4 L 8,4 L 12,10 Z',
            'center' => ['x' => -8, 'y' => -4, 'w' => 16, 'h' => 8, 'rx' => 2],
        ],
        'molar' => [
            'root' => 'M -12,10 C -16,22 -18,34 -10,36 C -6,36 -4,22 0,16 C 4,22 6,36 10,36 C 18,34 16,22 12,10 Z',
            'top' => 'M -14,-12 C -7,-15 7,-15 14,-12 L 9,-5 L -9,-5 Z',
            'bottom' => 'M -9,5 L 9,5 L 14,12 C 7,15 -7,15 -14,12 Z',
            'left' => 'M -14,-12 L -9,-5 L -9,5 L -14,12 Z',
            'right' => 'M 14,-12 L 9,-5 L 9,5 L 14,12 Z',
            'center' => ['x' => -9, 'y' => -5, 'w' => 18, 'h' => 10, 'rx' => 2],
        ],
    ];

    /**
     * Daftar gigi siap render untuk satu lengkung.
     *
     * @return list<array{fdi: string, x: float, y: float, scale: float}>
     */
    public static function positions(string $arch, bool $deciduous = false): array
    {
        $rows = [];
        foreach ($deciduous ? self::DECIDUOUS_ANCHORS : self::ARCH as $fdi => $anchor) {
            [$x, $y] = $deciduous ? self::ARCH[$anchor] : $anchor;
            if (! self::belongsToArch($fdi, $arch)) {
                continue;
            }
            $rows[] = [
                'fdi' => (string) $fdi,
                'x' => (float) $x,
                'y' => (float) $y,
                'scale' => $deciduous ? self::DECIDUOUS_SCALE : 1.0,
            ];
        }

        return $rows;
    }

    private static function belongsToArch(string $fdi, string $arch): bool
    {
        $isUpper = in_array((int) $fdi[0], [1, 2, 5, 6], true);

        return $arch === 'upper' ? $isUpper : ! $isUpper;
    }

    /** Sudut rotasi gigi agar akar mengarah ke pusat lengkung (derajat). */
    public static function angle(float $x, float $y): float
    {
        return round(rad2deg(atan2($x - self::CENTER_X, -(($y - self::CENTER_Y)))), 2);
    }

    public static function shape(string $type): array
    {
        return self::SHAPES[$type] ?? self::SHAPES['incisor'];
    }

    public static function typeOf(string $fdi): string
    {
        $index = (int) substr($fdi, -1);

        return match (true) {
            $index >= 6 => 'molar',
            $index >= 4 => self::isPrimary($fdi) ? 'molar' : 'premolar',
            $index === 3 => 'canine',
            default => 'incisor',
        };
    }

    public static function isPrimary(string $fdi): bool
    {
        return (int) $fdi[0] >= 5;
    }

    /**
     * Metadata lengkap satu gigi (notasi + zona permukaan).
     *
     * @return array{
     *     fdi: string, type: string, type_label: string, universal: string, palmer: string,
     *     quadrant: int, is_primary: bool,
     *     surfaces: array{top: string, bottom: string, left: string, right: string, center: string}
     * }
     */
    public static function meta(string $fdi): array
    {
        $fdi = trim($fdi);
        $quadrant = (int) $fdi[0];
        $index = (int) substr($fdi, -1);
        $isPrimary = self::isPrimary($fdi);
        $type = self::typeOf($fdi);

        return [
            'fdi' => $fdi,
            'type' => $type,
            'type_label' => ($isPrimary ? self::DECIDUOUS_LABELS : self::TYPE_LABELS)[$index] ?? 'Tooth',
            'universal' => self::universal($quadrant, $index, $isPrimary),
            'palmer' => self::palmer($quadrant, $index, $isPrimary),
            'quadrant' => $quadrant,
            'is_primary' => $isPrimary,
            'surfaces' => self::surfacesFor($quadrant, $index),
        ];
    }

    /**
     * Nomor sistem Universal (1–32) atau huruf A–T untuk gigi sulung.
     * Sistem Universal gigi sulung berjalan dari molar kedua ke incisor sentral
     * (A = 55, E = 51), sehingga urutannya "terbalik" dari FDI.
     */
    public static function universal(int $quadrant, int $index, bool $isPrimary = false): string
    {
        if ($isPrimary) {
            $fromMolar = 6 - $index;

            return chr(64 + match ($quadrant) {
                5 => $fromMolar,
                6 => 5 + $fromMolar,
                7 => 10 + $fromMolar,
                default => 15 + $fromMolar,
            });
        }

        return (string) match ($quadrant) {
            1 => 9 - $index,
            2 => 8 + $index,
            3 => 25 - $index,
            default => 24 + $index,
        };
    }

    /** Notasi Palmer (mis. 8UR / 1UL / AUR untuk gigi sulung). */
    public static function palmer(int $quadrant, int $index, bool $isPrimary = false): string
    {
        $suffix = match ($quadrant) {
            1, 5 => 'UR',
            2, 6 => 'UL',
            3, 7 => 'LL',
            default => 'LR',
        };

        return ($isPrimary ? chr(64 + $index) : $index).$suffix;
    }

    /**
     * Pemetaan zona geometri → nama permukaan FDI.
     *
     * Karena gigi diputar mengikuti lengkung, sumbu lokal +y selalu mengarah
     * ke pusat lengkung: sisi luar = bukal, sisi dalam = palatal (rahang atas)
     * atau lingual (rahang bawah). Sisi mesial selalu menghadap garis tengah.
     *
     * @return array{top: string, bottom: string, left: string, right: string, center: string}
     */
    public static function surfacesFor(int $quadrant, int $index): array
    {
        $isUpper = in_array($quadrant, [1, 2, 5, 6], true);
        $patientRight = in_array($quadrant, [1, 4, 5, 8], true);

        return [
            'top' => 'buccal',
            'bottom' => $isUpper ? 'palatal' : 'lingual',
            'left' => $patientRight ? 'distal' : 'mesial',
            'right' => $patientRight ? 'mesial' : 'distal',
            'center' => $index >= 4 ? 'occlusal' : 'incisal',
        ];
    }

    /**
     * Warna fill satu zona dari peta temuan (surface spesifik menang,
     * fallback ke temuan 'whole').
     *
     * @param  array<string, array<string, string>>  $zoneMap
     */
    public static function fillFor(array $zoneMap, string $fdi, string $surface): string
    {
        $condition = $zoneMap[$fdi][$surface] ?? $zoneMap[$fdi]['whole'] ?? null;

        return self::FILLS[$condition] ?? self::DEFAULT_FILL;
    }

    /**
     * Rangkum temuan visit menjadi peta zona `fdi => surface => condition`
     * (temuan terbaru menang).
     *
     * @param  iterable<object{fdi: string, surface: ?string, condition: string, created_at: mixed}>  $findings
     * @return array<string, array<string, string>>
     */
    public static function zoneMap(iterable $findings): array
    {
        $map = [];
        foreach (collect($findings)->sortBy('created_at') as $finding) {
            $map[$finding->fdi][$finding->surface ?: 'whole'] = $finding->condition;
        }

        return $map;
    }
}
