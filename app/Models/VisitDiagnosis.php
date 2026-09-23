<?php

namespace App\Models;

use App\Models\Concerns\MedicalAddendable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitDiagnosis extends Model
{
    use HasFactory, MedicalAddendable, SoftDeletes;

    protected $table = 'visit_diagnoses';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'tooth_fdi',
        'diagnosis_code_id',
        'system',
        'code',
        'display',
        'is_primary',
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public function diagnosisCode(): BelongsTo
    {
        return $this->belongsTo(DiagnosisCode::class, 'diagnosis_code_id', 'id');
    }
}
