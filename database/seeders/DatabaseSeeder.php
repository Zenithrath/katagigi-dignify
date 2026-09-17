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
            DiagnosisCodeSeeder::class,
            CategoriesSeeder::class,
            ServiceUmumSeeder::class,
            ServiceBedahMulutSeeder::class,
            ServiceKonservasiSeeder::class,
            ServiceOrtodonsiaSeeder::class,
            ServicePedodonsiaSeeder::class,
            ServiceProstodonsiaSeeder::class,
        ]);

        DB::transaction(function () {
            $patients = Patient::factory()->count(15)->create();

            $patients->each(function ($patient) {
                PatientAddress::factory()->create(['patient_id' => $patient->id]);
            });

            Schedule::factory()->count(15)->create();
            Appointment::factory()->count(15)->create();
            Transaction::factory()->count(12)->create();
            MedicalRecord::factory()->count(12)->create();
        });
    }
}
