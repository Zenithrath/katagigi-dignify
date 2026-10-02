<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Fase 4.3: penjamin bawaan — umum (tunai) + BPJS Kesehatan.
 * Asuransi swasta/korporasi ditambah lewat UI oleh manajemen.
 */
class MasterInsuranceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Umum (Tunai)', 'type' => 'general', 'notes' => 'Bayar mandiri, tanpa penjamin'],
            ['name' => 'BPJS Kesehatan', 'type' => 'government', 'notes' => 'PBI / Mandiri / KK'],
        ] as $row) {
            DB::table('master_insurances')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'id' => DB::table('master_insurances')->where('name', $row['name'])->value('id') ?? (string) Str::uuid(),
                    'type' => $row['type'],
                    'notes' => $row['notes'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
