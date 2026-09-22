<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\SatuSehat\SatuSehatPayload;
use App\Services\SatuSehat\SatuSehatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * F4-T2: payload FHIR + sinkronisasi sandbox (Http::fake).
 */
class SatuSehatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_payload_builders_enforce_completeness(): void
    {
        $patient = (object) ['nik' => '6371011705900001', 'name' => 'Uji', 'gender' => 'FEMALE', 'birthdate' => '1990-01-01'];
        $payload = SatuSehatPayload::patient($patient);
        $this->assertEquals('Patient', $payload['resourceType']);
        $this->assertEquals('female', $payload['gender']);
        $this->assertEquals('1990-01-01', $payload['birthDate']);

        $this->assertNull(SatuSehatPayload::patient((object) ['nik' => '123', 'name' => 'X', 'birthdate' => '1990-01-01']));
        $this->assertNull(SatuSehatPayload::patient((object) ['nik' => '6371011705900001', 'name' => 'X', 'birthdate' => 'bukan-tanggal']));

        $this->assertNull(SatuSehatPayload::condition((object) ['system' => 'ICD9', 'code' => '23.2'], 'P1', 'E1'));
        $ok = SatuSehatPayload::condition((object) ['system' => 'ICD10', 'code' => 'K02.1', 'display' => 'Karies'], 'P1', 'E1');
        $this->assertEquals('K02.1', $ok['code']['coding'][0]['code']);

        $this->assertNull(SatuSehatPayload::practitionerRef(null));
        $this->assertEquals('Practitioner/D1', SatuSehatPayload::practitionerRef('D1'));
    }

    public function test_vital_sign_and_medication_request_payloads(): void
    {
        $vitals = (object) ['pulse_bpm' => 78, 'temperature_c' => 36.8, 'respiratory_rate' => 16, 'pregnancy_status' => 'NOT_PREGNANT'];
        $obs = SatuSehatPayload::vitalSignObservations($vitals, 'P1', 'E1', 'Practitioner/D1', '2026-09-22 10:00:00');
        $this->assertCount(4, $obs);
        $loincs = array_column(array_column(array_column($obs, 'code'), 'coding'), 'code');
        $this->assertContains('8867-4', $loincs);
        $this->assertContains('8310-5', $loincs);
        $this->assertContains('9279-1', $loincs);
        $this->assertContains('82810-3', $loincs);

        $this->assertSame([], SatuSehatPayload::vitalSignObservations(null, 'P1', 'E1', null, '2026-09-22 10:00:00'));

        $prescription = (object) [
            'prescribed_at' => '2026-09-22',
            'items' => [
                (object) ['medicine_name' => 'Amoxicillin 500mg', 'kfa_code' => 'A01', 'dosage' => '500mg', 'frequency' => '3x sehari', 'duration' => '5 hari', 'quantity' => 15, 'route' => 'ORAL', 'instruction' => 'sesudah makan'],
            ],
        ];
        $med = SatuSehatPayload::medicationRequest($prescription, 'P1', 'E1', 'Practitioner/D1');
        $this->assertEquals('MedicationRequest', $med['resourceType']);
        $this->assertEquals('active', $med['status']);
        $this->assertEquals('order', $med['intent']);
        $this->assertEquals('Amoxicillin 500mg', $med['medicationCodeableConcept']['text']);
        $this->assertEquals(15, $med['dispenseRequest']['quantity']['value']);
        $this->assertEquals('Practitioner/D1', $med['requester']['reference']);

        $this->assertNull(SatuSehatPayload::medicationRequest((object) ['items' => []], 'P1', 'E1', null));
    }

    public function test_sync_disabled_by_default(): void
    {
        $this->assertFalse((new SatuSehatService)->isEnabled());
        $this->expectExceptionMessage('belum dikonfigurasi');
        (new SatuSehatService)->syncVisit(Visit::factory()->create());
    }

    private function signedVisit(): Visit
    {
        $doctor = Doctor::factory()->create();
        // Pasien eksplisit lengkap: VisitFactory mengambil pasien acak bila
        // patient_id kosong, dan pasien incomplete() seeded membuat sync skip.
        $visit = Visit::factory()->create([
            'patient_id' => Patient::factory()->complete()->create()->id,
            'doctor_id' => $doctor->user_id,
            'clinical_status' => Visit::STATUS_SIGNED,
        ]);
        // UU PDP: sinkron hanya boleh dengan persetujuan pasien.
        $visit->patient->update(['satusehat_consent' => true]);
        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $visit->diagnoses()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'diagnosis_code_id' => $icd10->id,
            'system' => 'ICD10',
            'code' => 'K02.1',
            'display' => 'Karies',
            'is_primary' => true,
        ]);
        $visit->treatments()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'procedure_code_id' => $icd9->id,
            'system' => 'ICD9',
            'code' => '23.2',
            'procedure' => 'Tambal',
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        return $visit->fresh();
    }

    private function enableFake(): void
    {
        config(['satusehat.enabled' => true, 'satusehat.client_id' => 'test', 'satusehat.client_secret' => 'test', 'satusehat.org_id' => 'ORG-1']);
        // Fake per URL resource (bukan sequence) agar urutan/kontingensi request
        // tidak membuat test rapuh saat dijalankan dalam suite penuh.
        Http::fake([
            '*/accesstoken' => Http::response(['access_token' => 'tok', 'expires_in' => 3600], 200),
            '*/fhir-r4/v1/Patient*' => Http::response(['resourceType' => 'Patient', 'id' => 'P-1'], 201),
            '*/fhir-r4/v1/Encounter*' => Http::response(['resourceType' => 'Encounter', 'id' => 'E-1'], 200),
            '*/fhir-r4/v1/Condition*' => Http::response(['resourceType' => 'Condition', 'id' => 'C-1'], 201),
            '*/fhir-r4/v1/Procedure*' => Http::response(['resourceType' => 'Procedure', 'id' => 'PR-1'], 201),
            '*/fhir-r4/v1/Observation*' => Http::response(['resourceType' => 'Observation', 'id' => 'O-1'], 201),
            '*/fhir-r4/v1/MedicationRequest*' => Http::response(['resourceType' => 'MedicationRequest', 'id' => 'MR-1'], 201),
        ]);
    }

    public function test_full_sync_chains_external_ids(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();

        $summary = (new SatuSehatService)->syncVisit($visit);
        // Patient + Encounter + Condition + Procedure + update Encounter (diagnosis).
        $this->assertEquals(['success' => 5, 'failed' => 0, 'skipped' => 0], $summary);

        $logs = $visit->satusehatLogs()->orderBy('created_at')->get();
        $this->assertEqualsCanonicalizing(
            ['Patient', 'Encounter', 'Condition', 'Procedure', 'Encounter'],
            $logs->pluck('resource_type')->all()
        );
        $byResource = $logs->whereNotNull('external_id')->keyBy('resource_type');
        $this->assertEquals(
            ['Patient' => 'P-1', 'Encounter' => 'E-1', 'Condition' => 'C-1', 'Procedure' => 'PR-1'],
            $byResource->map->external_id->all()
        );
        $this->assertTrue($logs->every(fn ($l) => $l->status === 'SUCCESS'));

        // MPI: IHS dari server tersimpan ke pasien (P-xxx dari server).
        $this->assertSame('P-1', $visit->patient->fresh()->ihs_id);
    }

    public function test_sync_sends_vitals_and_medication_request(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();

        \App\Models\VitalSign::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'visit_id' => $visit->id,
            'pulse_bpm' => 80,
            'temperature_c' => 36.5,
            'respiratory_rate' => 18,
            'pregnancy_status' => 'NOT_PREGNANT',
        ]);

        $rx = \App\Models\Prescription::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'visit_id' => $visit->id,
            'prescribed_at' => now()->toDateString(),
        ]);
        $rx->items()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'medicine_name' => 'Ibuprofen 400mg',
            'kfa_code' => 'B01',
            'quantity' => 9,
        ]);

        $summary = (new SatuSehatService)->syncVisit($visit->fresh());

        $this->assertSame(0, $summary['failed']);
        $this->assertGreaterThanOrEqual(7, $summary['success']);

        $types = $visit->satusehatLogs()->pluck('resource_type')->unique()->all();
        $this->assertContains('Observation', $types);
        $this->assertContains('MedicationRequest', $types);

        Http::assertSent(fn ($req) => str_contains($req->url(), 'MedicationRequest'));
        Http::assertSent(fn ($req) => str_contains($req->url(), 'Observation')
            && str_contains(json_encode($req->data()), '8867-4'));
    }

    public function test_sync_skips_without_patient_consent(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();
        $visit->patient->update(['satusehat_consent' => false]);

        $summary = (new SatuSehatService)->syncVisit($visit->fresh());
        $this->assertEquals(1, $summary['skipped']);
        $this->assertEquals(0, $summary['success']);
        Http::assertNothingSent();

        $log = $visit->satusehatLogs()->first();
        $this->assertStringContainsString('persetujuan', $log->error);
    }

    public function test_incomplete_patient_skips_without_http(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();
        $visit->patient->update(['nik' => null]);

        $summary = (new SatuSehatService)->syncVisit($visit->fresh());
        $this->assertEquals(1, $summary['skipped']);
        $this->assertEquals(0, $summary['success']);
        Http::assertNothingSent();
    }

    public function test_monitoring_and_sync_routes_gated(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $visit = $this->signedVisit();

        $this->actingAs($admin)->get(route('satusehat.index'))->assertForbidden();
        $this->actingAs($manajemen)->get(route('satusehat.index'))
            ->assertOk()
            ->assertSee('BELUM DIKONFIGURASI', false);

        // Tanpa kredensial → redirect error terkendali, tanpa log terkirim.
        $this->actingAs($manajemen)->post(route('visits.satusehat.sync', $visit->id))->assertRedirect();
        $this->assertEquals(0, $visit->satusehatLogs()->count());
    }
}
