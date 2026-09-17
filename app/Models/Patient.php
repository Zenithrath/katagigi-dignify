<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        // 'mr_number',
        'name',
        'code',
        'email',
        'payment_email',
        'phone',
        'birthdate',
        'birth_place',
        'nik',
        'ihs_id',
        'religion',
        'gender',
        'picture',
        'sosmed',
        'satusehat_consent',
    ];

    protected function casts(): array
    {
        return [
            'satusehat_consent' => 'boolean',
        ];
    }

    /**
     * Get the patient associated with the address.
     */
    public function address(): HasOne
    {
        return $this->hasOne(PatientAddress::class, 'id', 'patient_id');
    }

    /**
     * Get the patient associated with the appointment.
     */
    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id', 'patient_id');
    }

    /**
     * Get the patient associated with the medicalRecord.
     */
    public function medical_record(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'id', 'patient_id');
    }

    /**
     * Get the patient associated with the transaction.
     */
    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id', 'patient_id');
    }
}
