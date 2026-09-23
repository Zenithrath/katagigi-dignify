<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Satu baris koreksi pada data medis (Permenkes 24/2022).
 * Dipancarkan otomatis oleh MedicalAddendable trait.
 */
class MedicalRecordAddendum extends Model
{
    use HasFactory;

    protected $table = 'medical_record_addendums';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'model_type',
        'model_id',
        'field',
        'old_value',
        'new_value',
        'reason',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function record(
        string $modelType,
        string $modelId,
        string $field,
        mixed $old,
        mixed $new,
        ?string $reason = null,
    ): self {
        return self::create([
            'id' => (string) Str::uuid(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'field' => $field,
            'old_value' => $old,
            'new_value' => $new,
            'reason' => $reason,
            'user_id' => Auth::id(),
        ]);
    }
}
