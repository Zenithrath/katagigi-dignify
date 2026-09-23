<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cakupan tulis isi rekam medis per role (PRD §4):
 * doctor+manajemen menulis klinis penuh; nurse mendampingi (anamnesis/vital/OHI/
 * lampiran); admin front-office tidak menulis isi rekam medis.
 */
class ClinicalWriteScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_admin_reads_clinical_pages_but_cannot_write_content(): void
    {
        $admin = $this->user('admin@gmail.com');
        $visit = Visit::factory()->create();

        $this->actingAs($admin)->get(route('visits.show', $visit->id))->assertOk();

        $this->actingAs($admin)->post(route('visits.anamnesis.store', $visit->id), ['chief_complaint' => 'x'])->assertForbidden();
        $this->actingAs($admin)->post(route('visits.odontogram.store', $visit->id), ['fdi' => '11', 'condition' => 'sound'])->assertForbidden();
        $this->actingAs($admin)->post(route('visits.diagnoses.store', $visit->id), [])->assertForbidden();
        $this->actingAs($admin)->post(route('visits.treatments.store', $visit->id), [])->assertForbidden();
        $this->actingAs($admin)->post(route('visits.vitals.store', $visit->id), ['pulse_bpm' => 80])->assertForbidden();
    }

    public function test_nurse_assists_with_anamnesis_and_vitals_only(): void
    {
        $nurse = $this->user('nurse@gmail.com');
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.anamnesis.store', $visit->id), [
            'chief_complaint' => 'Draft asisten',
        ])->assertRedirect();
        $this->actingAs($nurse)->post(route('visits.vitals.store', $visit->id), [
            'pulse_bpm' => 82,
            'temperature_c' => 36.7,
        ])->assertRedirect();
        $this->assertDatabaseHas('vital_signs', ['visit_id' => $visit->id, 'pulse_bpm' => 82]);

        // Tanpa kewenangan final medis.
        $this->actingAs($nurse)->post(route('visits.odontogram.store', $visit->id), ['fdi' => '36', 'condition' => 'caries'])->assertForbidden();
        $this->actingAs($nurse)->post(route('visits.diagnoses.store', $visit->id), [])->assertForbidden();
        $this->actingAs($nurse)->post(route('visits.treatments.store', $visit->id), [])->assertForbidden();
    }

    public function test_doctor_writes_full_clinical_record(): void
    {
        $doctor = $this->user('doctor@gmail.com');
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '36',
            'surface' => 'occlusal',
            'condition' => 'caries',
        ])->assertRedirect();
        $this->assertDatabaseHas('odontogram_findings', [
            'visit_id' => $visit->id,
            'fdi' => '36',
            'surface' => 'occlusal',
            'condition' => 'caries',
        ]);

        $this->actingAs($doctor)->post(route('visits.examination.store', $visit->id), [
            'subjective' => 'Nyeri',
            'assessment' => 'K02.1',
        ])->assertRedirect();
        $this->assertDatabaseHas('examinations', ['visit_id' => $visit->id, 'assessment' => 'K02.1']);
    }

    public function test_consent_can_be_recorded_by_clinic_staff_but_revoked_only_by_doctor_or_manajemen(): void
    {
        $visit = Visit::factory()->create();
        $payload = [
            'consent_text' => 'Setuju tindakan pencabutan.',
            'granted' => true,
            'granted_by_name' => 'Pasien',
        ];

        $this->actingAs($this->user('admin@gmail.com'))
            ->post(route('visits.consents.store', $visit->id), $payload)->assertRedirect();

        $consent = $visit->consents()->firstOrFail();

        // Admin mencatat consent, tetapi tidak boleh mencabutnya.
        $this->actingAs($this->user('admin@gmail.com'))
            ->delete(route('visits.consents.destroy', [$visit->id, $consent->id]))->assertForbidden();

        $this->actingAs($this->user('doctor@gmail.com'))
            ->delete(route('visits.consents.destroy', [$visit->id, $consent->id]))->assertRedirect();
        $this->assertDatabaseMissing('medical_consent_records', ['id' => $consent->id]);
    }

    public function test_manajemen_outranks_every_role_on_clinical_writes(): void
    {
        $manajemen = $this->user('manajemen@gmail.com');
        $visit = Visit::factory()->create();

        $this->actingAs($manajemen)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '11',
            'surface' => 'whole',
            'condition' => 'crown',
        ])->assertRedirect();

        $this->assertDatabaseHas('odontogram_findings', [
            'visit_id' => $visit->id,
            'fdi' => '11',
            'condition' => 'crown',
        ]);
    }
}
