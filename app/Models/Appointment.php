<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * D-05: selaras dengan migrasi (tanpa schedule_id/status hantu).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'patient_id',
        'patient_code',
        'patient_name',
        'patient_phone',
        'doctor_id',
        'doctor_name',
        'doctor_nipp',
        'doctor_niptk',
        'date',
        'services',
        'time_start',
        'time_end',
        'confirmed_at',
        'paid_at',
        'recorded_at',
        'canceled_at',
    ];

    /**
     * Get the appointment that owns the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the appointment that owns the doctor.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }

    /**
     * Get the appointment associated with the transaction.
     */
    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class, 'appointment_id', 'id');
    }
}
