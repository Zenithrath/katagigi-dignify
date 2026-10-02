<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_IN = 'IN';

    public const TYPE_OUT = 'OUT';

    public const TYPE_ADJUST = 'ADJUST';

    protected $fillable = [
        'id',
        'inventory_item_id',
        'batch_id',
        'type',
        'quantity',
        'reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'float'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'batch_id', 'id');
    }
}
