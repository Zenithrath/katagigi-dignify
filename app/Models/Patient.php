<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'name', 'nik', 'ihs_id', 'birth_place',
        'phone', 'email', 'birthdate', 'gender', 'religion',
        'satusehat_consent',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'satusehat_consent' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
