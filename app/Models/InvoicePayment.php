<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $table = 'invoice_payments';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const METHODS = ['CASH', 'TRANSFER', 'QRIS', 'DEBIT', 'CREDIT'];

    protected $fillable = [
        'id',
        'invoice_id',
        'amount',
        'method',
        'paid_at',
        'received_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class, 'payment_id', 'id');
    }
}
