<?php

namespace Tests\Feature;

use App\Models\Doctor;
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

    public function test_sync_disabled_by_default(): void
    {
        $this->assertFalse((new SatuSehatService)->isEnabled());
        $this->expectExceptionMessage('belum dikonfigurasi');
        (new SatuSehatService)->syncVisit(Visit::factory()->create());
    }

    private function signedVisit(): Visit
    {
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'clinical_status' => Visit::STATUS_SIGNED,
        ]);
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
        config(['satusehat.enabled' => true, 'satusehat.client_id' => 'test', 'satusehat.client_secret' => 'test']);
        Http::fake([
            '*/accesstoken' => Http::response(['access_token' => 'tok', 'expires_in' => 3600], 200),
            '*/fhir-r4/v1/*' => Http::sequence()
                ->push(['resourceType' => 'Patient', 'id' => 'P-1'], 201)
                ->push(['resourceType' => 'Encounter', 'id' => 'E-1'], 201)
                ->push(['resourceType' => 'Condition', 'id' => 'C-1'], 201)
                ->push(['resourceType' => 'Procedure', 'id' => 'PR-1'], 201),
        ]);
    }

    public function test_full_sync_chains_external_ids(): void
    {
        $this->enableFake();
        $visit = $this->signedVisit();

        $summary = (new SatuSehatService)->syncVisit($visit);
        $this->assertEquals(['success' => 4, 'failed' => 0, 'skipped' => 0], $summary);

        $logs = $visit->satusehatLogs()->orderBy('created_at')->get();
        $this->assertEqualsCanonicalizing(
            ['Patient', 'Encounter', 'Condition', 'Procedure'],
            $logs->pluck('resource_type')->all()
        );
        $byResource = $logs->keyBy('resource_type');
        $this->assertEquals(
            ['Patient' => 'P-1', 'Encounter' => 'E-1', 'Condition' => 'C-1', 'Procedure' => 'PR-1'],
            $byResource->map->external_id->all()
        );
        $this->assertTrue($logs->every(fn ($l) => $l->status === 'SUCCESS'));
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
