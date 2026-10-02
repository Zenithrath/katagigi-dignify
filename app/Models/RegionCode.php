<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionCode extends Model
{
    use HasFactory;

    protected $table = 'region_codes';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'level',
        'parent_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public const LEVEL_PROVINCE = 'province';

    public const LEVEL_CITY = 'city';

    public const LEVEL_DISTRICT = 'district';

    public const LEVEL_VILLAGE = 'village';

    public static function active(): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->where('is_active', true);
    }
}
