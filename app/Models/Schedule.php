<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedules';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'doctor_id',
        'day',
        'time_start',
        'time_end',
    ];

    /**
     * Get the schedule that owns the doctor.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }

    /**
     * Get the schedule associated with the appointment.
     */
    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id', 'schedule_id');
    }

    /**
     * Get the schedule associated with the MedicalRecord.
     */
    public function medical_record(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'id', 'schedule_id');
    }
}
