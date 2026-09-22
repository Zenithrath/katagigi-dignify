<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F4-T3: WhatsApp Official (log driver) + reminder H-1.
 */
class WhatsappTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_normalize_phone(): void
    {
        $this->assertEquals('6281234567890', WhatsappService::normalizePhone('081234567890'));
        $this->assertEquals('6281234567890', WhatsappService::normalizePhone('+62 812-3456-7890'));
        $this->assertNull(WhatsappService::normalizePhone('123'));
        $this->assertNull(WhatsappService::normalizePhone(''));
    }

    public function test_send_records_sent_message_via_log_driver(): void
    {
        $this->assertEquals('log', config('whatsapp.driver'));

        $message = (new WhatsappService)->send('081234567890', 'Halo, tes.');
        $this->assertEquals('SENT', $message->status);
        $this->assertEquals('6281234567890', $message->phone);
        $this->assertNotNull($message->sent_at);
        $this->assertDatabaseHas('whatsapp_messages', [
            'phone' => '6281234567890',
            'status' => 'SENT',
        ]);
    }

    public function test_send_rejects_invalid_phone(): void
    {
        $this->expectExceptionMessage('tidak valid');
        (new WhatsappService)->send('abc', 'Halo.');
    }

    public function test_remind_h1_only_confirmed_tomorrow(): void
    {
        $patient = Patient::factory()->create(['phone' => '628121110001']);
        $doctor = Doctor::factory()->create();
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $confirmed = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'date' => $tomorrow,
            'confirmed_at' => now(),
        ]);
        // Tidak terkonfirmasi → tidak dikirimi.
        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'date' => $tomorrow,
            'confirmed_at' => null,
        ]);
        // Batal → tidak dikirimi.
        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'date' => $tomorrow,
            'confirmed_at' => now(),
            'canceled_at' => now(),
        ]);

        $this->artisan('wa:remind-h1')->assertSuccessful();
        $this->assertEquals(1, DB::table('whatsapp_messages')->count());
        $row = DB::table('whatsapp_messages')->first();
        $this->assertEquals('SENT', $row->status);
        // Nama ikut snapshot appointment (bukan relasi pasien).
        $confirmedName = DB::table('appointments')->where('id', $confirmed->id)->value('patient_name');
        $this->assertStringContainsString($confirmedName, $row->body);
        $this->assertStringContainsString($tomorrow, $row->body);

        // Idempoten: jalan kedua tak menggandakan.
        $this->artisan('wa:remind-h1')->assertSuccessful();
        $this->assertEquals(1, DB::table('whatsapp_messages')->count());
    }

    public function test_whatsapp_page_gated(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();

        $this->actingAs($admin)->get(route('whatsapp.index'))->assertOk();
        $this->actingAs($nurse)->get(route('whatsapp.index'))->assertForbidden();

        $this->actingAs($admin)->post(route('whatsapp.send'), [
            'phone' => '081234567891',
            'body' => 'Kontrol ulang ya.',
        ])->assertRedirect();
        $this->assertDatabaseHas('whatsapp_messages', ['phone' => '6281234567891']);
    }

    public function test_manual_send_failure_shows_error_to_user(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)
            ->from(route('whatsapp.index'))
            ->post(route('whatsapp.send'), ['phone' => 'abc', 'body' => 'Tes.'])
            ->assertRedirect(route('whatsapp.index'))
            ->assertSessionHasErrors('phone');

        // Percobaan dengan nomor invalid tetap tercatat FAILED di outbox.
        $this->assertDatabaseHas('whatsapp_messages', ['status' => 'FAILED']);
    }

    public function test_remind_h1_counts_invalid_phone_as_failed_and_stays_idempotent(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        // Snapshot phone di appointment invalid (factory default menyalin phone pasien).
        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'confirmed_at' => now(),
            'patient_phone' => '123',
        ]);

        $this->artisan('wa:remind-h1')
            ->expectsOutputToContain('0 terkirim, 1 gagal')
            ->assertSuccessful();

        $this->assertDatabaseHas('whatsapp_messages', [
            'patient_id' => $patient->id,
            'status' => 'FAILED',
        ]);

        // Jalan kedua tetap tidak menggandakan (dedup juga untuk yang gagal).
        $this->artisan('wa:remind-h1')->assertSuccessful();
        $this->assertEquals(1, DB::table('whatsapp_messages')->count());
    }

    public function test_remind_h1_is_scheduled(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('wa:remind-h1')
            ->assertSuccessful();
    }
}
