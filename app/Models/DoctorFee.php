<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorFee extends Model
{
    use HasFactory;

    protected $table = 'doctor_fees';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_UNPAID = 'UNPAID';

    public const STATUS_PAID = 'PAID';

    protected $fillable = [
        'id',
        'doctor_id',
        'invoice_id',
        'base_amount',
        'percentage',
        'fee_amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'float',
            'percentage' => 'float',
            'fee_amount' => 'float',
            'paid_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
}
