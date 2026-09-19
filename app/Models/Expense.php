<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const CATEGORIES = [
        'Operasional',
        'Gaji',
        'Sewa',
        'Utilitas',
        'Bahan',
        'Marketing',
        'Pajak',
        'Lainnya',
    ];

    protected $fillable = [
        'id',
        'branch_id',
        'category',
        'amount',
        'spent_at',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'spent_at' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
