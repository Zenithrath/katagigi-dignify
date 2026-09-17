<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\PatientAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientAddress>
 */
class PatientAddressFactory extends Factory
{
    protected $model = PatientAddress::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::inRandomOrder()->first()?->id ?? throw new \RuntimeException('No patients found. Create patients first.'),
            'zip_code' => fake()->numerify('#####'),
            'tonarigumi' => fake()->numerify('#####'),
            'street' => fake()->streetAddress(),
            'village' => fake()->citySuffix(),
            'district' => fake()->city(),
            'regency' => fake()->city(),
            'province' => fake()->randomElement([
                'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur',
                'Banten', 'Yogyakarta', 'Sumatera Utara', 'Bali',
            ]),
        ];
    }
}
