<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterInsurance extends Model
{
    use HasFactory;

    protected $table = 'master_insurances';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['id', 'name', 'type', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public const TYPES = [
        'general' => 'Umum (Tunai)',
        'government' => 'Pemerintah (BPJS)',
        'private' => 'Asuransi Swasta',
        'corporate' => 'Korporasi',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'insurance_id', 'id');
    }
}
