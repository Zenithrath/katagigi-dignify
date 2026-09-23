<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            BranchSeeder::class,
            WhatsappTemplateSeeder::class,
            DiagnosisCodeSeeder::class,
            // Fase 4: master wilayah Kemendagri + kamus KFA lokal.
            RegionCodeSeeder::class,
            MasterKfaSeeder::class,
            CategoriesSeeder::class,
            ServiceUmumSeeder::class,
            ServiceBedahMulutSeeder::class,
            ServiceKonservasiSeeder::class,
            ServiceOrtodonsiaSeeder::class,
            ServicePedodonsiaSeeder::class,
            ServiceProstodonsiaSeeder::class,
            // D-06e: seeder lab prostodonsia (dulu orphan, tak pernah dipanggil).
            ServiceProstodonsiaBASSeeder::class,
            ServiceProstodonsiaKlinikSeeder::class,
            ServiceProstodonsiaAfifSeeder::class,
            ServiceProstodonsiaDeltaSeeder::class,
        ]);

        DB::transaction(function () {
            // Kelengkapan data pasien dibuat beragam: ±1/3 lengkap (siap
            // SATUSEHAT), sisanya bervariasi — untuk mendemokan tab
            // "Data Lengkap" vs "Belum Lengkap" di master pasien.
            $patients = Patient::factory()->count(5)->complete()->create()
                ->concat(Patient::factory()->count(5)->incomplete()->create())
                ->concat(Patient::factory()->count(5)->create());

            foreach ($patients as $patient) {
                DB::table('patient_addresses')->insert([
                    'patient_id' => $patient->id,
                    'zip_code' => fake()->numerify('#####'),
                    'tonarigumi' => fake()->numerify('#####'),
                    'street' => fake()->streetAddress(),
                    'village' => fake()->citySuffix(),
                    'district' => fake()->city(),
                    'regency' => fake()->city(),
                    'province' => fake()->randomElement(['DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur']),
                    // Area operasional klinik dapat kode Kemendagri Kalsel.
                    'region_code' => fake()->randomElement(['6371', '6372', '6302', '6306', null]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schedule::factory()->count(15)->create();
            Appointment::factory()->count(15)->create();
            Transaction::factory()->count(12)->create();
            MedicalRecord::factory()->count(12)->create();

            // Data demo siap-review (visit klinis + kode ICD + invoice) di-run
            // terpisah agar tidak tercampur test suite:
            //   php artisan db:seed --class=DemoDataSeeder
        });
    }
}
