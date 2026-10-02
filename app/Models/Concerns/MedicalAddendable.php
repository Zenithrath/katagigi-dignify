<?php

namespace App\Models\Concerns;

use App\Models\MedicalRecordAddendum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Menonaktifkan hard-delete pada model medis dan menulis addendum
 * untuk setiap perubahan field (Permenkes 24/2022).
 *
 * Pakai: use MedicalAddendable;
 * - SoftDeletes aktif (delete → soft delete, restore bisa)
 * - saving event: bandingkan dirty attributes → tulis addendum
 * - deleting event: tulis addendum aksi delete sebelum soft delete
 */
trait MedicalAddendable
{
    public static function bootMedicalAddendable(): void
    {
        static::saved(function ($model) {
            if (! $model->wasChanged()) {
                return;
            }

            $original = $model->getOriginal();

            foreach ($model->getDirty() as $field => $newValue) {
                if (in_array($field, ['updated_at', 'created_at'], true)) {
                    continue;
                }

                $old = $original[$field] ?? null;
                if ($old === $newValue) {
                    continue;
                }

                MedicalRecordAddendum::record(
                    class_basename($model),
                    $model->getKey(),
                    $field,
                    $old,
                    $newValue,
                    request()?->input('audit_reason'),
                );
            }
        });

        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                MedicalRecordAddendum::record(
                    class_basename($model),
                    $model->getKey(),
                    '__deleted__',
                    $model->getAttributes(),
                    null,
                    request()?->input('audit_reason') ?? 'force-delete',
                );

                return;
            }

            MedicalRecordAddendum::record(
                class_basename($model),
                $model->getKey(),
                '__soft_deleted__',
                ['deleted' => false],
                ['deleted' => true],
                request()?->input('audit_reason'),
            );
        });
    }

    public static function addendumsFor(string $modelId)
    {
        return MedicalRecordAddendum::where('model_type', static::class)
            ->orWhere('model_type', class_basename(static::class))
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'desc');
    }
}
