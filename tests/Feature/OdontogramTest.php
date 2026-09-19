<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 4 Fase 2: odontogram FDI per visit.
 */
class OdontogramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_doctor_can_record_finding(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '36',
            'surface' => 'occlusal',
            'condition' => 'caries',
            'material' => null,
            'notes' => 'Kavitas dalam',
        ])->assertRedirect();
        $this->assertDatabaseHas('odontogram_findings', [
            'visit_id' => $visit->id,
            'fdi' => '36',
            'surface' => 'occlusal',
            'condition' => 'caries',
        ]);

        // Simpan ulang gigi+permukaan sama menimpa, bukan duplikat.
        $this->actingAs($doctor)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '36',
            'surface' => 'occlusal',
            'condition' => 'filled',
        ])->assertRedirect();
        $this->assertEquals(1, $visit->odontogramFindings()->where('fdi', '36')->count());
        $this->assertDatabaseHas('odontogram_findings', [
            'visit_id' => $visit->id,
            'fdi' => '36',
            'condition' => 'filled',
        ]);
    }

    public function test_rejects_invalid_tooth_and_condition(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '99',
            'condition' => 'caries',
        ])->assertSessionHasErrors('fdi');

        $this->actingAs($doctor)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '11',
            'condition' => 'alien',
        ])->assertSessionHasErrors('condition');

        $this->assertEquals(0, $visit->odontogramFindings()->count());
    }

    public function test_nurse_cannot_write_odontogram_but_can_read_chart(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.odontogram.store', $visit->id), [
            'fdi' => '11',
            'condition' => 'sound',
        ])->assertForbidden();

        $this->actingAs($nurse)->get(route('visits.show', $visit->id))
            ->assertOk()
            ->assertSee('Odontogram (FDI)', false);
    }

    public function test_doctor_can_delete_finding(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();
        $finding = $visit->odontogramFindings()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'fdi' => '21',
            'surface' => 'whole',
            'condition' => 'caries',
        ]);

        $this->actingAs($doctor)
            ->delete(route('visits.odontogram.destroy', [$visit->id, $finding->id]))
            ->assertRedirect();
        $this->assertDatabaseMissing('odontogram_findings', ['id' => $finding->id]);
    }
}
