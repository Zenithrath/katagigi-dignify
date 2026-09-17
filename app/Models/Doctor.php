<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctors';

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nipp',
        'niptk',
        'profile_picture',
        'cover_picture',
    ];

    /**
     * Get the doctor that owns the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the doctor associated with the schedule.
     */
    public function schedule(): HasMany
    {
        return $this->hasMany(Schedule::class, 'id', 'doctor_id');
    }

    /**
     * Get the doctor associated with the appointment.
     */
    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class, 'user_id', 'doctor_id');
    }

    /**
     * Get the doctor associated with the medicalRecord.
     */
    public function medical_record(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'user_id', 'doctor_id');
    }

    /**
     * Get the doctor associated with the Transaction.
     */
    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id', 'doctor_id');
    }
}
