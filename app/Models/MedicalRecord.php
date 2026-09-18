<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * D-05: selaras dengan migrasi (dulu basi: diagnose/service/date/
     * blood_tension/cooperative → atribut valid diam-diam dibuang Eloquent).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'patient_id',
        'patient_code',
        'patient_name',
        'patient_phone',
        'patient_address',
        'doctor_id',
        'doctor_name',
        'doctor_nipp',
        'doctor_niptk',
        'appointment_id',
        'appointment_date',
        'time_start',
        'time_end',
        'services',
        'anamnesis',
        'diagnosis',
        'therapy',
        'prescription',
        'checkup_result',
        'next_schedule',
        'price',
        'discount',
        'billing',
        'promat',
        'blood_pressure',
        'cooperativity',
        'image_before',
        'image_after',
    ];

    /**
     * Get the medical_record that owns the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the medical_record that owns the doctor.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }

    /**
     * Get the medical_record that owns the appointment.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }
}
