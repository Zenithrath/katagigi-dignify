<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPlanItem extends Model
{
    use HasFactory;

    protected $table = 'treatment_plan_items';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'treatment_plan_id',
        'tooth_fdi',
        'description',
        'estimated_price',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_price' => 'float',
            'priority' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id', 'id');
    }
}
