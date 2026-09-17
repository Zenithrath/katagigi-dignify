<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

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
        'sequence',
        'down_payment_transaction_id',
        'has_down_payment',
        'is_endorsed',
        'has_installment',
        'current_payment',
        'patient_id',
        'patient_name',
        'patient_phone',
        'patient_code',
        'doctor_id',
        'doctor_nipp',
        'doctor_name',
        'nurse_id',
        'nurse_nipp',
        'nurse_name',
        'appointment_id',
        'appointment_datetime',
        'next_schedule',
        'services',
        'price',
        'discount',
        'billing',
        'payment_method',
        'canceled_at',
        'cancel_reason',
        'voucher_code',
        'referenced_installment_id',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'current_payment' => 'decimal:2',
            'has_down_payment' => 'boolean',
            'is_endorsed' => 'boolean',
            'has_installment' => 'boolean',
            'is_locked' => 'boolean',
            'canceled_at' => 'datetime',
        ];
    }

    /**
     * Get the transaction that owns the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the transaction that owns the doctor.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }

    /**
     * Get the transaction that owns the appointment.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    /**
     * Get the transaction associated with the transaction_service.
     */
    public function transaction_service(): HasMany
    {
        return $this->hasMany(TransactionService::class, 'id', 'transaction_id');
    }

    /**
     * V2: usulan pembatalan nota (alur usul-kunci-approve).
     */
    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(TransactionCancellationRequest::class, 'transaction_id', 'id');
    }

    public function pendingCancellationRequest(): ?TransactionCancellationRequest
    {
        return $this->cancellationRequests()->where('status', 'PROPOSED')->first();
    }
}
