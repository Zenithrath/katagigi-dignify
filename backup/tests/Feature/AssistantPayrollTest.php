<?php

namespace Tests\Feature;

use App\Models\Nurse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Payroll asisten: tanggal merah, absensi jam kerja, rekap lembur, YoY.
 */
class AssistantPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_manages_holidays_nurse_cannot(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();

        $this->actingAs($admin)->post(route('holidays.store'), [
            'date' => date('Y') . '-08-17',
            'name' => 'HUT RI',
        ])->assertRedirect();
        $this->assertDatabaseHas('holidays', ['name' => 'HUT RI']);

        $this->actingAs($nurse)->post(route('holidays.store'), [
            'date' => date('Y') . '-12-25',
            'name' => 'Natal',
        ])->assertForbidden();
    }

    public function test_nurse_records_own_hours_admin_records_any(): void
    {
        $nurseUser = User::where('email', 'nurse@gmail.com')->first();
        Nurse::firstOrCreate(['user_id' => $nurseUser->id], ['nipp' => 'N001']);
        $admin = User::where('email', 'admin@gmail.com')->first();

        // Perawat catat milik sendiri (tanpa permission manage).
        $this->actingAs($nurseUser)->post(route('attendances.store'), [
            'date' => date('Y-m-d'),
            'clock_in' => '08:00',
            'clock_out' => '20:45',
            'scheduled_end' => '20:00',
        ])->assertRedirect();
        $this->assertDatabaseHas('nurse_attendances', [
            'user_id' => $nurseUser->id,
        ]);

        // Admin catat untuk perawat tersebut.
        $this->actingAs($admin)->post(route('attendances.store'), [
            'user_id' => $nurseUser->id,
            'date' => date('Y-m-d'),
            'clock_in' => '08:00',
            'clock_out' => '21:00',
            'scheduled_end' => '20:00',
        ])->assertRedirect();
    }

    public function test_payroll_reflects_forty_five_minute_rule(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $nurseUser = User::where('email', 'nurse@gmail.com')->first();
        Nurse::firstOrCreate(['user_id' => $nurseUser->id], ['nipp' => 'N001']);

        $this->actingAs($nurseUser)->post(route('attendances.store'), [
            'date' => date('Y-m-d'),
            'clock_in' => '08:00',
            'clock_out' => '20:45',
            'scheduled_end' => '20:00',
        ])->assertRedirect();

        $month = date('Y-m');
        $response = $this->actingAs($manajemen)->get(route('assistant-payroll.index', ['month' => $month]));
        $response->assertOk();
        // 45 menit => 1 jam x Rp15.000
        $response->assertSee('Rp15.000');
    }

    public function test_yoy_report_accessible_with_turnover_permission(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();

        // Semua peran membaca turnover (manajemen/admin/dokter/perawat).
        $this->actingAs($manajemen)->get(route('revenue-report.index'))
            ->assertOk()
            ->assertSee('Year-on-Year');
    }
}
