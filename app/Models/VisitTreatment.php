<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitTreatment extends Model
{
    use HasFactory;

    protected $table = 'visit_treatments';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'tooth_fdi',
        'procedure_code_id',
        'system',
        'code',
        'procedure',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'float',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public function procedureCode(): BelongsTo
    {
        return $this->belongsTo(DiagnosisCode::class, 'procedure_code_id', 'id');
    }

    public function subtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
