<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 2 Fase 2: check-in appointment → antrian → maju status.
 */
class QueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function confirmedAppointment(): object
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'confirmed_at' => now(),
        ]);

        return $appointment;
    }

    public function test_checkin_creates_waiting_visit(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $appointment = $this->confirmedAppointment();

        $this->actingAs($admin)
            ->post(route('appointments.checkin', $appointment->id))
            ->assertRedirect();

        $visit = Visit::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($visit);
        $this->assertEquals(Visit::STATUS_WAITING, $visit->clinical_status);
        $this->assertEquals($appointment->patient_id, $visit->patient_id);
        $this->assertEquals($appointment->doctor_id, $visit->doctor_id);
    }

    public function test_double_checkin_redirects_to_existing_visit(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $appointment = $this->confirmedAppointment();

        $this->actingAs($admin)->post(route('appointments.checkin', $appointment->id));
        $this->actingAs($admin)->post(route('appointments.checkin', $appointment->id))
            ->assertRedirect();

        $this->assertEquals(1, Visit::where('appointment_id', $appointment->id)->count());
    }

    public function test_checkin_rejects_unconfirmed_appointment(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'confirmed_at' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('appointments.checkin', $appointment->id))
            ->assertStatus(422);
        $this->assertEquals(0, Visit::where('appointment_id', $appointment->id)->count());
    }

    public function test_status_advances_forward_only(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'clinical_status' => Visit::STATUS_WAITING,
        ]);

        // Lompat WAITING → IN_TREATMENT ditolak.
        $this->actingAs($nurse)
            ->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_IN_TREATMENT])
            ->assertStatus(422);

        foreach ([Visit::STATUS_CALLED, Visit::STATUS_IN_TREATMENT, Visit::STATUS_DONE] as $next) {
            $this->actingAs($nurse)
                ->post(route('visits.status', $visit->id), ['status' => $next])
                ->assertRedirect();
            $this->assertEquals($next, $visit->fresh()->clinical_status);
        }
    }

    public function test_queue_page_lists_todays_visits(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_WAITING,
        ]);

        $this->actingAs($admin)->get(route('visits.index'))
            ->assertOk()
            ->assertSee($visit->visit_number, false);

        $this->actingAs($admin)->get(route('visits.show', $visit->id))
            ->assertOk()
            ->assertSee($visit->visit_number, false);
    }

    public function test_guest_cannot_access_queue(): void
    {
        $this->get(route('visits.index'))->assertRedirect(route('login'));
    }
}
