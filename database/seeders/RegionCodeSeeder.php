<?php

namespace Database\Seeders;

use App\Models\RegionCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fase 4.1: master wilayah Kemendagri. Seeder ini mengisi provinsi + kota
 * (Kalsel dulu — area operasional klinik). Kecamatan/kelurahan dianjurkan
 * diimpor penuh lewat CSV Kemendagri: php artisan db:seed --class=RegionCodeSeeder
 * setelah file resources/data/wilayah.csv tersedia (format: kode,nama,parent).
 */
class RegionCodeSeeder extends Seeder
{
    public function run(): void
    {
        // 38 provinsi (kode Kemendagri 2 digit).
        $provinces = [
            '11' => 'Aceh', '12' => 'Sumatera Utara', '13' => 'Sumatera Barat', '14' => 'Riau',
            '15' => 'Jambi', '16' => 'Sumatera Selatan', '17' => 'Bengkulu', '18' => 'Lampung',
            '19' => 'Kepulauan Bangka Belitung', '21' => 'Kepulauan Riau', '31' => 'DKI Jakarta',
            '32' => 'Jawa Barat', '33' => 'Jawa Tengah', '34' => 'DI Yogyakarta', '35' => 'Jawa Timur',
            '36' => 'Banten', '51' => 'Bali', '52' => 'Nusa Tenggara Barat', '53' => 'Nusa Tenggara Timur',
            '61' => 'Kalimantan Barat', '62' => 'Kalimantan Tengah', '63' => 'Kalimantan Selatan',
            '64' => 'Kalimantan Timur', '65' => 'Kalimantan Utara', '71' => 'Sulawesi Utara',
            '72' => 'Sulawesi Tengah', '73' => 'Sulawesi Selatan', '74' => 'Sulawesi Tenggara',
            '75' => 'Gorontalo', '76' => 'Sulawesi Barat', '81' => 'Maluku', '82' => 'Maluku Utara',
            '91' => 'Papua', '92' => 'Papua Barat', '93' => 'Papua Selatan', '94' => 'Papua Tengah',
            '95' => 'Papua Pegunungan', '96' => 'Papua Barat Daya',
        ];

        foreach ($provinces as $code => $name) {
            RegionCode::updateOrCreate(['code' => $code], [
                'name' => $name,
                'level' => RegionCode::LEVEL_PROVINCE,
                'parent_code' => null,
                'is_active' => true,
            ]);
        }

        // Kota/kabupaten area operasional (Kalimantan Selatan, kode 63xxxx).
        $cities = [
            '6371' => 'Kota Banjarmasin',
            '6372' => 'Kota Banjarbaru',
            '6302' => 'Kab. Tanah Laut',
            '6303' => 'Kab. Tanah Bumbu',
            '6304' => 'Kab. Kotabaru',
            '6305' => 'Kab. Barito Kuala',
            '6306' => 'Kab. Tapin',
            '6307' => 'Kab. Hulu Sungai Selatan',
            '6308' => 'Kab. Hulu Sungai Tengah',
            '6309' => 'Kab. Hulu Sungai Utara',
            '6310' => 'Kab. Balangan',
            '6311' => 'Kab. Tabalong',
            '6312' => 'Kab. Barito Selatan',
            '6313' => 'Kab. Barito Utara',
        ];

        foreach ($cities as $code => $name) {
            RegionCode::updateOrCreate(['code' => $code], [
                'name' => $name,
                'level' => RegionCode::LEVEL_CITY,
                'parent_code' => '63',
                'is_active' => true,
            ]);
        }

        // Impor penuh kec/kel dari CSV bila tersedia (kode,nama,parent).
        $csv = database_path('data/wilayah.csv');
        if (is_file($csv) && ($handle = fopen($csv, 'r')) !== false) {
            DB::transaction(function () use ($handle) {
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) < 3 || ! preg_match('/^\\d{2,10}$/', $row[0])) {
                        continue;
                    }
                    [$code, $name, $parent] = $row;
                    RegionCode::updateOrCreate(['code' => $code], [
                        'name' => $name,
                        'level' => match (strlen($code)) {
                            2 => RegionCode::LEVEL_PROVINCE,
                            4 => RegionCode::LEVEL_CITY,
                            6 => RegionCode::LEVEL_DISTRICT,
                            default => RegionCode::LEVEL_VILLAGE,
                        },
                        'parent_code' => $parent ?: null,
                        'is_active' => true,
                    ]);
                }
            });
            fclose($handle);
        }
    }
}
