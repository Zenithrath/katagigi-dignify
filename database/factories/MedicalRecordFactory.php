<?php

namespace Database\Factories;

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

        $startHour = fake()->numberBetween(8, 16);
        $endHour = $startHour + fake()->numberBetween(1, 2);

        return [
            'id' => Str::uuid(),
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'doctor_id' => $doctor->user_id,
            'doctor_name' => $doctor->user->name ?? fake()->name('male'),
            'date' => fake()->dateTimeBetween('-6 months', 'now'),
            'time_start' => sprintf('%02d:00:00', $startHour),
            'time_end' => sprintf('%02d:00:00', min($endHour, 20)),
            'service' => fake()->randomElement(['Scaling', 'Tambal Gigi', 'Cabut Gigi', 'Pembersihan Karang Gigi', 'Root Canal', 'Crown', 'Veneer']),
            'diagnose' => fake()->sentence(3),
            'therapy' => fake()->sentence(4),
            'prescription' => fake()->sentence(3),
            'next_schedule' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'price' => fake()->randomFloat(2, 100000, 5000000),
            'promat' => fake()->randomElement(['R1', 'R2', 'R3', 'R4', 'L1', 'L2', 'L3', 'L4']),
            'blood_tension' => fake()->numerify('##/##'),
            'cooperative' => fake()->randomElement(['Ya', 'Tidak', 'Kurang']),
            'image_before' => null,
            'image_after' => null,
        ];
    }
}
