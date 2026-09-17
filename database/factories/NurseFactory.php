<?php

namespace Database\Factories;

use App\Models\Nurse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseFactory extends Factory
{
    protected $model = Nurse::class;

    public function definition(): array
    {
        $user = User::factory()->create([
            'name' => 'Ns. ' . fake()->name('female'),
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
