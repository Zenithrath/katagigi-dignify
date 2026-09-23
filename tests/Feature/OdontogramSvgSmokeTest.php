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
        // Chart anatomis: path zona + akar, warna karies, notasi FDI pada tooltip,
        // legenda kondisi, dan kontrol lengkung.
        $response->assertSee('id="odontogramSvg"', false);
        $response->assertSee('<path', false);
        $response->assertSee('#FCA5A5', false);
        $response->assertSee('data-universal', false);
        $response->assertSee('Rahang Atas', false);
        $response->assertSee('Mahkota', false);
    }
}
