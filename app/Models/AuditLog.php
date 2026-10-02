<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log formal (Permenkes 24/2022): siapa melakukan apa, kapan,
 * nilai lama/baru, dan alasannya. Write-only dari aplikasi; tidak diedit.
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old',
        'new',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old' => 'array',
            'new' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
