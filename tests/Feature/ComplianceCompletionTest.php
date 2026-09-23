<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Examination;
use App\Models\OdontogramFinding;
use App\Models\RadiologyOrder;
use App\Models\SatuSehatCredential;
use App\Models\User;
use App\Models\Visit;
use App\Services\SatuSehat\SatuSehatDental;
use App\Services\SatuSehat\SatuSehatPayload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fase 2–4 roadmap compliance: kredensial per cabang, payload dental lengkap
 * (oklusi/torus/palatum/diastema), DiagnosticReport/Media radiologi,
 * Address FHIR berkode Kemendagri, dan validator KFA.
 */
class ComplianceCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function makeBranch(): \App\Models\Branch
    {
        return \App\Models\Branch::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'code' => 'CBG-T'.random_int(100, 999),
            'org' => 'Klinik Kata Gigi',
            'name' => 'Cabang Uji',
        ]);
    }

    private function makeVisit(): Visit
    {
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'branch_id' => null,
            'clinical_status' => Visit::STATUS_DONE,
        ]);
        $visit->patient->update(['nik' => '6371011705900009', 'ihs_id' => 'P02478375538']);

        return $visit;
    }

    // ── Fase 2.1: kredensial per cabang ─────────────────────────

    public function test_branch_credential_resolves_over_global_env(): void
    {
        $branch = $this->makeBranch();
        SatuSehatCredential::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $branch->id,
            'client_id' => 'branch-client',
            'client_secret' => 'branch-secret',
            'environment' => 'sandbox',
        ]);

        $service = app(\App\Services\SatuSehat\SatuSehatService::class);
        $config = $service->resolveConfig($branch->id);

        $this->assertSame('branch-client', $config['client_id']);
        $this->assertSame('branch-secret', $config['client_secret']);

        // Cabang tanpa kredensial → fallback .env.
        $fallback = $service->resolveConfig(null);
        $this->assertArrayHasKey('client_id', $fallback);
    }

    public function test_credential_secret_never_reaches_view_payload(): void
    {
        $branch = $this->makeBranch();
        $credential = SatuSehatCredential::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $branch->id,
            'client_id' => 'cid',
            'client_secret' => 'sekret-sangat-rahasia',
            'environment' => 'sandbox',
        ]);

        $this->assertNotContains('sekret-sangat-rahasia', $credential->toArray());
        $this->assertSame('sekret-sangat-rahasia', $credential->client_secret);
    }

    // ── Fase 3.2: payload dental lengkap ────────────────────────

    public function test_oral_exam_observation_contains_dental_fields(): void
    {
        $visit = $this->makeVisit();
        $visit->examination()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'occlusion' => 'deep_bite',
            'torus' => 'palatinus',
            'palatum' => 'high',
            'diastema' => 'present',
            'molar_relation' => 'class_ii',
            'other_oral_findings' => 'Mucosa sehat',
        ]);
        $visit->load('examination');

        $payload = SatuSehatDental::oralExamObservation(
            $visit->examination, 'P02478375538', 'E1', 'Practitioner/D1', '2026-09-22'
        );

        $this->assertNotNull($payload);
        $this->assertSame('Kondisi Gigi dan Mulut Lainnya', $payload['code']['coding'][0]['display']);
        $this->assertStringContainsString('Oklusi: Deep bite', $payload['valueString']);
        $this->assertStringContainsString('Torus: Torus palatinus', $payload['valueString']);
        $this->assertStringContainsString('Palatum: Tinggi (deep)', $payload['valueString']);
        $this->assertStringContainsString('Diastema: Ada', $payload['valueString']);
        $this->assertStringContainsString('Mucosa sehat', $payload['valueString']);

        // Tanpa data dental → null (tidak ada resource dikirim).
        $empty = new Examination(['occlusion' => null]);
        $this->assertNull(SatuSehatDental::oralExamObservation(
            $empty, 'P', 'E', null, '2026-09-22'
        ));
    }

    public function test_dmt_counts_derive_from_odontogram_findings(): void
    {
        $visit = $this->makeVisit();
        foreach ([['46', 'caries'], ['36', 'filled'], ['47', 'missing'], ['45', 'crown'], ['44', 'root']] as [$fdi, $condition]) {
            $visit->odontogramFindings()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'fdi' => $fdi,
                'surface' => 'whole',
                'condition' => $condition,
            ]);
        }

        $findings = $visit->odontogramFindings()->select('fdi', 'condition')->get()->groupBy('fdi');
        $d = $findings->filter(fn ($f) => $f->pluck('condition')->intersect(['caries', 'root', 'fracture'])->isNotEmpty())->count();
        $m = $findings->filter(fn ($f) => $f->pluck('condition')->contains('missing'))->count();
        $f = $findings->filter(fn ($f) => $f->pluck('condition')->intersect(['filled', 'crown', 'implant', 'denture'])->isNotEmpty())->count();

        $this->assertSame(2, $d); // 46 karies, 44 sisa akar
        $this->assertSame(1, $m); // 47 hilang
        $this->assertSame(2, $f); // 36 tambalan, 45 mahkota
    }

    // ── Fase 3.3: DiagnosticReport + Media ──────────────────────

    public function test_radiology_report_payload_built_from_order(): void
    {
        $visit = $this->makeVisit();
        $order = RadiologyOrder::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'ordered_by' => $visit->doctor_id,
            'modality' => 'DX',
            'body_site' => 'Periapikal 46',
            'clinical_indication' => 'Karies dalam',
            'priority' => 'routine',
            'status' => RadiologyOrder::STATUS_COMPLETED,
            'result_text' => 'Terlihat kavitas oklusal menembus dentin.',
        ]);

        $payload = SatuSehatDental::diagnosticReport($order, 'P02478375538', 'E1', 'Practitioner/D1');

        $this->assertNotNull($payload);
        $this->assertSame('DiagnosticReport', $payload['resourceType']);
        $this->assertSame('RAD', $payload['category'][0]['coding'][0]['code']);
        $this->assertSame('Terlihat kavitas oklusal menembus dentin.', $payload['conclusion']);

        // Media payload dari berkas hasil.
        $media = SatuSehatDental::mediaPayload($order, 'P02478375538', 'E1', 'binary', 'image/png');
        $this->assertSame('Media', $media['resourceType']);
        $this->assertSame('image/png', $media['content']['contentType']);
        $this->assertSame('DX', $media['modality'][0]['code']);

        // Tanpa hasil baca → tidak ada report.
        $order->update(['result_text' => null]);
        $this->assertNull(SatuSehatDental::diagnosticReport($order, 'P', 'E', null));
    }

    // ── Fase 4.1: Address FHIR berkode Kemendagri ───────────────

    public function test_patient_payload_includes_address_with_region_code(): void
    {
        $patient = \App\Models\Patient::factory()->complete()->create();
        $patient->address()->create([
            'patient_id' => $patient->id,
            'street' => 'Jl. Merdeka No. 1',
            'district' => 'Banjarmasin Utara',
            'regency' => 'Kota Banjarmasin',
            'province' => 'Kalimantan Selatan',
            'zip_code' => '70123',
            'region_code' => '6371',
        ]);
        $patient->load('address');

        $payload = SatuSehatPayload::patient($patient);

        // Mandatory per docs SSP.
        $this->assertFalse($payload['multipleBirthBoolean']);
        $this->assertSame('id-ID', $payload['communication'][0]['language']['coding'][0]['code']);

        $this->assertArrayHasKey('address', $payload);
        $address = $payload['address'][0];
        $this->assertSame('Kota Banjarmasin', $address['city']);
        $this->assertSame('Kalimantan Selatan', $address['state']);

        // administrativeCode = extension nested per level wilayah (docs SSP).
        $codeExt = collect($address['extension'] ?? [])
            ->firstWhere('url', 'https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode');
        $this->assertNotNull($codeExt);
        $nested = collect($codeExt['extension'])->pluck('valueCode', 'url')->all();
        $this->assertSame('63', $nested['province']);
        $this->assertSame('6371', $nested['city']);
        $this->assertArrayNotHasKey('village', $nested);
    }

    public function test_encounter_payload_has_status_and_class_history(): void
    {
        $visit = $this->makeVisit();
        $payload = SatuSehatPayload::encounter($visit, 'P02478375538', null, 'ORG-1');

        $this->assertSame('finished', $payload['status']);
        $this->assertCount(3, $payload['statusHistory']);
        $this->assertSame(['arrived', 'in-progress', 'finished'], collect($payload['statusHistory'])->pluck('status')->all());
        $this->assertNotEmpty($payload['classHistory']);
        $this->assertSame('AMB', $payload['classHistory'][0]['class']['code']);
        // Identifier memakai org IHS dari parameter (kredensial cabang).
        $this->assertStringContainsString('encounter/ORG-1', $payload['identifier'][0]['system']);
        $this->assertSame('84687003', $payload['serviceType']['coding'][0]['code']);
    }

    public function test_service_request_and_allergy_payloads(): void
    {
        $visit = $this->makeVisit();
        $order = RadiologyOrder::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'ordered_by' => $visit->doctor_id,
            'modality' => 'DX',
            'body_site' => '46',
            'clinical_indication' => 'Karies dalam',
            'priority' => 'urgent',
            'status' => RadiologyOrder::STATUS_COMPLETED,
        ]);

        $sr = SatuSehatDental::serviceRequest($order, 'P1', 'E1', 'Practitioner/D1');
        $this->assertSame('ServiceRequest', $sr['resourceType']);
        $this->assertSame('completed', $sr['status']);
        $this->assertSame('urgent', $sr['priority']);
        $this->assertSame('RAD-'.$order->id, $sr['identifier'][0]['value']);

        // Alergi tercatat → AllergyIntolerance; NKDA/tidak ada → null.
        $anamnesis = (object) ['allergies' => 'Penisilin', 'created_at' => now()];
        $allergy = SatuSehatDental::allergyIntolerance($anamnesis, 'P1');
        $this->assertSame('AllergyIntolerance', $allergy['resourceType']);
        $this->assertSame('Penisilin', $allergy['code']['text']);

        $nkda = (object) ['allergies' => 'Tidak ada', 'created_at' => now()];
        $this->assertNull(SatuSehatDental::allergyIntolerance($nkda, 'P1'));
    }    // ── Fase 4.2: validator KFA ─────────────────────────────

    public function test_patient_payload_maps_marital_status(): void
    {
        $patient = \App\Models\Patient::factory()->complete()->create(['marital_status' => 'M']);
        $payload = SatuSehatPayload::patient($patient);

        $this->assertSame('v3-MaritalStatus', substr($payload['maritalStatus']['coding'][0]['system'], -16));
        $this->assertSame('M', $payload['maritalStatus']['coding'][0]['code']);

        // Tanpa marital status → elemen tidak dikirim (opsional).
        $single = \App\Models\Patient::factory()->complete()->create(['marital_status' => null]);
        $this->assertArrayNotHasKey('maritalStatus', SatuSehatPayload::patient($single));
    }

    public function test_pregnancy_status_uses_kemenkes_codes(): void
    {
        $vitals = (object) [
            'pulse_bpm' => null, 'temperature_c' => null, 'respiratory_rate' => null,
            'pregnancy_status' => 'PREGNANT',
        ];
        $payloads = SatuSehatPayload::vitalSignObservations($vitals, 'P1', 'E1', null, '2026-09-22 10:00:00');

        $this->assertCount(1, $payloads);
        $this->assertSame('82810-3', $payloads[0]['code']['coding'][0]['code']);
        $this->assertSame('OI000011', $payloads[0]['valueCodeableConcept']['coding'][0]['code']);
        $this->assertSame(
            'http://terminology.kemkes.go.id/CodeSystem/oi',
            $payloads[0]['valueCodeableConcept']['coding'][0]['system']
        );
    }

    public function test_insurance_seeded_and_assignable_to_patient(): void
    {
        $this->seed(\Database\Seeders\MasterInsuranceSeeder::class);

        $bpjs = \App\Models\MasterInsurance::where('name', 'BPJS Kesehatan')->first();
        $this->assertNotNull($bpjs);

        $patient = \App\Models\Patient::factory()->complete()->create([
            'insurance_id' => $bpjs->id,
            'insurance_number' => '0001234567890',
        ]);

        $this->assertTrue($patient->insurance->is($bpjs));
        $this->assertSame('0001234567890', $patient->insurance_number);
    }

    // ── Fase 4.2: validator KFA ─────────────────────────────

    public function test_prescription_item_rejects_unknown_kfa_code(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        Doctor::factory()->create(['user_id' => $doctor->id]);
        $visit = Visit::factory()->create(['doctor_id' => $doctor->id]);
        $visit->patient->update(['satusehat_consent' => true]);

        $this->actingAs($doctor)->post(route('visits.prescriptions.store', $visit->id), [
            'prescribed_at' => date('Y-m-d'),
        ])->assertRedirect();
        $prescription = $visit->prescriptions()->first();

        // Kode KFA tidak ada di master → error validasi.
        $this->actingAs($doctor)->post(route('visits.prescriptions.items.store', [$visit->id, $prescription->id]), [
            'medicine_name' => 'Obat Misterius',
            'kfa_code' => 'BUKAN-KFA',
        ])->assertSessionHasErrors('kfa_code');

        // Kode dari seeder → diterima.
        $this->actingAs($doctor)->post(route('visits.prescriptions.items.store', [$visit->id, $prescription->id]), [
            'medicine_name' => 'Amoxicillin',
            'kfa_code' => 'J01CA04',
        ])->assertRedirect();
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'kfa_code' => 'J01CA04',
        ]);
    }
}
