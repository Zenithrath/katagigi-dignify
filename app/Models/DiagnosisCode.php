<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DiagnosisCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'system', 'code', 'display_id', 'display_en',
        'keywords', 'category', 'source', 'version', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query, ?string $system)
    {
        return $system ? $query->where('system', strtoupper($system)) : $query;
    }

    public function scopeSearch($query, string $keyword)
    {
        $like = '%'.strtolower($keyword).'%';
        // json perlu di-cast ke text di pgsql; di sqlite/mysql cukup LOWER langsung
        $keywordsExpr = DB::getDriverName() === 'pgsql'
            ? 'LOWER(keywords::text)'
            : 'LOWER(keywords)';

        return $query->where(function ($q) use ($like, $keywordsExpr) {
            $q->whereRaw('LOWER(code) LIKE ?', [$like])
                ->orWhereRaw('LOWER(display_id) LIKE ?', [$like])
                ->orWhereRaw('LOWER(display_en) LIKE ?', [$like])
                ->orWhereRaw($keywordsExpr.' LIKE ?', [$like]);
        });
    }
}
