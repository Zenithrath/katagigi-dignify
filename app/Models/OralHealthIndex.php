<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OralHealthIndex extends Model
{
    use HasFactory;

    protected $table = 'oral_health_indices';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'patient_id',
        'ohis_debris',
        'ohis_calculus',
        'ohis_total',
        'd_count',
        'm_count',
        'f_count',
        'dmt_index',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ohis_debris' => 'float',
            'ohis_calculus' => 'float',
            'ohis_total' => 'float',
            'd_count' => 'integer',
            'm_count' => 'integer',
            'f_count' => 'integer',
            'dmt_index' => 'float',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}
