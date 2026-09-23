<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 6 Fase 2: rencana perawatan per visit.
 */
class TreatmentPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_doctor_can_manage_plan_and_items(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.plans.store', $visit->id), [
            'title' => 'Rehab tahap 1',
        ])->assertRedirect();
        $this->assertDatabaseHas('treatment_plans', [
            'visit_id' => $visit->id,
            'title' => 'Rehab tahap 1',
            'status' => 'PLANNED',
        ]);
        $plan = $visit->treatmentPlans()->first();

        $this->actingAs($doctor)->post(route('visits.plans.items.store', [$visit->id, $plan->id]), [
            'tooth_fdi' => '11',
            'description' => 'Mahkota porselen',
            'estimated_price' => 2500000,
            'priority' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('treatment_plan_items', [
            'treatment_plan_id' => $plan->id,
            'description' => 'Mahkota porselen',
        ]);
        $this->assertEquals(2500000.0, $plan->fresh()->estimatedTotal());

        $this->actingAs($doctor)->post(route('visits.plans.status', [$visit->id, $plan->id]), [
            'status' => 'SCHEDULED',
        ])->assertRedirect();
        $this->assertEquals('SCHEDULED', $plan->fresh()->status);

        $this->actingAs($doctor)->post(route('visits.plans.status', [$visit->id, $plan->id]), [
            'status' => 'NGACO',
        ])->assertSessionHasErrors('status');
    }

    public function test_nurse_cannot_write_plan(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.plans.store', $visit->id), [
            'title' => 'X',
        ])->assertForbidden();
    }
}
