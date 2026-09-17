<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAddress extends Model
{
    use HasFactory;
    use HasFactory;

    protected $table = 'patient_addresses';

    protected $primaryKey = 'patient_id';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'zip_code',
        'tonarigumi',
        'street',
        'village',
        'district',
        'regency',
        'province',
    ];

    /**
     * Get the address that owns the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}
