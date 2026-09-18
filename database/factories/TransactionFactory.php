<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
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
            'down_payment_transaction_id' => null,
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
            'nurse_id' => null,
            'nurse_nipp' => null,
            'nurse_name' => null,
            'appointment_id' => $appointment->id,
            'appointment_datetime' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d H:i:s'),
            'next_schedule' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'services' => json_encode([$this->serviceItem($price)]),
            'price' => $price,
            'discount' => $discount,
            'billing' => $price - $discount,
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'card', 'insurance']),
            'canceled_at' => null,
            'cancel_reason' => null,
            'voucher_code' => null,
            'referenced_installment_id' => null,
            'is_locked' => false,
        ];
    }

    /**
     * Bentuk item services disamakan dengan penulis asli
     * (TransactionService::insertTransaction -> pricedServices).
     */
    private function serviceItem(float $price): array
    {
        $svc = DB::table('services')
            ->leftJoin('categories', 'services.category_id', '=', 'categories.id')
            ->select('services.id', 'services.code', 'services.name', 'categories.name as category')
            ->inRandomOrder()
            ->first();

        return [
            'id' => $svc->id ?? (string) Str::uuid(),
            'class' => 'service',
            'price' => $price,
            'quantity' => 1,
            'subtotal' => $price,
            'discount' => 0,
            'code' => $svc->code ?? 'SRV-000',
            'name' => $svc->name ?? fake()->randomElement(['Scaling', 'Tambal Gigi', 'Cabut Gigi', 'Pembersihan Karang Gigi', 'Orthodonti']),
            'category' => $svc->category ?? 'Umum',
        ];
    }
}
