<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug laporan: detail pasien 500 untuk RM format lama + nama pasien
 * di daftar RM tak terlihat bisa diklik.
 */
class PatientDetailBugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_patient_detail_renders_with_legacy_records(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $patient = Patient::factory()->create();

        // RM format lama: JSON skalar + objek tanpa code/name.
        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'services' => json_encode('format-tidak-dikenal'),
            'image_before' => null,
            'image_after' => null,
        ]);
        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'services' => json_encode(['harga' => 100000]),
        ]);

        $this->actingAs($admin)->get(route('patients.show', $patient->id))->assertOk();
    }

    public function test_patient_show_missing_returns_404(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)->get(route('patients.show', (string) \Illuminate\Support\Str::uuid()))->assertNotFound();
    }

    public function test_medical_record_index_patient_name_is_clickable(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)->get(route('medical-records.index'))
            ->assertOk()
            ->assertSee('hover:underline', false);
    }
}
