<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Examination extends Model
{
    use HasFactory;

    protected $table = 'examinations';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'blood_pressure',
        'occlusion',
        'torus',
        'palatum',
        'diastema',
        'molar_relation',
        'canine_relation',
        'other_oral_findings',
    ];

    /** Klasifikasi oklusi (formulir pemeriksaan dental standar Kemenkes). */
    public const OCCLUSIONS = [
        'normal' => 'Normal',
        'deep_bite' => 'Deep bite',
        'open_bite' => 'Open bite',
        'cross_bite' => 'Cross bite',
        'edge_to_edge' => 'Edge to edge',
    ];

    public const TORUS = [
        'absent' => 'Tidak ada',
        'palatinus' => 'Torus palatinus',
        'mandibularis' => 'Torus mandibularis',
        'both' => 'Keduanya',
    ];

    public const PALATUM = [
        'normal' => 'Normal',
        'high' => 'Tinggi (deep)',
        'cleft' => 'Cleft palatum',
    ];

    public const DIASTEMA = [
        'absent' => 'Tidak ada',
        'present' => 'Ada',
        'closed' => 'Sudah ditangani',
    ];

    public const ANGLE_CLASSES = [
        'class_i' => 'Kelas I',
        'class_ii' => 'Kelas II',
        'class_iii' => 'Kelas III',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }
}
