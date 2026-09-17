<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => fake()->name('male'),
            'code' => 'PSG-' . strtoupper(fake()->bothify('???#')),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08' . fake()->numerify('##########'),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'birth_place' => fake()->city(),
            'nik' => fake()->numerify('################'),
            'ihs_id' => fake()->numerify('############'),
            'religion' => fake()->randomElement(['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu']),
            'gender' => fake()->randomElement(['male', 'female']),
            'picture' => null,
            'sosmed' => null,
            'satusehat_consent' => fake()->boolean(),
        ];
    }
}
