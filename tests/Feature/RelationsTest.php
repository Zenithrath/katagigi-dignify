<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * D-05: relasi Eloquent berkunci benar (dulu key tertukar sehingga
 * relasi dari sisi Patient/Doctor mengembalikan data salah/kosong)
 * dan fillable selaras migrasi (dulu atribut valid diam-diam dibuang).
 */
class RelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_patient_relations_resolve_to_owned_rows(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
        ]);
        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'appointment_id' => $appointment->id,
        ]);
        Transaction::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->user_id,
            'appointment_id' => $appointment->id,
        ]);

        $fresh = Patient::find($patient->id);
        $this->assertTrue($fresh->appointment()->whereKey($appointment->id)->exists());
        $this->assertEquals(1, $fresh->appointment()->count());
        $this->assertEquals(1, $fresh->medical_record()->count());
        $this->assertEquals(1, $fresh->transaction()->count());

        // Foreign key tidak tertukar: relasi milik pasien A tak menarik milik B.
        $other = Patient::factory()->create();
        $this->assertEquals(0, $other->appointment()->count());
        $this->assertEquals(0, $other->medical_record()->count());
        $this->assertEquals(0, $other->transaction()->count());
    }

    public function test_medical_record_belongs_to_patient_doctor_appointment(): void
    {
        $record = MedicalRecord::factory()->create();

        $this->assertNotNull($record->patient);
        $this->assertNotNull($record->doctor);
        $this->assertNotNull($record->appointment);
        $this->assertEquals($record->patient_id, $record->patient->id);
        $this->assertEquals($record->doctor_id, $record->doctor->user_id);
        $this->assertEquals($record->appointment_id, $record->appointment->id);
    }

    public function test_doctor_relations_use_user_id_owner_key(): void
    {
        $doctor = Doctor::factory()->create();
        $schedule = \App\Models\Schedule::factory()->create(['doctor_id' => $doctor->user_id]);
        $appointment = Appointment::factory()->create(['doctor_id' => $doctor->user_id]);

        $fresh = Doctor::find($doctor->user_id);
        $this->assertTrue($fresh->schedule()->whereKey($schedule->id)->exists());
        $this->assertTrue($fresh->appointment()->whereKey($appointment->id)->exists());
    }

    public function test_fillable_matches_schema_columns(): void
    {
        $recordFillable = (new MedicalRecord)->getFillable();
        foreach (['patient_code', 'patient_address', 'doctor_nipp', 'appointment_date', 'anamnesis', 'diagnosis', 'discount', 'billing', 'blood_pressure', 'cooperativity', 'checkup_result'] as $column) {
            $this->assertContains($column, $recordFillable, "MedicalRecord fillable kehilangan kolom [$column]");
        }

        $appointmentFillable = (new Appointment)->getFillable();
        foreach (['patient_code', 'doctor_nipp', 'services', 'confirmed_at', 'canceled_at'] as $column) {
            $this->assertContains($column, $appointmentFillable, "Appointment fillable kehilangan kolom [$column]");
        }
        $this->assertNotContains('schedule_id', $appointmentFillable);
    }
}
