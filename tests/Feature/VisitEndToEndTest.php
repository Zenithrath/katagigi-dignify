<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task 10 Fase 2: rantai penuh visit → sign → nota.
 * registrasi → appointment → confirm → check-in → queue → anamnesis →
 * SOAP → odontogram → diagnosis → tindakan → plan → resep → lampiran →
 * DONE → SIGNED → nota.
 */
class VisitEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_full_visit_chain_to_signed_and_billed(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $doctor = Doctor::factory()->create();
        $doctorUser = $doctor->user;
        $doctorUser->assignRole('doctor');
        $service = DB::table('services')->where('is_active', true)->first();
        $date = date('Y-m-d', strtotime('next monday'));

        // 1. Registrasi + appointment + konfirmasi (front office).
        $this->actingAs($admin)->post(route('patients.store'), [
            'name' => 'Pasien Visit',
            'phone' => '081234560000',
            'gender' => 'FEMALE',
            'nik' => '6371011705900009',
            'birth_place' => 'Banjarmasin',
            'birthdate' => '1995-01-01',
            'satusehat_consent' => '1',
        ])->assertRedirect();
        $patient = DB::table('patients')->where('name', 'Pasien Visit')->first();

        $this->actingAs($admin)->post(route('schedules.store'), [
            'doctor_id' => $doctor->user_id,
            'day' => 'MONDAY',
            'start_time' => '08:00',
            'end_time' => '15:00',
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'service_id' => [$service->id],
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ])->assertRedirect();
        $appointment = DB::table('appointments')->where('patient_id', $patient->id)->first();
        $this->actingAs($admin)->post(route('appointments.confirm', $appointment->id))->assertRedirect();

        // 2. Check-in → WAITING → CALLED → IN_TREATMENT.
        $this->actingAs($admin)->post(route('appointments.checkin', $appointment->id))->assertRedirect();
        $visit = Visit::where('appointment_id', $appointment->id)->first();
        $this->assertEquals(Visit::STATUS_WAITING, $visit->clinical_status);
        $this->actingAs($nurse)->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_CALLED])->assertRedirect();
        $this->actingAs($nurse)->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_IN_TREATMENT])->assertRedirect();

        // 3. Isi klinis oleh dokter.
        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $this->actingAs($doctorUser)->post(route('visits.anamnesis.store', $visit->id), [
            'chief_complaint' => 'Lubang di gigi bawah kanan',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.examination.store', $visit->id), [
            'objective' => 'Kavitas 46 oklusal',
            'assessment' => 'Karies dentin',
            'plan' => 'Restorasi komposit',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '46',
            'surface' => 'occlusal',
            'condition' => 'caries',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.diagnoses.store', $visit->id), [
            'diagnosis_code_ids' => [$icd10->id],
            'tooth_fdi' => '46',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.treatments.store', $visit->id), [
            'procedure_code_ids' => [$icd9->id],
            'tooth_fdi' => '46',
            'quantity' => 1,
            'unit_price' => 350000,
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.plans.store', $visit->id), [
            'title' => 'Restorasi 46',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.prescriptions.store', $visit->id), [])->assertRedirect();
        $rx = $visit->prescriptions()->first();
        $this->actingAs($doctorUser)->post(route('visits.prescriptions.items.store', [$visit->id, $rx->id]), [
            'medicine_name' => 'Ibuprofen',
            'dosage' => '400 mg',
            'frequency' => '3x sehari',
            'quantity' => 9,
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.attachments.store', $visit->id), [
            'type' => 'INTRAORAL',
            'file' => UploadedFile::fake()->image('gigi46.jpg'),
        ])->assertRedirect();

        // 4. DONE → SIGNED oleh dokter. Consent (Permenkes 24/2022) dicatat dulu —
        // tanpa consent disetujui, sign ditolak 422.
        $this->actingAs($doctorUser)->post(route('visits.sign', $visit->id))->assertStatus(422);
        $this->actingAs($doctorUser)->post(route('visits.consents.store', $visit->id), [
            'consent_text' => \App\Models\MedicalConsentRecord::DEFAULT_TEXT,
            'granted' => '1',
            'granted_by_name' => 'Pasien Visit',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_DONE])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.sign', $visit->id))->assertRedirect();
        $visit = $visit->fresh();
        $this->assertTrue($visit->isSigned());
        $this->assertNotNull($visit->signed_at);
        $this->assertNotNull(DB::table('appointments')->where('id', $appointment->id)->first()->recorded_at);

        // 5. Terkunci: tulis klinis ditolak, nurse tak bisa sign.
        $this->actingAs($doctorUser)->put(route('visits.anamnesis.update', $visit->id), [
            'chief_complaint' => 'Ubah',
        ])->assertStatus(422);
        $this->actingAs($nurse)->post(route('visits.sign', $visit->id))->assertForbidden();

        // 6. Kasir membuat nota dari appointment visit.
        $this->actingAs($admin)->post(route('transactions.store'), [
            'appointment_id' => $appointment->id,
            'service_id' => [$service->id],
            'service_price' => [$service->lower_price],
            'service_quantity' => [1],
            'service_discount' => [0],
            'price' => $service->lower_price,
            'billing' => $service->lower_price,
            'payment_method' => 'CASH',
        ])->assertRedirect();
        $this->assertNotNull(DB::table('transactions')->where('appointment_id', $appointment->id)->first());
    }

    public function test_sign_requires_done_and_icd10(): void
    {
        $doctor = Doctor::factory()->create();
        $doctorUser = $doctor->user;
        $doctorUser->assignRole('doctor');
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'clinical_status' => Visit::STATUS_IN_TREATMENT,
        ]);

        // Belum DONE → 422.
        $this->actingAs($doctorUser)->post(route('visits.sign', $visit->id))->assertStatus(422);

        $visit->update(['clinical_status' => Visit::STATUS_DONE]);

        // DONE tapi tanpa ICD-10 → 422.
        $this->actingAs($doctorUser)->post(route('visits.sign', $visit->id))->assertStatus(422);

        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $this->actingAs($doctorUser)->post(route('visits.diagnoses.store', $visit->id), [
            'diagnosis_code_ids' => [$icd10->id],
        ])->assertRedirect();

        // DONE + ICD-10 tapi tanpa consent → masih 422 (Permenkes 24/2022).
        $this->actingAs($doctorUser)->post(route('visits.sign', $visit->id))->assertStatus(422);

        $this->actingAs($doctorUser)->post(route('visits.consents.store', $visit->id), [
            'consent_text' => \App\Models\MedicalConsentRecord::DEFAULT_TEXT,
            'granted' => '1',
            'granted_by_name' => 'Pasien Visit',
        ])->assertRedirect();
        $this->actingAs($doctorUser)->post(route('visits.sign', $visit->id))->assertRedirect();
        $this->assertTrue($visit->fresh()->isSigned());
    }
}
