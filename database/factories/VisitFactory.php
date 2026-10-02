<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        $patient = Patient::inRandomOrder()->first() ?? Patient::factory()->create();
        $doctor = Doctor::inRandomOrder()->first() ?? Doctor::factory()->create();

        return [
            'id' => (string) Str::uuid(),
            'visit_number' => 'VST-'.date('y').str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'branch_id' => null,
            'patient_id' => $patient->id,
            'appointment_id' => null,
            'doctor_id' => $doctor->user_id,
            'visit_date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'clinical_status' => Visit::STATUS_REGISTERED,
            'billing_status' => Visit::BILLING_UNBILLED,
            'notes' => null,
            'signed_at' => null,
            'signed_by' => null,
        ];
    }
}
