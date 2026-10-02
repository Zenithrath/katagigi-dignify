<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatuSehatSyncLog extends Model
{
    use HasFactory;

    protected $table = 'satusehat_sync_logs';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_SUCCESS = 'SUCCESS';

    public const STATUS_FAILED = 'FAILED';

    public const STATUS_SKIPPED = 'SKIPPED';

    protected $fillable = [
        'id',
        'patient_id',
        'visit_id',
        'resource_type',
        'local_id',
        'external_id',
        'status',
        'request',
        'response',
        'error',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'request' => 'array',
            'response' => 'array',
            'attempts' => 'integer',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }
}
