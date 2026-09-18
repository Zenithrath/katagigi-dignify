<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * D-04: enforcement RBAC server-side. Setiap perubahan permission wajib
 * ada test positif + negatif via URL langsung (ROADMAP aturan 3).
 * Ekspektasi merujuk PRD §4 + kriteria penerimaan Fase 1.
 */
class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_nurse_cannot_delete_patient_but_manajemen_can(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $patient = DB::table('patients')->first();
        $this->assertNotNull($patient);

        // Negatif: nurse (tanpa delete patient sejak D-04) ditolak via URL langsung.
        $this->actingAs($nurse)
            ->delete(route('patients.destroy', $patient->id))
            ->assertForbidden();

        // Positif: manajemen boleh hapus (soft delete menyusul Fase 2+).
        $this->actingAs($manajemen)
            ->delete(route('patients.destroy', $patient->id))
            ->assertRedirect(route('patients.index'));
        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
    }

    public function test_admin_cannot_write_medical_record(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $ghostId = (string) Str::uuid();

        // authorize() berjalan sebelum lookup model → 403 walau ID fiktif.
        $this->actingAs($admin)->get(route('medical-records.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('medical-records.edit', $ghostId))->assertForbidden();

        // Payload valid (data seed) agar lolos validasi FormRequest lalu kena gate.
        $appointment = DB::table('appointments')->first();
        $service = DB::table('services')->where('is_active', true)->first();
        $dxCode = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $this->assertNotNull($appointment);
        $payload = [
            'appointment_id' => $appointment->id,
            'checkup_result' => 'Hasil',
            'anamnesis' => 'Anamnesis',
            'diagnosis' => 'Catatan',
            'therapy' => 'Terapi',
            'diagnosis_codes_icd10' => [$dxCode->id],
            'promat' => 'NO PROMAT',
            'blood_pressure' => '120/80',
            'cooperativity' => 'COOPERATIVE',
            'price' => $service->lower_price,
            'discount' => 0,
            'billing' => $service->lower_price,
            'service_id' => [$service->id],
            'service_price' => [$service->lower_price],
            'service_quantity' => [1],
            'service_discount' => [0],
            // Slot bayangan terakhir selalu dipangkas Request (pola form JS).
            'image_before_meta' => ['a', ''],
            'image_after_meta' => ['b', ''],
        ];
        $this->actingAs($admin)->post(route('medical-records.store'), $payload)->assertForbidden();
        $this->actingAs($admin)->put(route('medical-records.update', $ghostId), $payload)->assertForbidden();
    }

    public function test_doctor_cannot_open_global_turnover_or_cashier(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();

        // Kasir: doctor tanpa create transaction (payload valid → kena gate, bukan validasi).
        $appointment = DB::table('appointments')->first();
        $service = DB::table('services')->where('is_active', true)->first();
        $this->actingAs($doctor)->post(route('transactions.store'), [
            'appointment_id' => $appointment->id,
            'service_id' => [$service->id],
            'service_price' => [$service->lower_price],
            'service_quantity' => [1],
            'price' => $service->lower_price,
            'billing' => $service->lower_price,
            'payment_method' => 'CASH',
        ])->assertForbidden();

        // Master tarif: doctor tanpa read service.
        $this->actingAs($doctor)->get(route('services.index'))->assertForbidden();
    }

    public function test_cashier_allowed_for_admin_and_nurse(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();

        // Kasir dirangkap admin/nurse sesuai praktik klinik (PRD §1).
        $this->actingAs($admin)->get(route('transactions.create'))->assertOk();
        $this->actingAs($nurse)->get(route('transactions.create'))->assertOk();
    }

    public function test_confirm_appointment_requires_post_and_permission(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $ghostId = (string) Str::uuid();

        // D-04: GET tidak lagi terdaftar untuk konfirmasi.
        $this->actingAs($nurse)->get(route('appointments.index'))->assertOk();
        $this->assertFalse(
            collect(app('router')->getRoutes()->getRoutesByMethod()['GET'] ?? [])
                ->contains(fn ($r) => $r->getName() === 'appointments.confirm')
        );

        // POST tanpa hak update appointment tetap 403 (doctor hanya read).
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $this->assertFalse($doctor->can('update appointment'));
        $this->actingAs($doctor)
            ->post(route('appointments.confirm', $ghostId))
            ->assertForbidden();

        // POST dengan hak (nurse) lolos gate — gagal di service (ID fiktif,
        // update 0 baris) lalu redirect dengan error, bukan 403.
        // Membuktikan gate terbuka untuk role yang benar.
        $this->actingAs($nurse)
            ->post(route('appointments.confirm', $ghostId))
            ->assertRedirect();
    }
}
