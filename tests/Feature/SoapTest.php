<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 3 Fase 2: anamnesis + SOAP per visit.
 */
class SoapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_doctor_can_write_anamnesis_and_soap(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.anamnesis.store', $visit->id), [
            'chief_complaint' => 'Gigi ngilu sejak seminggu',
            'allergies' => 'Amoxicillin',
        ])->assertRedirect();
        $this->assertDatabaseHas('anamneses', [
            'visit_id' => $visit->id,
            'chief_complaint' => 'Gigi ngilu sejak seminggu',
        ]);

        $this->actingAs($doctor)->post(route('visits.examination.store', $visit->id), [
            'subjective' => 'Nyeri berdenyut',
            'objective' => 'Karies dentin 36',
            'assessment' => 'K02.1',
            'plan' => 'Restorasi',
            'blood_pressure' => '120/80',
        ])->assertRedirect();
        $this->assertDatabaseHas('examinations', [
            'visit_id' => $visit->id,
            'assessment' => 'K02.1',
        ]);

        // Update menimpa baris yang sama (satu per visit).
        $this->actingAs($doctor)->put(route('visits.anamnesis.update', $visit->id), [
            'chief_complaint' => 'Gigi ngilu + bengkak',
        ])->assertRedirect();
        $this->assertEquals(1, $visit->anamnesis()->count());
        $this->assertDatabaseHas('anamneses', [
            'visit_id' => $visit->id,
            'chief_complaint' => 'Gigi ngilu + bengkak',
        ]);
    }

    public function test_nurse_can_draft_anamnesis_but_not_soap(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.anamnesis.store', $visit->id), [
            'chief_complaint' => 'Draft asisten',
        ])->assertRedirect();
        $this->assertDatabaseHas('anamneses', ['visit_id' => $visit->id]);

        $this->actingAs($nurse)->post(route('visits.examination.store', $visit->id), [
            'assessment' => 'X',
        ])->assertForbidden();
        $this->assertDatabaseMissing('examinations', ['visit_id' => $visit->id]);
    }

    public function test_anamnesis_requires_chief_complaint(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.anamnesis.store', $visit->id), [])
            ->assertSessionHasErrors('chief_complaint');
    }
}
