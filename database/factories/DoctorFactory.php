<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $user = User::factory()->create([
            'name' => 'Dr. ' . fake()->name('male'),
        ]);

        return [
            'user_id' => $user->id,
            'nipp' => 'NIPP' . fake()->unique()->numerify('######'),
            'niptk' => 'NIPTK' . fake()->unique()->numerify('######'),
            'profile_picture' => null,
            'cover_picture' => null,
        ];
    }
}
