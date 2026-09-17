<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $endHour = $startHour + fake()->numberBetween(1, 2);

        $patient = Patient::inRandomOrder()->first() ?? Patient::factory()->create();
        $doctor = Doctor::inRandomOrder()->first() ?? Doctor::factory()->create();

        return [
            'id' => Str::uuid(),
            'patient_id' => $patient->id,
            'patient_code' => $patient->code,
            'patient_name' => $patient->name,
            'patient_phone' => $patient->phone,
            'doctor_id' => $doctor->user_id,
            'doctor_name' => $doctor->user->name ?? fake()->name('male'),
            'doctor_nipp' => $doctor->nipp,
            'doctor_niptk' => $doctor->niptk,
            'date' => fake()->dateTimeBetween('-1 month', '+3 months')->format('Y-m-d'),
            'services' => json_encode([
                ['name' => fake()->randomElement(['Scaling', 'Tambal Gigi', 'Cabut Gigi', 'Pembersihan Karang Gigi', 'Orthodonti']), 'price' => fake()->randomFloat(2, 100000, 5000000)],
            ]),
            'time_start' => sprintf('%02d:00:00', $startHour),
            'time_end' => sprintf('%02d:00:00', min($endHour, 20)),
            'confirmed_at' => null,
            'paid_at' => null,
            'recorded_at' => null,
            'canceled_at' => null,
        ];
    }
}
