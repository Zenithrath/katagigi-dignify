<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4.2: kamus KFA (Katalog Farmasi & Alat Kesehatan) lokal untuk autocomplete
 * kode obat/BHP di resep. Seeder mengisi entri umum layanan gigi; impor penuh
 * kamus KFA nasional disarankan lewat CSV resmi Kemenkes:
 * letakkan database/data/kfa.csv (kolom: kode,nama,bentuk, adalah_obat) lalu seeder ulang.
 */
class MasterKfaSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('master_kfa')) {
            return;
        }

        $items = [
            // Obat (kfa: obat) — contoh yang lazim di layanan gigi.
            ['code' => 'N02BE01', 'name' => 'Paracetamol', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'N02BA01', 'name' => 'Acetylsalicylic Acid (Asam Asetilsalisilat)', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'N02AJ06', 'name' => 'Paracetamol + Codeine', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'M01AE01', 'name' => 'Ibuprofen', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'M01AB05', 'name' => 'Mefenamic Acid (Asam Mefenamat)', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'M01AB15', 'name' => 'Natrium Diclofenac', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'J01CA04', 'name' => 'Amoxicillin', 'dosage_form' => 'Kapsul', 'is_drug' => true],
            ['code' => 'J01CR02', 'name' => 'Amoxicillin + Clavulanic Acid', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'J01FA09', 'name' => 'Clarithromycin', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'J01FA10', 'name' => 'Azithromycin', 'dosage_form' => 'Kapsul', 'is_drug' => true],
            ['code' => 'J01MA12', 'name' => 'Ciprofloxacin', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'J01XD01', 'name' => 'Metronidazole', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'J01XD02', 'name' => 'Tinidazole', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'H02AB06', 'name' => 'Dexamethasone (Deksametason)', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'R06AD07', 'name' => 'Dimenhydrinate (Dimenhidrinat)', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'A03FA05', 'name' => 'Omeprazole (Omeprazol)', 'dosage_form' => 'Kapsul', 'is_drug' => true],
            ['code' => 'B03BA03', 'name' => 'Asam Folat', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'C07AB02', 'name' => 'Bisoprolol', 'dosage_form' => 'Tablet', 'is_drug' => true],
            ['code' => 'N03AX16', 'name' => 'Gabapentin', 'dosage_form' => 'Kapsul', 'is_drug' => true],
            ['code' => 'N02CX01', 'name' => 'Amitriptyline (Amitriptilin)', 'dosage_form' => 'Tablet', 'is_drug' => true],
            // BHP / antiseptik gigi & mulut.
            ['code' => 'B05CA04', 'name' => 'Chlorhexidine (Klorheksidin) Obat Kumur', 'dosage_form' => 'Larutan', 'is_drug' => true],
            ['code' => 'B05CB03', 'name' => 'Povidone Iodine (Povidon Iodin) Mouthwash', 'dosage_form' => 'Larutan', 'is_drug' => true],
            ['code' => 'G01AX03', 'name' => 'Nystatin (Nistatin) Oral Suspension', 'dosage_form' => 'Suspensi', 'is_drug' => true],
            ['code' => 'N01BA01', 'name' => 'Lidocaine (Lidokain)', 'dosage_form' => 'Injeksi', 'is_drug' => true],
            ['code' => 'N01BB02', 'name' => 'Articaine (Artikain) + Adrenalin', 'dosage_form' => 'Karpu', 'is_drug' => true],
            ['code' => 'N01BX04', 'name' => 'Benzocaine (Benzoain) Topical', 'dosage_form' => 'Salep', 'is_drug' => true],
            // Alat kesehatan (BHP non-obat).
            ['code' => 'AKL-KAPAS', 'name' => 'Kapas Steril', 'dosage_form' => null, 'is_drug' => false],
            ['code' => 'AKL-GAUZE', 'name' => 'Kasa Steril', 'dosage_form' => null, 'is_drug' => false],
            ['code' => 'AKL-NITRIL', 'name' => 'Sarung Tangan Nitril', 'dosage_form' => null, 'is_drug' => false],
            ['code' => 'AKL-BARRIER', 'name' => 'Barrier Plastik Klinis', 'dosage_form' => null, 'is_drug' => false],
            ['code' => 'AKL-JARUM', 'name' => 'Jarum Anestesi', 'dosage_form' => null, 'is_drug' => false],
            ['code' => 'AKL-SUTUR', 'name' => 'Benang Sutura', 'dosage_form' => null, 'is_drug' => false],
        ];

        foreach ($items as $item) {
            DB::table('master_kfa')->updateOrInsert(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'dosage_form' => $item['dosage_form'],
                    'is_drug' => $item['is_drug'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
