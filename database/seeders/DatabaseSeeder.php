<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed HANYA master & akun — tanpa data dummy.
     * Data operasional (pasien, visit, nota, dll.) diinput sendiri lewat UI
     * saat testing/produksi.
     *
     * Akun bawaan (lihat RolesAndPermissionsSeeder):
     *   manajemen@gmail.com / admin@gmail.com / doctor@gmail.com / nurse@gmail.com
     *   (password sesuai RolesAndPermissionsSeeder).
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            BranchSeeder::class,
            WhatsappTemplateSeeder::class,
            DiagnosisCodeSeeder::class,
            // Master wilayah Kemendagri + kamus KFA + penjamin (Fase 4).
            RegionCodeSeeder::class,
            MasterKfaSeeder::class,
            MasterInsuranceSeeder::class,
            CategoriesSeeder::class,
            ServiceUmumSeeder::class,
            ServiceBedahMulutSeeder::class,
            ServiceKonservasiSeeder::class,
            ServiceOrtodonsiaSeeder::class,
            ServicePedodonsiaSeeder::class,
            ServiceProstodonsiaSeeder::class,
            // Seeder lab prostodonsia (dulu orphan, tak pernah dipanggil).
            ServiceProstodonsiaBASSeeder::class,
            ServiceProstodonsiaKlinikSeeder::class,
            ServiceProstodonsiaAfifSeeder::class,
            ServiceProstodonsiaDeltaSeeder::class,
        ]);
    }
}
