<?php

namespace Tests\Feature;

use App\Models\MedicalConsentRecord;
use App\Models\RadiologyOrder;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Fitur klinis yang sebelumnya hanya ada di sisi server (tanda vital, OHI-S/DMF-T,
 * radiologi, informed consent) kini punya UI di halaman visit — dan berkas medisnya
 * disimpan di disk private (Permenkes 24/2022), bukan disk publik.
 */
class ClinicalFeatureUiTest extends TestCase
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

    public function test_visit_page_renders_all_clinical_sections(): void
    {
        $visit = Visit::factory()->create();

        $this->actingAs($this->user('doctor@gmail.com'))
            ->get(route('visits.show', $visit->id))
            ->assertOk()
            ->assertSee('Tanda Vital')
            ->assertSee('OHI-S')
            ->assertSee('Radiologi')
            ->assertSee('Informed Consent');
    }

    public function test_doctor_records_vitals_and_nurse_can_update_them(): void
    {
        $visit = Visit::factory()->create();

        $this->actingAs($this->user('doctor@gmail.com'))
            ->post(route('visits.vitals.store', $visit->id), [
                'pulse_bpm' => 78,
                'temperature_c' => 36.8,
                'respiratory_rate' => 16,
                'pregnancy_status' => 'NOT_PREGNANT',
            ])->assertRedirect();

        $this->assertDatabaseHas('vital_signs', ['visit_id' => $visit->id, 'pulse_bpm' => 78]);

        // Simpan ulang (asisten) menimpa baris yang sama, bukan menambah baris baru.
        $this->actingAs($this->user('nurse@gmail.com'))
            ->post(route('visits.vitals.store', $visit->id), ['pulse_bpm' => 82])
            ->assertRedirect();

        $this->assertSame(1, $visit->vitalSign()->count());
        $this->assertSame(82, $visit->fresh()->vitalSign->pulse_bpm);
    }

    public function test_ohis_total_and_interpretation_are_derived(): void
    {
        $visit = Visit::factory()->create();

        $this->actingAs($this->user('doctor@gmail.com'))
            ->post(route('visits.oral-health.store', $visit->id), [
                'ohis_debris' => 1.2,
                'ohis_calculus' => 0.8,
                'd_count' => 3,
                'm_count' => 1,
                'f_count' => 2,
            ])->assertRedirect();

        $ohi = $visit->fresh()->oralHealthIndex;
        $this->assertSame(2.0, (float) $ohi->ohis_total);
        $this->assertSame(6.0, (float) $ohi->dmt_index);
        $this->assertSame('OI000030', $ohi->interpretation()[0]);
        $this->assertSame('Kondisi Gigi Cukup Baik', $ohi->interpretation()[1]);
    }

    public function test_radiology_result_file_uses_private_disk_and_is_cleaned_up(): void
    {
        Storage::fake('local');
        $visit = Visit::factory()->create();
        $doctor = $this->user('doctor@gmail.com');

        $this->actingAs($doctor)->post(route('visits.radiology.store', $visit->id), [
            'modality' => 'DX',
            'body_site' => 'Regio 46',
            'clinical_indication' => 'Karies profunda',
            'priority' => 'routine',
        ])->assertRedirect();

        $order = RadiologyOrder::where('visit_id', $visit->id)->firstOrFail();
        $this->assertSame(RadiologyOrder::STATUS_ORDERED, $order->status);

        $this->actingAs($doctor)->put(route('visits.radiology.result', [$visit->id, $order->id]), [
            'status' => RadiologyOrder::STATUS_COMPLETED,
            'result_text' => 'Tidak ada lesi periapikal',
            'result_file' => UploadedFile::fake()->create('periapikal.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame(RadiologyOrder::STATUS_COMPLETED, $order->status);
        $this->assertNotSame(RadiologyOrder::STATUS_ORDERED, $order->status);
        $this->assertTrue(Storage::disk('local')->exists($order->result_path));

        // Berkas medis hanya lewat signed URL; tautan tanpa tanda tangan ditolak.
        $url = URL::temporarySignedRoute('radiology.result.file', now()->addMinutes(30), ['order' => $order->id]);
        $this->actingAs($doctor)->get($url)->assertOk();
        $this->actingAs($doctor)->get(route('radiology.result.file', $order->id))->assertForbidden();

        // Hapus order ikut menghapus berkasnya (tidak menyisakan sampah).
        $this->actingAs($doctor)->delete(route('visits.radiology.destroy', [$visit->id, $order->id]))->assertRedirect();
        Storage::disk('local')->assertMissing($order->result_path);
    }

    public function test_consent_signature_is_private_and_revocable(): void
    {
        Storage::fake('local');
        $visit = Visit::factory()->create();
        $doctor = $this->user('doctor@gmail.com');

        $this->actingAs($doctor)->post(route('visits.consents.store', $visit->id), [
            'consent_type' => 'surgery',
            'consent_text' => 'Setuju ekstraksi gigi 46.',
            'granted' => 1,
            'granted_by_name' => 'Pasien Uji',
            'granted_by_relation' => 'self',
            'signature' => UploadedFile::fake()->image('ttd.png'),
        ])->assertRedirect();

        $consent = MedicalConsentRecord::where('visit_id', $visit->id)->firstOrFail();
        $this->assertTrue($consent->granted);
        $this->assertNotNull($consent->granted_at);
        $this->assertTrue(Storage::disk('local')->exists($consent->signature_path));

        $url = URL::temporarySignedRoute('consents.signature', now()->addMinutes(30), ['consent' => $consent->id]);
        $this->actingAs($doctor)->get($url)->assertOk();

        $this->actingAs($doctor)->delete(route('visits.consents.destroy', [$visit->id, $consent->id]))->assertRedirect();
        Storage::disk('local')->assertMissing($consent->signature_path);
        $this->assertDatabaseMissing('medical_consent_records', ['id' => $consent->id]);
    }

    public function test_nurse_may_record_consent_but_not_order_radiology_or_revoke(): void
    {
        $visit = Visit::factory()->create();
        $nurse = $this->user('nurse@gmail.com');

        $this->actingAs($nurse)->post(route('visits.radiology.store', $visit->id), [
            'modality' => 'DX',
            'clinical_indication' => 'Kontrol',
        ])->assertForbidden();

        $this->actingAs($nurse)->post(route('visits.consents.store', $visit->id), [
            'consent_text' => 'Setuju.',
            'granted' => 1,
            'granted_by_name' => 'Pasien Uji',
        ])->assertRedirect();

        $consent = MedicalConsentRecord::where('visit_id', $visit->id)->firstOrFail();
        $this->actingAs($nurse)->delete(route('visits.consents.destroy', [$visit->id, $consent->id]))->assertForbidden();
    }

    public function test_signed_visit_locks_new_sections(): void
    {
        $visit = Visit::factory()->create([
            'clinical_status' => Visit::STATUS_SIGNED,
        ]);
        $doctor = $this->user('doctor@gmail.com');

        $this->actingAs($doctor)
            ->get(route('visits.show', $visit->id))
            ->assertOk()
            ->assertDontSee('Buat order')
            ->assertDontSee('Catat consent');

        // Terkunci bukan cuma di tampilan: request langsung pun ditolak.
        $this->actingAs($doctor)->post(route('visits.vitals.store', $visit->id), ['pulse_bpm' => 80])->assertStatus(422);
        $this->actingAs($doctor)->post(route('visits.oral-health.store', $visit->id), ['d_count' => 1])->assertStatus(422);
        $this->actingAs($doctor)->post(route('visits.radiology.store', $visit->id), [
            'modality' => 'DX',
            'clinical_indication' => 'x',
        ])->assertStatus(422);
        $this->actingAs($doctor)->post(route('visits.consents.store', $visit->id), [
            'consent_text' => 'x',
            'granted' => 1,
            'granted_by_name' => 'x',
        ])->assertStatus(422);

        $this->assertDatabaseCount('vital_signs', 0);
        $this->assertDatabaseCount('radiology_orders', 0);
        $this->assertDatabaseCount('medical_consent_records', 0);
    }
}
