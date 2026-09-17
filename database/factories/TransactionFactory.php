<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $patient = Patient::inRandomOrder()->first() ?? Patient::factory()->create();
        $doctor = Doctor::inRandomOrder()->first() ?? Doctor::factory()->create();
        $appointment = Appointment::inRandomOrder()->first() ?? Appointment::factory()->create();

        $price = fake()->randomFloat(2, 100000, 5000000);
        $discount = fake()->randomFloat(2, 0, $price * 0.3);

        return [
            'id' => Str::uuid(),
            'sequence' => fake()->unique()->randomNumber(6),
            'has_down_payment' => fake()->boolean(30),
            'is_endorsed' => fake()->boolean(20),
            'has_installment' => fake()->boolean(20),
            'current_payment' => $price - $discount,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'patient_phone' => $patient->phone,
            'patient_code' => $patient->code,
            'doctor_id' => $doctor->user_id,
            'doctor_nipp' => $doctor->nipp,
            'doctor_name' => $doctor->user->name ?? fake()->name('male'),
            'appointment_id' => $appointment->id,
            'appointment_datetime' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'next_schedule' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'services' => json_encode([
                ['name' => fake()->randomElement(['Scaling', 'Tambal Gigi', 'Cabut Gigi', 'Pembersihan Karang Gigi', 'Orthodonti']), 'price' => $price],
            ]),
            'price' => $price,
            'discount' => $discount,
            'billing' => $price - $discount,
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'card', 'insurance']),
            'canceled_at' => null,
            'cancel_reason' => null,
            'voucher_code' => null,
            'is_locked' => false,
        ];
    }
}
