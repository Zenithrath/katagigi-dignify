<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramFinding extends Model
{
    use HasFactory;

    protected $table = 'odontogram_findings';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const CONDITIONS = [
        'sound' => 'Sehat',
        'caries' => 'Karies',
        'filled' => 'Tambalan',
        'crown' => 'Mahkota',
        'missing' => 'Hilang',
        'implant' => 'Implan',
        'denture' => 'Gigi tiruan',
        'root' => 'Sisa akar',
        'mobile' => 'Goyang',
        'fracture' => 'Fraktur',
    ];

    public const SURFACES = [
        'whole' => 'Seluruh gigi',
        'mesial' => 'Mesial',
        'distal' => 'Distal',
        'occlusal' => 'Oklusal',
        'buccal' => 'Bukal',
        'lingual' => 'Lingual',
        'palatal' => 'Palatal',
        'incisal' => 'Insisal',
    ];

    /** Gigi permanen FDI per kuadran (tampil). */
    public const PERMANENT = [
        'upper_right' => ['18', '17', '16', '15', '14', '13', '12', '11'],
        'upper_left' => ['21', '22', '23', '24', '25', '26', '27', '28'],
        'lower_right' => ['48', '47', '46', '45', '44', '43', '42', '41'],
        'lower_left' => ['31', '32', '33', '34', '35', '36', '37', '38'],
    ];

    /** Gigi sulung FDI per kuadran (tampil). */
    public const DECIDUOUS = [
        'upper_right' => ['55', '54', '53', '52', '51'],
        'upper_left' => ['61', '62', '63', '64', '65'],
        'lower_right' => ['85', '84', '83', '82', '81'],
        'lower_left' => ['71', '72', '73', '74', '75'],
    ];

    public const CHART_COLORS = [
        'sound' => 'bg-emerald-100 border-emerald-400 text-emerald-800',
        'caries' => 'bg-red-100 border-red-500 text-red-800',
        'filled' => 'bg-blue-100 border-blue-500 text-blue-800',
        'crown' => 'bg-amber-100 border-amber-500 text-amber-800',
        'missing' => 'bg-slate-200 border-slate-400 text-slate-500 line-through',
        'implant' => 'bg-violet-100 border-violet-500 text-violet-800',
        'denture' => 'bg-teal-100 border-teal-500 text-teal-800',
        'root' => 'bg-orange-100 border-orange-500 text-orange-800',
        'mobile' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
        'fracture' => 'bg-rose-100 border-rose-500 text-rose-800',
    ];

    public static function allTeeth(): array
    {
        return array_merge(
            ...array_values(self::PERMANENT),
            ...array_values(self::DECIDUOUS)
        );
    }

    protected $fillable = [
        'id',
        'visit_id',
        'fdi',
        'surface',
        'condition',
        'material',
        'notes',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }
}
