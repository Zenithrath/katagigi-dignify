<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kredensial SATUSEHAT per cabang (Fase 2.1): 2 cabang = 2 klien OAuth berbeda.
 * Secret disimpan terenkripsi dan tidak pernah dikirim balik ke UI (dibaca hanya
 * oleh SatuSehatService saat menegosiasikan token).
 */
class SatuSehatCredential extends Model
{
    use HasFactory;

    protected $table = 'satusehat_credentials';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'client_id',
        'client_secret',
        'organization_id',
        'location_id',
        'environment',
        'is_active',
    ];

    protected $hidden = ['client_secret'];

    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public const ENV_SANDBOX = 'sandbox';

    public const ENV_PRODUCTION = 'production';

    public const ENVIRONMENTS = [
        self::ENV_SANDBOX => 'Sandbox',
        self::ENV_PRODUCTION => 'Production',
    ];

    /** URL dasar + OAuth per lingkungan (dokumentasi SATUSEHAT Platform). */
    public const BASE_URLS = [
        self::ENV_SANDBOX => [
            'base' => 'https://api-satusehat-dev.dto.kemkes.go.id',
            'auth' => 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1',
        ],
        self::ENV_PRODUCTION => [
            'base' => 'https://api-satusehat.kemkes.go.id',
            'auth' => 'https://api-satusehat.kemkes.go.id/oauth2/v1',
        ],
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /** Kredensial aktif milik cabang, atau null. */
    public static function forBranch(?string $branchId): ?self
    {
        if (! $branchId) {
            return null;
        }

        return static::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();
    }
}
