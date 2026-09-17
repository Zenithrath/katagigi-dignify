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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'patient_id',
        'patient_name',
        'doctor_id',
        'doctor_name',
        'schedule_id',
        'date',
        'time_start',
        'time_end',
        'status',
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
     * Get the appointment that owns the schedule.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }

    /**
     * Get the appointment associated with the transaction.
     */
    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id', 'appointment_id');
    }
}
