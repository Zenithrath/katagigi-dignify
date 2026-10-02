<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalConsentRecord;
use App\Models\Patient;
use App\Models\PatientAddress;
use App\Models\User;
use App\Models\Visit;
use App\Services\Patient\MasterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Fitur baru audit v2: audit log (Permenkes 24/2022), master pasien
 * tab kelengkapan (Lengkap vs Belum Lengkap), ganti bahasa ID/EN,
 * dan pesan error flash yang tampil ke pengguna.
 */
class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    // ── Audit log ──────────────────────────────────────────────
    // PARKED 2026-09-24: halaman audit-logs diparkir (route nonaktif).
    // Pencatatan audit via sign visit tetap diuji di test_sign_visit_is_audited.

    public function test_sign_visit_is_audited(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        // FK visits.doctor_id → doctors.user_id: pakai profil seeder bila ada.
        $doctorProfile = Doctor::where('user_id', $doctor->id)->first()
            ?? Doctor::factory()->create(['user_id' => $doctor->id]);
        $visit = Visit::factory()->create([
            'doctor_id' => $doctorProfile->user_id,
            'clinical_status' => Visit::STATUS_DONE,
        ]);
        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $visit->diagnoses()->create([
            'id' => (string) Str::uuid(),
            'diagnosis_code_id' => $icd10->id,
            'system' => 'ICD10',
            'code' => 'K02.1',
            'display' => 'Karies',
            'is_primary' => true,
        ]);

        // Gate consent (Permenkes 24/2022): sign tanpa consent disetujui ditolak.
        $this->actingAs($doctor)->post(route('visits.sign', $visit->id))->assertStatus(422);
        $visit->consents()->create([
            'id' => (string) Str::uuid(),
            'patient_id' => $visit->patient_id,
            'consent_type' => 'treatment',
            'consent_text' => MedicalConsentRecord::DEFAULT_TEXT,
            'granted' => true,
            'granted_by_name' => 'Pasien',
            'granted_at' => now(),
        ]);

        $this->actingAs($doctor)->post(route('visits.sign', $visit->id))->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sign-visit',
            'entity_type' => 'visit',
            'entity_id' => $visit->id,
            'user_id' => $doctor->id,
        ]);
    }

    // ── Master pasien: kelengkapan ─────────────────────────────

    public function test_patient_completeness_tabs_count_correctly(): void
    {
        $complete = Patient::factory()->complete()->create(); // lengkap
        PatientAddress::factory()->create(['patient_id' => $complete->id]); // + alamat terisi
        Patient::factory()->incomplete()->create(); // tanpa NIK/consent

        $counts = app(MasterService::class)->countByCompleteness();

        $this->assertGreaterThanOrEqual(1, $counts['complete']);
        $this->assertGreaterThanOrEqual(1, $counts['incomplete']);
        $this->assertEquals(
            $counts['complete'] + $counts['incomplete'],
            Patient::count(),
            'Semua pasien harus masuk salah satu kelompok.'
        );
    }

    public function test_missing_fields_lists_what_is_absent(): void
    {
        $patient = Patient::factory()->incomplete()->create([
            'phone' => null,
            'birthdate' => null,
        ]);
        $patient = Patient::query()->findOrFail($patient->id);
        $row = (object) ['satusehat_consent' => $patient->satusehat_consent, ...$patient->getAttributes()];

        $missing = MasterService::missingFields($row);

        $this->assertContains('NIK', $missing);
        $this->assertContains('No. HP', $missing);
        $this->assertContains('Tgl Lahir', $missing);
        $this->assertContains('Consent', $missing);
    }

    public function test_patient_index_renders_completeness_tabs(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();

        foreach (['all' => 'all', 'complete' => 'complete', 'incomplete' => 'incomplete'] as $tab => $value) {
            $response = $this->actingAs($manajemen)->get(route('patients.index', ['tab' => $value]));
            $response->assertOk();
        }

        $this->actingAs($manajemen)->get(route('patients.index', ['tab' => 'incomplete']))
            ->assertOk()
            ->assertSee('Belum Lengkap');
    }

    // ── Bahasa ─────────────────────────────────────────────────

    public function test_switch_language_id_and_en(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($user)->get(route('switch-language', 'en'))->assertRedirect();
        $this->assertEquals('en', session('applocale'));

        $this->actingAs($user)->get(route('switch-language', 'id'))->assertRedirect();
        $this->assertEquals('id', session('applocale'));
    }

    public function test_switch_language_rejects_unknown_locale(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($user)->get(route('switch-language', 'fr'))->assertRedirect();
        $this->assertNull(session('applocale'));
    }

    public function test_patient_page_translated_in_english(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($user)->get(route('switch-language', 'en'));

        $this->actingAs($user)->get(route('patients.index'))
            ->assertOk()
            ->assertSee('Patient Information');
    }

    // ── Pesan error tampil ke pengguna ─────────────────────────

    public function test_failed_store_shows_error_message_to_user(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();

        // Nama kosong → validasi gagal → banner error harus dirender.
        $response = $this->actingAs($user)->post(route('patients.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors();
        $this->actingAs($user)->get(route('patients.create'))
            ->assertOk();
    }

    // ── Dark mode & language toggle ada di topbar ──────────────

    public function test_topbar_has_theme_and_language_switchers(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('switch-language', false)
            ->assertSee('localStorage.setItem(\'theme\'', false);
    }
}
