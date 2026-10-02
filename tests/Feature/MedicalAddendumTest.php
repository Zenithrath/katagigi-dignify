<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\MedicalRecordAddendum;
use App\Models\OdontogramFinding;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitDiagnosis;
use App\Models\VisitTreatment;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MedicalAddendumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function doctor(): User
    {
        return User::where('email', 'doctor@gmail.com')->firstOrFail();
    }

    public function test_odontogram_update_writes_addendum(): void
    {
        $visit = Visit::factory()->create();
        $finding = OdontogramFinding::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'fdi' => '11',
            'surface' => 'whole',
            'condition' => 'sound',
            'material' => null,
            'notes' => null,
        ]);

        $this->actingAs($this->doctor());

        $finding->update(['condition' => 'caries']);

        $this->assertDatabaseHas('medical_record_addendums', [
            'model_type' => 'OdontogramFinding',
            'model_id' => $finding->id,
            'field' => 'condition',
            'old_value' => json_encode('sound'),
            'new_value' => json_encode('caries'),
        ]);
    }

    public function test_visit_diagnosis_update_writes_addendum(): void
    {
        $visit = Visit::factory()->create();
        $icd10 = \DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $dx = VisitDiagnosis::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'tooth_fdi' => '11',
            'diagnosis_code_id' => $icd10->id,
            'system' => 'ICD10',
            'code' => 'K02.1',
            'display' => 'Karies',
            'is_primary' => true,
        ]);

        $this->actingAs($this->doctor());
        $dx->update(['is_primary' => false]);

        $this->assertDatabaseHas('medical_record_addendums', [
            'model_type' => 'VisitDiagnosis',
            'model_id' => $dx->id,
            'field' => 'is_primary',
        ]);
    }

    public function test_visit_treatment_update_writes_addendum(): void
    {
        $visit = Visit::factory()->create();
        $icd9 = \DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $tx = VisitTreatment::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'tooth_fdi' => '11',
            'procedure_code_id' => $icd9->id,
            'system' => 'ICD9',
            'code' => '23.2',
            'procedure' => 'Tambal',
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        $this->actingAs($this->doctor());
        $tx->update(['quantity' => 2]);

        $this->assertDatabaseHas('medical_record_addendums', [
            'model_type' => 'VisitTreatment',
            'model_id' => $tx->id,
            'field' => 'quantity',
        ]);
    }

    public function test_vital_sign_update_writes_addendum(): void
    {
        $visit = Visit::factory()->create();
        $vital = VitalSign::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'pulse_bpm' => 70,
            'temperature_c' => 36.5,
            'respiratory_rate' => 16,
            'pregnancy_status' => null,
        ]);

        $this->actingAs($this->doctor());
        $vital->update(['pulse_bpm' => 80]);

        $this->assertDatabaseHas('medical_record_addendums', [
            'model_type' => 'VitalSign',
            'model_id' => $vital->id,
            'field' => 'pulse_bpm',
            'old_value' => json_encode(70),
            'new_value' => json_encode(80),
        ]);
    }

    public function test_medical_record_soft_delete_blocks_hard_delete(): void
    {
        $record = MedicalRecord::factory()->create();

        $this->actingAs($this->doctor());
        $record->delete();

        // Masih ada di DB (soft delete)
        $this->assertSoftDeleted('medical_records', ['id' => $record->id]);

        // Addendum tercatat
        $this->assertDatabaseHas('medical_record_addendums', [
            'model_type' => 'MedicalRecord',
            'model_id' => $record->id,
            'field' => '__soft_deleted__',
        ]);
    }

    public function test_odontogram_soft_delete(): void
    {
        $visit = Visit::factory()->create();
        $finding = OdontogramFinding::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'fdi' => '21',
            'surface' => 'whole',
            'condition' => 'sound',
        ]);

        $this->actingAs($this->doctor());
        $finding->delete();

        $this->assertSoftDeleted('odontogram_findings', ['id' => $finding->id]);
        $this->assertDatabaseHas('medical_record_addendums', [
            'model_type' => 'OdontogramFinding',
            'model_id' => $finding->id,
            'field' => '__soft_deleted__',
        ]);
    }

    public function test_addendum_records_actor(): void
    {
        $visit = Visit::factory()->create();
        $finding = OdontogramFinding::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'fdi' => '31',
            'surface' => 'whole',
            'condition' => 'sound',
        ]);

        $doctor = $this->doctor();
        $this->actingAs($doctor);
        $finding->update(['condition' => 'crown']);

        $addendum = MedicalRecordAddendum::where('model_type', 'OdontogramFinding')
            ->where('model_id', $finding->id)
            ->where('field', 'condition')
            ->first();

        $this->assertNotNull($addendum);
        $this->assertEquals($doctor->id, $addendum->user_id);
    }
}
