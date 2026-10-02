<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_TREATMENT = 'TREATMENT';

    public const TYPE_MEDICINE = 'MEDICINE';

    public const TYPE_OTHER = 'OTHER';

    protected $fillable = [
        'id',
        'invoice_id',
        'item_type',
        'description',
        'tooth_fdi',
        'reference_code',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'float',
            'amount' => 'float',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
}
