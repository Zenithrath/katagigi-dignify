<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OdontogramSvgSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_show_renders_svg_chart(): void
    {
        $this->seed();
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->complete()->create();
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
        ]);
        $visit->odontogramFindings()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'fdi' => '16',
            'surface' => 'occlusal',
            'condition' => 'caries',
        ]);

        $user = User::where('email', 'doctor@gmail.com')->first();
        $response = $this->actingAs($user)->get(route('visits.show', $visit->id));
        $response->assertOk();
        // SVG chart: polygon zona + warna karies + legenda + info permukaan terpilih.
        $response->assertSee('<polygon', false);
        $response->assertSee('#FCA5A5', false);
        $response->assertSee('surface', false);
        $response->assertSee('Mahkota', false);
    }
}
