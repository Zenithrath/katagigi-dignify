<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DiagnosisCodeSeeder::class,
            CategoriesSeeder::class,
            ServiceUmumSeeder::class,
            ServiceBedahMulutSeeder::class,
            ServiceKonservasiSeeder::class,
            ServiceOrtodonsiaSeeder::class,
            ServicePedodonsiaSeeder::class,
            ServiceProstodonsiaSeeder::class,
        ]);
    }
}
