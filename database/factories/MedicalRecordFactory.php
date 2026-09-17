<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        $patient = Patient::inRandomOrder()->first() ?? Patient::factory()->create();
        $doctor = Doctor::inRandomOrder()->first() ?? Doctor::factory()->create();
        $appointment = Appointment::inRandomOrder()->first() ?? Appointment::factory()->create();

        $startHour = fake()->numberBetween(8, 16);
        $endHour = $startHour + fake()->numberBetween(1, 2);

        return [
            'id' => Str::uuid(),
            'patient_id' => $patient->id,
            'patient_code' => $patient->code,
            'patient_name' => $patient->name,
            'patient_phone' => $patient->phone,
            'patient_address' => fake()->address(),
            'doctor_id' => $doctor->user_id,
            'doctor_name' => $doctor->user->name ?? fake()->name('male'),
            'doctor_nipp' => $doctor->nipp,
            'doctor_niptk' => $doctor->niptk,
            'appointment_id' => $appointment->id,
            'appointment_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'time_start' => sprintf('%02d:00:00', $startHour),
            'time_end' => sprintf('%02d:00:00', min($endHour, 20)),
            'services' => json_encode([
                ['name' => fake()->randomElement(['Scaling', 'Tambal Gigi', 'Cabut Gigi', 'Pembersihan Karang Gigi', 'Root Canal', 'Crown', 'Veneer']), 'price' => fake()->randomFloat(2, 100000, 5000000)],
            ]),
            'anamnesis' => fake()->sentence(4),
            'diagnosis' => fake()->sentence(3),
            'therapy' => fake()->sentence(4),
            'prescription' => fake()->sentence(3),
            'checkup_result' => null,
            'next_schedule' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'price' => fake()->randomFloat(2, 100000, 5000000),
            'discount' => fake()->randomFloat(2, 0, 100000),
            'billing' => fake()->randomFloat(2, 100000, 5000000),
            'promat' => fake()->randomElement(['PROMAT', 'NO PROMAT']),
            'blood_pressure' => fake()->numerify('##/##'),
            'cooperativity' => fake()->randomElement(['COOPERATIVE', 'LESS COOPERATIVE', 'NOT COOPERATIVE']),
            'image_before' => null,
            'image_after' => null,
        ];
    }
}
