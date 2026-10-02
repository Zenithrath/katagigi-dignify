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
            'code' => 'PSG-'.strtoupper(fake()->bothify('???#')),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08'.fake()->numerify('##########'),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'birth_place' => fake()->city(),
            'nik' => fake()->numerify('################'),
            'ihs_id' => fake()->numerify('############'),
            'religion' => fake()->randomElement(['ISLAM', 'CHRISTIANITY', 'CATHOLIC', 'HINDUISM', 'BUDDHISM', 'KONGHUCHU', 'OTHER']),
            'gender' => fake()->randomElement(['MALE', 'FEMALE']),
            'picture' => null,
            'sosmed' => null,
            'satusehat_consent' => fake()->boolean(),
        ];
    }

    /**
     * Pasien data lengkap: NIK 16 digit, HP, tgl lahir, IHS, consent —
     * siap bridging SATUSEHAT.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => '08'.fake()->numerify('##########'),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'nik' => fake()->numerify('################'),
            'ihs_id' => fake()->numerify('P############'),
            'satusehat_consent' => true,
        ]);
    }

    /**
     * Pasien data belum lengkap: tanpa NIK & tanpa consent —
     * muncul di tab "Belum Lengkap" master pasien.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'nik' => null,
            'ihs_id' => null,
            'satusehat_consent' => false,
        ]);
    }
}
