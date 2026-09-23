<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'org',
        'name',
        'address',
        'phone',
        'is_active',
        'organization_ihs',
        'location_ihs',
        'satusehat_org_id',
        'satusehat_location_id',
    ];

    protected $table = 'branches';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'branch_id', 'id');
    }

    public function satusehatCredential(): HasOne
    {
        return $this->hasOne(SatuSehatCredential::class, 'branch_id', 'id');
    }
}
