<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'patient_id', 'patient_name', 'total',
        'status', 'is_locked', 'canceled_at', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'is_locked' => 'boolean',
            'canceled_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(TransactionCancellationRequest::class);
    }

    public function pendingCancellationRequest(): ?TransactionCancellationRequest
    {
        return $this->cancellationRequests()->where('status', 'PROPOSED')->first();
    }
}
