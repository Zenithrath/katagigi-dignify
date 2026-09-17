<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
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
            'patient_name' => $patient->name,
            'doctor_id' => $doctor->user_id,
            'doctor_name' => $doctor->user->name ?? fake()->name('male'),
            'schedule_id' => Schedule::inRandomOrder()->first()?->id ?? Schedule::factory()->create()->id,
            'date' => fake()->dateTimeBetween('-1 month', '+3 months'),
            'time_start' => sprintf('%02d:00:00', $startHour),
            'time_end' => sprintf('%02d:00:00', min($endHour, 20)),
            'status' => fake()->randomElement(['pending', 'confirmed', 'completed', 'canceled']),
        ];
    }
}
