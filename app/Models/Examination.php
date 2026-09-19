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
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }
}
