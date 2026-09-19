<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('branches')->updateOrInsert(
            ['code' => 'CBG-01'],
            [
                'id' => DB::table('branches')->where('code', 'CBG-01')->value('id') ?? Uuid::uuid4()->toString(),
                'org' => 'Klinik Kata Gigi',
                'name' => 'Cabang Utama',
                'address' => null,
                'phone' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
