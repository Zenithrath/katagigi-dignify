<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientAddress;
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
            DiagnosisCodeSeeder::class,
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
            $patients = Patient::factory()->count(15)->create();

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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schedule::factory()->count(15)->create();
            Appointment::factory()->count(15)->create();
            Transaction::factory()->count(12)->create();
            MedicalRecord::factory()->count(12)->create();
        });
    }
}
