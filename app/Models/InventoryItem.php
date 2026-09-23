<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_items';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'unit',
        'min_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_stock' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class, 'inventory_item_id', 'id')->orderBy('expiry_date')->orderBy('created_at');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'inventory_item_id', 'id')->orderBy('created_at', 'desc');
    }

    public function currentStock(): float
    {
        return (float) $this->batches()->sum('quantity');
    }

    public function isLowStock(): bool
    {
        return $this->currentStock() < $this->min_stock;
    }
}
