<?php

namespace Database\Seeders;

use App\Models\DiagnosisCode;
use Illuminate\Database\Seeder;

class DiagnosisCodeSeeder extends Seeder
{
    /**
     * Contoh awal master gigi 3 sistem + sinonim bahasa awam.
     * Produksi: impor penuh dari terminologi Kemenkes (ICD-10, ICD-9CM) + subset SNOMED gigi.
     */
    public function run(): void
    {
        $rows = [
            [
                'system' => 'ICD10', 'code' => 'K02.1', 'category' => 'konservasi',
                'display_id' => 'Karies dentin (gigi berlubang sampai lapisan dentin)',
                'display_en' => 'Dental caries of dentine',
                'keywords' => ['gigi berlubang', 'karies', 'lubang gigi', 'ngilu makan manis'],
            ],
            [
                'system' => 'ICD10', 'code' => 'K04.7', 'category' => 'konservasi',
                'display_id' => 'Abses periapikal (nanah di ujung akar gigi)',
                'display_en' => 'Periapical abscess without sinus',
                'keywords' => ['nanah', 'abses', 'bengkak pipi', 'nyeri berdenyut'],
            ],
            [
                'system' => 'ICD10', 'code' => 'K05.1', 'category' => 'periodonsia',
                'display_id' => 'Gingivitis kronis (radang gusi menahun)',
                'display_en' => 'Chronic gingivitis',
                'keywords' => ['gusi berdarah', 'radang gusi', 'gusi bengkak', 'bau mulut'],
            ],
            [
                'system' => 'ICD9', 'code' => '23.19', 'category' => 'bedah-mulut',
                'display_id' => 'Pencabutan gigi (ekstraksi)',
                'display_en' => 'Other surgical extraction of tooth',
                'keywords' => ['cabut gigi', 'ekstraksi', 'cabut'],
            ],
            [
                'system' => 'ICD9', 'code' => '23.2', 'category' => 'konservasi',
                'display_id' => 'Penambalan gigi (restorasi)',
                'display_en' => 'Restoration of tooth by filling',
                'keywords' => ['tambal gigi', 'tampal', 'restorasi'],
            ],
            [
                'system' => 'SNOMED', 'code' => '3723001', 'category' => 'ortodonsia',
                'display_id' => 'Gigi berjejal (crowding)',
                'display_en' => 'Crowded teeth',
                'keywords' => ['gigi berjejal', 'gigi bertumpuk', 'gigi tidak rapi'],
            ],
        ];

        foreach ($rows as $row) {
            DiagnosisCode::updateOrCreate(
                ['system' => $row['system'], 'code' => $row['code']],
                [...$row, 'source' => 'seed-awal', 'version' => 'v1', 'is_active' => true]
            );
        }
    }
}
