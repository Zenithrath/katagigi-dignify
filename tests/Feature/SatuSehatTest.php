<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\OralHealthIndex;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\SatuSehatSyncLog;
use App\Models\User;
use App\Models\Visit;
use App\Models\VitalSign;
use App\Services\SatuSehat\SatuSehatPayload;
use App\Services\SatuSehat\SatuSehatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
        $loincs = array_map(fn (array $code) => $code['coding'][0]['code'], array_column($obs, 'code'));
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
            'id' => (string) Str::uuid(),
            'diagnosis_code_id' => $icd10->id,
            'system' => 'ICD10',
            'code' => 'K02.1',
            'display' => 'Karies',
            'is_primary' => true,
        ]);
        $visit->treatments()->create([
            'id' => (string) Str::uuid(),
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

        VitalSign::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'pulse_bpm' => 80,
            'temperature_c' => 36.5,
            'respiratory_rate' => 18,
            'pregnancy_status' => 'NOT_PREGNANT',
        ]);

        $rx = Prescription::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'prescribed_at' => now()->toDateString(),
        ]);
        $rx->items()->create([
            'id' => (string) Str::uuid(),
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

    public function test_sync_sends_odontogram_and_ohis_observations(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();

        $visit->odontogramFindings()->create([
            'id' => (string) Str::uuid(),
            'fdi' => '36',
            'surface' => 'occlusal',
            'condition' => 'caries',
        ]);
        OralHealthIndex::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'ohis_debris' => 1.5,
            'ohis_calculus' => 1.0,
            'ohis_total' => 2.5,
            'd_count' => 1,
            'm_count' => 0,
            'f_count' => 0,
            'dmt_index' => 1.0,
        ]);

        $summary = (new SatuSehatService)->syncVisit($visit->fresh());
        $this->assertSame(0, $summary['failed']);

        // Odontogram: satu Observation per gigi dengan bodySite FDI 36.
        Http::assertSent(fn ($req) => str_contains(json_encode($req->data()), 'OC000061')
            && str_contains(json_encode($req->data()), '866006002'));
        // OHI-S: total debris, kalkulus, dan skor total OHIS terkirim terpisah.
        foreach (['OC000056', 'OC000057', 'OC000058', '251319000'] as $code) {
            Http::assertSent(fn ($req) => str_contains(json_encode($req->data()), $code));
        }
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

    public function test_resync_is_idempotent(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();
        $service = new SatuSehatService;

        $first = $service->syncVisit($visit);
        $this->assertSame(5, $first['success']);

        // Sinkron ulang tanpa perubahan data → tidak ada HTTP sama sekali.
        $this->enableFake();
        $second = $service->syncVisit($visit->fresh());

        $sent = Http::recorded()->map(fn ($pair) => $pair[0]->method().' '.$pair[0]->url())->all();
        $this->assertTrue(Http::recorded()->isEmpty(), 'Sinkron ulang tanpa perubahan seharusnya tidak mengirim request apa pun. Terkirim: '.json_encode($sent));
        $this->assertSame($first, $second);

        // Satu resource SSP per data lokal — tidak pernah ganda.
        $this->assertSame(4, $visit->satusehatLogs()->whereNotNull('external_id')->distinct()->count('external_id'));
    }

    public function test_changed_payload_updates_instead_of_duplicating(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();
        $service = new SatuSehatService;
        $service->syncVisit($visit);

        $visit->patient->update(['name' => 'Nama Diperbarui']);
        $this->enableFake();
        $service->syncVisit($visit->fresh());

        // Data berubah → PUT ke resource yang sama, bukan POST baru.
        Http::assertSent(fn ($req) => $req->method() === 'PUT' && str_contains($req->url(), '/Patient/P-1'));
        Http::assertNotSent(fn ($req) => $req->method() === 'POST' && str_ends_with($req->url(), '/Patient'));
        Http::assertNotSent(fn ($req) => $req->method() === 'POST' && str_contains($req->url(), '/Condition'));
    }

    public function test_retry_resends_only_failed_resources(): void
    {
        config(['satusehat.enabled' => true, 'satusehat.client_id' => 'test', 'satusehat.client_secret' => 'test', 'satusehat.org_id' => 'ORG-1']);

        // Stub dicatat manual: Http::fake() menambah stub, tidak menggantinya,
        // sehingga hasil ulang sink harus dibedakan lewat penanda, bukan re-fake.
        $failing = true;
        $calls = [];
        // Closure biasa (bukan arrow fn) agar `use (&$calls)` benar-benar terikat
        // ke variabel test — arrow fn menyalin nilainya dan rekaman jadi hilang.
        $ok = function (string $id, int $status) use (&$calls) {
            return function ($request) use ($id, $status, &$calls) {
                $calls[] = $request->method().' '.basename($request->url());

                return Http::response(['resourceType' => 'X', 'id' => $id], $status);
            };
        };
        Http::fake([
            '*/accesstoken' => Http::response(['access_token' => 'tok', 'expires_in' => 3600], 200),
            '*/fhir-r4/v1/Patient*' => $ok('P-1', 201),
            '*/fhir-r4/v1/Encounter*' => $ok('E-1', 200),
            '*/fhir-r4/v1/Procedure*' => $ok('PR-1', 201),
            '*/fhir-r4/v1/Condition*' => function ($request) use (&$failing, &$calls) {
                $calls[] = $request->method().' Condition';

                return $failing
                    ? Http::response(['resourceType' => 'OperationOutcome'], 400)
                    : Http::response(['resourceType' => 'Condition', 'id' => 'C-1'], 201);
            },
        ]);

        $visit = $this->signedVisit();
        (new SatuSehatService)->syncVisit($visit);
        $this->assertSame(1, $visit->satusehatLogs()->where('status', SatuSehatSyncLog::STATUS_FAILED)->count());
        $this->assertContains('POST Condition', $calls);

        // Server pulih → ulangi lewat tombol "Ulang yang gagal".
        $failing = false;
        $calls = [];

        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $this->actingAs($manajemen)->post(route('visits.satusehat.retry', $visit->id))
            ->assertRedirect()
            ->assertSessionHas('success', fn ($message) => str_contains($message, '1 sebelumnya gagal'));

        // Baris log dipakai ulang, jadi status visit ini benar-benar bersih dari kegagalan.
        $this->assertSame(0, $visit->satusehatLogs()->where('status', SatuSehatSyncLog::STATUS_FAILED)->count());
        $this->assertSame('C-1', $visit->satusehatLogs()->where('resource_type', 'Condition')->first()->external_id);
        $this->assertSame(2, $visit->satusehatLogs()->where('resource_type', 'Condition')->first()->attempts);
        // Hanya Condition yang dikirim ulang, lalu daftar diagnosis diperbarui.
        $this->assertSame(['POST Condition', 'PUT E-1'], $calls);
    }

    public function test_retry_reports_when_there_is_nothing_failed(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();
        (new SatuSehatService)->syncVisit($visit);

        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($manajemen)->post(route('visits.satusehat.retry', $visit->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Tidak ada sinkronisasi yang gagal pada visit ini.');

        // Ulang sinkron tetap hak manajemen saja.
        $this->actingAs($admin)->post(route('visits.satusehat.retry', $visit->id))->assertForbidden();
    }
}
