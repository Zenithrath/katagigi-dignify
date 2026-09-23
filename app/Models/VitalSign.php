<?php

namespace App\Models;

use App\Models\Concerns\MedicalAddendable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSign extends Model
{
    use HasFactory, MedicalAddendable, SoftDeletes;

    protected $table = 'vital_signs';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'pulse_bpm',
        'temperature_c',
        'respiratory_rate',
        'pregnancy_status',
    ];

    protected function casts(): array
    {
        return [
            'pulse_bpm' => 'integer',
            'temperature_c' => 'float',
            'respiratory_rate' => 'integer',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }
}
