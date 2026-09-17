<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClinicFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_full_flow_patient_to_approved_cancellation(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();

        // 1. Dokter (langsung DB + role, seperti dibuat manajemen)
        $doctorUser = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Dr Flow',
            'email' => 'drflow@mail.com',
            'password' => 'password',
        ]);
        $doctorUser->assignRole('doctor');
        DB::table('doctors')->insert([
            'user_id' => $doctorUser->id,
            'nipp' => 'FLOW001',
            'niptk' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = DB::table('services')->where('is_active', true)->first();
        $this->assertNotNull($service, 'Seeder layanan harus ada');

        // 2. Jadwal Senin 08:00-15:00
        $date = date('Y-m-d', strtotime('next monday'));
        $this->actingAs($admin)->post(route('schedules.store'), [
            'doctor_id' => $doctorUser->id,
            'day' => 'MONDAY',
            'start_time' => '08:00',
            'end_time' => '15:00',
        ])->assertRedirect();
        $this->assertDatabaseHas('schedules', ['doctor_id' => $doctorUser->id, 'day' => 'MONDAY']);

        // 3. Pasien
        $this->actingAs($admin)->post(route('patients.store'), [
            'name' => 'Pasien Flow',
            'phone' => '081234567890',
            'gender' => 'MALE',
            'street' => 'Jl Flow',
            'village' => 'V',
            'district' => 'D',
            'regency' => 'R',
            'province' => 'P',
            'zip_code' => '70111',
            'tonarigumi' => '01/02',
        ])->assertRedirect();
        $patient = DB::table('patients')->where('name', 'Pasien Flow')->first();
        $this->assertNotNull($patient);

        // 4. Appointment + konfirmasi
        $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->id,
            'service_id' => [$service->id],
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ])->assertRedirect(route('appointments.index'));
        $appointment = DB::table('appointments')->where('patient_id', $patient->id)->first();
        $this->assertNotNull($appointment);

        $this->actingAs($admin)->get(route('appointments.confirm', $appointment->id))->assertRedirect();
        $this->assertNotNull(DB::table('appointments')->where('id', $appointment->id)->first()->confirmed_at);

        // 5. Rekam medis oleh dokter
        $this->actingAs($doctorUser)->post(route('medical-records.store'), [
            'appointment_id' => $appointment->id,
            'service_id' => [$service->id],
            'service_price' => [$service->lower_price],
            'service_quantity' => [1],
            'service_discount' => [0],
            'checkup_result' => 'Hasil periksa',
            'anamnesis' => 'Anamnesis',
            'diagnosis' => 'K02.1 Karies dentin',
            'therapy' => 'Penambalan',
            'prescription' => 'Paracetamol',
            'promat' => 'NO PROMAT',
            'blood_pressure' => '120/80',
            'cooperativity' => 'COOPERATIVE',
            'price' => $service->lower_price,
            'discount' => 0,
            'billing' => $service->lower_price,
            'next_schedule' => '',
            'image_before' => [UploadedFile::fake()->image('before.jpg')],
            'image_after' => [UploadedFile::fake()->image('after.jpg')],
        ])->assertRedirect();
        $this->assertDatabaseHas('medical_records', ['appointment_id' => $appointment->id]);

        // 6. Transaksi oleh admin (kasir)
        $response = $this->actingAs($admin)->post(route('transactions.store'), [
            'appointment_id' => $appointment->id,
            'service_id' => [$service->id],
            'service_price' => [$service->lower_price],
            'service_quantity' => [1],
            'service_discount' => [0],
            'price' => $service->lower_price,
            'billing' => $service->lower_price,
            'payment_method' => 'CASH',
        ]);
        $response->assertRedirect();
        $transaction = DB::table('transactions')->where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($transaction);
        $this->assertGreaterThan(0, (int) $transaction->sequence, 'Sequence nota harus terisi');

        // 7. Admin usul batal → terkunci; admin tidak bisa batal langsung
        $this->actingAs($admin)->post(route('transactions.cancel', $transaction->id), [
            'cancel_reason' => 'coba langsung',
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('transactions.propose-cancel', $transaction->id), [
            'cancel_reason' => 'salah input',
        ])->assertRedirect();
        $proposal = DB::table('transaction_cancellation_requests')
            ->where('transaction_id', $transaction->id)->first();
        $this->assertEquals('PROPOSED', $proposal->status);
        $this->assertTrue((bool) DB::table('transactions')->where('id', $transaction->id)->first()->is_locked);

        // Usulan ganda ditolak
        $this->actingAs($admin)->post(route('transactions.propose-cancel', $transaction->id), [
            'cancel_reason' => 'lagi',
        ])->assertStatus(422);

        // 8. Manajemen approve → nota batal
        $this->actingAs($manajemen)->post(route('cancellations.approve', $proposal->id), [
            'decision_note' => 'ok',
        ])->assertRedirect();
        $after = DB::table('transactions')->where('id', $transaction->id)->first();
        $this->assertNotNull($after->canceled_at);
        $this->assertFalse((bool) $after->is_locked);
        $this->assertEquals(
            'APPROVED',
            DB::table('transaction_cancellation_requests')->where('id', $proposal->id)->first()->status
        );
    }
}
