<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visits';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_REGISTERED = 'REGISTERED';

    public const STATUS_WAITING = 'WAITING';

    public const STATUS_CALLED = 'CALLED';

    public const STATUS_IN_TREATMENT = 'IN_TREATMENT';

    public const STATUS_DONE = 'DONE';

    public const STATUS_SIGNED = 'SIGNED';

    public const QUEUE_STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_CALLED,
        self::STATUS_IN_TREATMENT,
    ];

    public const BILLING_UNBILLED = 'UNBILLED';

    public const BILLING_BILLED = 'BILLED';

    protected $fillable = [
        'id',
        'visit_number',
        'branch_id',
        'patient_id',
        'appointment_id',
        'doctor_id',
        'visit_date',
        'clinical_status',
        'billing_status',
        'notes',
        'signed_at',
        'signed_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'signed_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by', 'id');
    }

    public function anamnesis(): HasOne
    {
        return $this->hasOne(Anamnesis::class, 'visit_id', 'id');
    }

    public function examination(): HasOne
    {
        return $this->hasOne(Examination::class, 'visit_id', 'id');
    }

    public function odontogramFindings(): HasMany
    {
        return $this->hasMany(OdontogramFinding::class, 'visit_id', 'id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(VisitDiagnosis::class, 'visit_id', 'id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(VisitTreatment::class, 'visit_id', 'id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'visit_id', 'id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'visit_id', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(VisitAttachment::class, 'visit_id', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'visit_id', 'id');
    }

    public function satusehatLogs(): HasMany
    {
        return $this->hasMany(SatuSehatSyncLog::class, 'visit_id', 'id');
    }

    public function isSigned(): bool
    {
        return $this->clinical_status === self::STATUS_SIGNED;
    }

    public function isQueued(): bool
    {
        return in_array($this->clinical_status, self::QUEUE_STATUSES, true);
    }
}
