<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 5 Fase 2: diagnosis ICD-10 + tindakan ICD-9 per visit.
 */
class VisitDxTxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_doctor_can_add_diagnosis_and_treatment(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();
        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();

        $this->actingAs($doctor)->post(route('visits.diagnoses.store', $visit->id), [
            'diagnosis_code_ids' => [$icd10->id],
            'tooth_fdi' => '36',
            'is_primary' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('visit_diagnoses', [
            'visit_id' => $visit->id,
            'code' => 'K02.1',
            'tooth_fdi' => '36',
        ]);

        $this->actingAs($doctor)->post(route('visits.treatments.store', $visit->id), [
            'procedure_code_ids' => [$icd9->id],
            'tooth_fdi' => '36',
            'quantity' => 2,
            'unit_price' => 150000,
        ])->assertRedirect();
        $this->assertDatabaseHas('visit_treatments', [
            'visit_id' => $visit->id,
            'code' => '23.2',
            'quantity' => 2,
        ]);

        $treatment = $visit->treatments()->first();
        $this->assertEquals(300000.0, $treatment->subtotal());
    }

    public function test_diagnosis_rejects_non_icd10_and_treatment_rejects_non_icd9(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.19')->first();
        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();

        $this->actingAs($doctor)->post(route('visits.diagnoses.store', $visit->id), [
            'diagnosis_code_ids' => [$icd9->id],
        ])->assertSessionHasErrors('diagnosis_code_ids.0');

        $this->actingAs($doctor)->post(route('visits.treatments.store', $visit->id), [
            'procedure_code_ids' => [$icd10->id],
        ])->assertSessionHasErrors('procedure_code_ids.0');

        $this->assertEquals(0, $visit->diagnoses()->count());
        $this->assertEquals(0, $visit->treatments()->count());
    }

    public function test_nurse_cannot_write_dx_tx(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();
        $icd10 = DB::table('diagnosis_codes')->where('code', 'K02.1')->first();

        $this->actingAs($nurse)->post(route('visits.diagnoses.store', $visit->id), [
            'diagnosis_code_ids' => [$icd10->id],
        ])->assertForbidden();

        $this->actingAs($nurse)->post(route('visits.treatments.store', $visit->id), [
            'procedure_code_ids' => [$icd10->id],
        ])->assertForbidden();
    }
}
