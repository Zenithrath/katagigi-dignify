<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 7 Fase 2: resep terstruktur per visit.
 */
class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_doctor_can_write_prescription(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.prescriptions.store', $visit->id), [
            'prescribed_at' => date('Y-m-d'),
        ])->assertRedirect();
        $prescription = $visit->prescriptions()->first();
        $this->assertNotNull($prescription);

        $this->actingAs($doctor)->post(route('visits.prescriptions.items.store', [$visit->id, $prescription->id]), [
            'medicine_name' => 'Amoxicillin',
            'kfa_code' => 'KFA001',
            'dosage' => '500 mg',
            'frequency' => '3x sehari',
            'duration' => '5 hari',
            'quantity' => 15,
            'instruction' => 'Sesudah makan',
        ])->assertRedirect();
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Amoxicillin',
            'quantity' => 15,
        ]);

        $this->actingAs($doctor)->post(route('visits.prescriptions.items.store', [$visit->id, $prescription->id]), [
            'medicine_name' => '',
        ])->assertSessionHasErrors('medicine_name');
    }

    public function test_nurse_cannot_write_prescription(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.prescriptions.store', $visit->id), [])
            ->assertForbidden();
    }
}
