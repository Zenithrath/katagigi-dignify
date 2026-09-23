<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 9 Fase 2: workspace + kalender + prefill nota dari visit.
 */
class WorkspaceCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_workspace_shows_queue_and_current_visit(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_IN_TREATMENT,
        ]);

        $this->actingAs($nurse)->get(route('workspace.index'))
            ->assertOk()
            ->assertSee($visit->visit_number, false)
            ->assertSee($patient->name, false)
            ->assertSee('PASIEN SAAT INI', false);
    }

    public function test_doctor_workspace_scoped_to_own_visits(): void
    {
        $doctor = Doctor::factory()->create();
        $doctorUser = $doctor->user;
        $doctorUser->assignRole('doctor');
        $other = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $mine = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_WAITING,
        ]);
        $theirs = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $other->user_id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_WAITING,
        ]);

        $this->actingAs($doctorUser)->get(route('workspace.index'))
            ->assertOk()
            ->assertSee($mine->visit_number, false)
            ->assertDontSee($theirs->visit_number, false);
    }

    public function test_calendar_renders_month_and_day(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $appointment = Appointment::factory()->create([
            'date' => date('Y-m-d'),
        ]);

        $this->actingAs($admin)->get(route('calendar.index', ['day' => date('Y-m-d')]))
            ->assertOk()
            ->assertSee('Kalender Appointment', false)
            ->assertSee($appointment->patient_name, false);
    }

    public function test_transaction_form_prefills_from_visit(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
        ]);
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'appointment_id' => $appointment->id,
        ]);

        $this->actingAs($admin)->get(route('transactions.create', ['visit' => $visit->id]))
            ->assertOk()
            ->assertSee($visit->visit_number, false)
            ->assertSee('Appointment terpilih otomatis', false);
    }
}
