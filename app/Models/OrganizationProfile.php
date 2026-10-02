<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Profil SATUSEHAT per cabang: Organization / Location / Practitioner IHS
 * yang dipakai Encounter.identifier dan serviceProvider.
 */
class OrganizationProfile extends Model
{
    use HasFactory;

    protected $table = 'organization_profiles';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'organization_ihs',
        'organization_name',
        'nakes_facility_code',
        'location_ihs',
        'location_name',
        'region_code',
        'address',
        'phone',
        'email',
        'practitioner_ihs',
        'active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
