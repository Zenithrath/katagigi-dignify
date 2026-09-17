<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $endHour = $startHour + fake()->numberBetween(1, 2);

        return [
            'id' => Str::uuid(),
            'doctor_id' => Doctor::inRandomOrder()->first()?->user_id ?? Doctor::factory(),
            'day' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
            'time_start' => sprintf('%02d:00:00', $startHour),
            'time_end' => sprintf('%02d:00:00', min($endHour, 20)),
        ];
    }
}
