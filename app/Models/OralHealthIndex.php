<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OralHealthIndex extends Model
{
    use HasFactory;

    protected $table = 'oral_health_indices';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'patient_id',
        'ohis_debris',
        'ohis_calculus',
        'ohis_total',
        'd_count',
        'm_count',
        'f_count',
        'dmt_index',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ohis_debris' => 'float',
            'ohis_calculus' => 'float',
            'ohis_total' => 'float',
            'd_count' => 'integer',
            'm_count' => 'integer',
            'f_count' => 'integer',
            'dmt_index' => 'float',
        ];
    }

    /**
     * Interpretasi skor OHI-S mengikuti kategori Kemenkes OI000029–OI000031.
     * Satu-satunya sumber ambang batas: dipakai UI klinis dan payload SATUSEHAT.
     *
     * @return array{0: string, 1: string} [kode, label]
     */
    public static function interpretationFor(float $total): array
    {
        return match (true) {
            $total <= 1.2 => ['OI000029', 'Kondisi Gigi Baik'],
            $total <= 3.0 => ['OI000030', 'Kondisi Gigi Cukup Baik'],
            default => ['OI000031', 'Kondisi Gigi Buruk'],
        };
    }

    /** @return array{0: string, 1: string}|null */
    public function interpretation(): ?array
    {
        return $this->ohis_total === null ? null : self::interpretationFor((float) $this->ohis_total);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }
}
