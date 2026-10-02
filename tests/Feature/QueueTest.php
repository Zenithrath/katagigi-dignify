<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientAddress;
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
        $patient = Patient::factory()->complete()->create(['satusehat_consent' => true]);
        PatientAddress::factory()->create(['patient_id' => $patient->id]);
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

    public function test_checkin_rejects_incomplete_patient_data(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $patient = Patient::factory()->incomplete()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'confirmed_at' => now(),
        ]);

        // Data kurang → balik ke detail janji + drawer terbuka (bukan halaman pasien).
        $this->actingAs($admin)
            ->post(route('appointments.checkin', $appointment->id))
            ->assertRedirect(route('appointments.show', $appointment->id));

        $this->assertEquals(0, Visit::where('appointment_id', $appointment->id)->count());

        $this->actingAs($admin)->get(route('appointments.show', $appointment->id))
            ->assertOk()
            ->assertSee('Lengkapi &amp; Check-in', false);
    }

    public function test_checkin_complete_fills_data_and_creates_visit(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $patient = Patient::factory()->incomplete()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'confirmed_at' => now(),
        ]);

        // 1 klik: lengkapi + check-in langsung mendarat di workspace.
        $response = $this->actingAs($admin)->post(route('appointments.checkin.complete', $appointment->id), [
            'nik' => '6371011705900009',
            'phone' => '081234567890',
            'birthdate' => '1995-01-01',
            'gender' => 'FEMALE',
            'street' => 'Jl. Test No. 1',
            'village' => 'Test',
            'satusehat_consent' => '1',
        ]);

        $visit = Visit::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($visit);
        $response->assertRedirect(route('workspace.index', ['highlight' => $visit->id]));

        $visit = Visit::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($visit);
        $this->assertEquals(Visit::STATUS_WAITING, $visit->clinical_status);
        $this->assertEquals('6371011705900009', DB::table('patients')->where('id', $patient->id)->value('nik'));
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
        $doctor = User::where('email', 'doctor@gmail.com')->first();
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

        // Suster boleh WAITING → CALLED; progres klinis hanya dokter.
        $this->actingAs($nurse)
            ->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_CALLED])
            ->assertRedirect();
        $this->actingAs($nurse)
            ->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_IN_TREATMENT])
            ->assertForbidden();

        foreach ([Visit::STATUS_IN_TREATMENT, Visit::STATUS_DONE] as $next) {
            $this->actingAs($doctor)
                ->post(route('visits.status', $visit->id), ['status' => $next])
                ->assertRedirect();
            $this->assertEquals($next, $visit->fresh()->clinical_status);
        }
    }

    public function test_done_auto_calls_next_doctor_patient(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $doctorId = $appointment->doctor_id;
        $date = date('Y-m-d');

        $first = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctorId,
            'appointment_id' => $appointment->id,
            'visit_date' => $date,
            'clinical_status' => Visit::STATUS_IN_TREATMENT,
        ]);
        $secondAppointment = Appointment::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctorId,
            'confirmed_at' => now(),
        ]);
        $second = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctorId,
            'appointment_id' => $secondAppointment->id,
            'visit_date' => $date,
            'clinical_status' => Visit::STATUS_CALLED,
        ]);

        $this->actingAs($doctor)
            ->post(route('visits.status', $first->id), ['status' => Visit::STATUS_DONE])
            ->assertRedirect(route('workspace.index'));

        $this->assertEquals(Visit::STATUS_DONE, $first->fresh()->clinical_status);
        $this->assertEquals(Visit::STATUS_IN_TREATMENT, $second->fresh()->clinical_status);
    }

    public function test_workspace_shows_nurse_and_doctor_tabs(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_WAITING,
            'queue_no' => 1,
        ]);

        // Halaman antrean lama redirect ke workspace gabungan.
        $this->actingAs($admin)->get(route('visits.index'))
            ->assertRedirect(route('workspace.index'));

        $this->actingAs($admin)->get(route('workspace.index'))
            ->assertOk()
            ->assertSee($visit->visit_number, false)
            ->assertSee('Antrean Suster', false)
            ->assertSee('Antrean Dokter', false)
            ->assertSee('Antrean Triase', false)
            ->assertSee('Antrean Masuk Dokter', false)
            ->assertSee('Diperiksa &amp; Selesai', false)
            ->assertSee('#1', false);

        $this->actingAs($admin)->get(route('visits.show', $visit->id))
            ->assertOk()
            ->assertSee($visit->visit_number, false);
    }

    public function test_triage_drawer_stays_on_workspace(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        // Check-in hari ini agar masuk workspace hari ini.
        Appointment::where('id', $appointment->id)->update(['date' => date('Y-m-d')]);
        $this->actingAs($admin)->post(route('appointments.checkin', $appointment->id))->assertRedirect();
        $visit = Visit::where('appointment_id', $appointment->id)->first();

        // Drawer triase tampil di workspace.
        $this->actingAs($nurse)->get(route('workspace.index'))
            ->assertOk()
            ->assertSee('Isi Triase', false);

        // Isi vital + keluhan dari workspace → tetap di workspace.
        $this->actingAs($nurse)->from(route('workspace.index'))
            ->post(route('visits.vitals.store', $visit->id), [
                'pulse_bpm' => 80,
                'temperature_c' => 36.5,
                'respiratory_rate' => 18,
            ])->assertRedirect(route('workspace.index'));
        $this->actingAs($nurse)->from(route('workspace.index'))
            ->post(route('visits.anamnesis.store', $visit->id), [
                'chief_complaint' => 'Sakit gigi bawah kanan',
            ])->assertRedirect(route('workspace.index'));

        $this->assertEquals(80, $visit->fresh()->vitalSign->pulse_bpm);
        $this->assertEquals('Sakit gigi bawah kanan', $visit->fresh()->anamnesis->chief_complaint);
    }

    public function test_done_redirects_to_workspace(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_IN_TREATMENT,
        ]);

        $this->actingAs($doctor)
            ->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_DONE])
            ->assertRedirect(route('workspace.index'));
    }

    public function test_triage_save_auto_advances_to_doctor_queue(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_WAITING,
        ]);

        // Tanpa flag: tetap di antrean suster.
        $this->actingAs($nurse)->post(route('visits.vitals.store', $visit->id), [
            'pulse_bpm' => 80,
        ])->assertRedirect();
        $this->assertEquals(Visit::STATUS_WAITING, $visit->fresh()->clinical_status);

        // Dengan flag drawer: otomatis masuk antrean dokter.
        $this->actingAs($nurse)->post(route('visits.anamnesis.store', $visit->id), [
            'chief_complaint' => 'Sakit gigi',
            'advance_to_called' => '1',
        ])->assertRedirect();
        $this->assertEquals(Visit::STATUS_CALLED, $visit->fresh()->clinical_status);
    }

    public function test_nurse_cannot_open_visit_nor_progress_clinically(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $appointment = $this->confirmedAppointment();
        $visit = Visit::factory()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_CALLED,
        ]);

        // Suster terkunci dari halaman visit (triase via drawer workspace).
        $this->actingAs($nurse)->get(route('visits.show', $visit->id))->assertForbidden();

        // Suster tidak boleh mulai periksa / menyelesaikan.
        $this->actingAs($nurse)
            ->post(route('visits.status', $visit->id), ['status' => Visit::STATUS_IN_TREATMENT])
            ->assertForbidden();
    }

    public function test_guest_cannot_access_queue(): void
    {
        $this->get(route('workspace.index'))->assertRedirect(route('login'));
    }
}
