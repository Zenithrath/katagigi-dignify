<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\Clinical\VisitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 1 Fase 2: fondasi branches + visits + permission visit.
 */
class VisitFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_default_branch_seeded(): void
    {
        $branch = DB::table('branches')->where('code', 'CBG-01')->first();
        $this->assertNotNull($branch);
        $this->assertTrue((bool) $branch->is_active);
    }

    public function test_visit_service_creates_numbered_visit(): void
    {
        $service = new VisitService;
        $appointment = Appointment::factory()->create();

        $first = $service->createVisit([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
        ]);
        $this->assertInstanceOf(Visit::class, $first);
        $this->assertMatchesRegularExpression('/^VST-\d{7}$/', $first->visit_number);
        $this->assertEquals(Visit::STATUS_REGISTERED, $first->clinical_status);
        $this->assertEquals(Visit::BILLING_UNBILLED, $first->billing_status);
        $this->assertNotNull($first->branch_id);

        $second = $service->createVisit([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
        ]);
        $this->assertNotEquals($first->visit_number, $second->visit_number);

        $fresh = Visit::find($first->id);
        $this->assertEquals($appointment->patient_id, $fresh->patient->id);
        $this->assertEquals($appointment->doctor_id, $fresh->doctor->user_id);
        $this->assertNotNull($fresh->branch);
    }

    public function test_visit_requires_appointment(): void
    {
        $service = new VisitService;
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $result = $service->createVisit([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
        ]);

        $this->assertInstanceOf(\Exception::class, $result);
    }

    public function test_queue_numbers_are_per_doctor_per_day(): void
    {
        $service = new VisitService;
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $mkAppointment = fn ($doctor) => Appointment::factory()->create([
            'doctor_id' => $doctor->user_id,
        ]);

        $a1 = $service->createVisit([
            'patient_id' => $mkAppointment($doctorA)->patient_id,
            'doctor_id' => $doctorA->user_id,
            'appointment_id' => $mkAppointment($doctorA)->id,
            'visit_date' => date('Y-m-d'),
        ]);
        // Samakan appointment agar FK konsisten (ID dibuat terpisah di atas).
        $a2 = $service->createVisit([
            'patient_id' => $a1->patient_id,
            'doctor_id' => $doctorA->user_id,
            'appointment_id' => $a1->appointment_id,
            'visit_date' => date('Y-m-d'),
        ]);
        $b1 = $service->createVisit([
            'patient_id' => $mkAppointment($doctorB)->patient_id,
            'doctor_id' => $doctorB->user_id,
            'appointment_id' => $mkAppointment($doctorB)->id,
            'visit_date' => date('Y-m-d'),
        ]);

        $this->assertInstanceOf(Visit::class, $a1);
        $this->assertEquals(1, $a1->queue_no);
        $this->assertEquals(2, $a2->queue_no);
        $this->assertEquals(1, $b1->queue_no);
        $this->assertEquals('#1', $a1->queueLabel());
    }

    public function test_visit_linked_to_appointment(): void
    {
        $appointment = Appointment::factory()->create();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
        ]);

        $this->assertEquals($appointment->id, Visit::find($visit->id)->appointment->id);
    }

    public function test_visit_permissions_per_role(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();

        foreach ([$manajemen, $admin, $doctor, $nurse] as $user) {
            $this->assertTrue($user->can('read visit'), $user->email);
            $this->assertTrue($user->can('create visit'), $user->email);
            $this->assertTrue($user->can('update visit'), $user->email);
        }

        $this->assertTrue($manajemen->can('sign visit'));
        $this->assertTrue($doctor->can('sign visit'));
        $this->assertFalse($admin->can('sign visit'));
        $this->assertFalse($nurse->can('sign visit'));
    }
}
