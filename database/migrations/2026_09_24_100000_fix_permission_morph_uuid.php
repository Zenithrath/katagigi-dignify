<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $morphKey = config('permission.column_names.model_morph_key', 'model_uuid');

        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // 1. Pastikan kolom morph UUID ada.
            if (! Schema::hasColumn($table, $morphKey)) {
                Schema::table($table, function (Blueprint $t) use ($morphKey, $table) {
                    $t->uuid($morphKey)->nullable();
                    $t->index([$morphKey, 'model_type'], $table.'_morph_uuid_index');
                });
            }

            // 2. Salin data legacy model_id -> model_uuid bila keduanya ada.
            if (Schema::hasColumn($table, 'model_id') && Schema::hasColumn($table, $morphKey) && $morphKey !== 'model_id') {
                DB::statement("UPDATE \"{$table}\" SET \"{$morphKey}\" = CAST(\"model_id\" AS CHAR) WHERE \"{$morphKey}\" IS NULL AND \"model_id\" IS NOT NULL");
            }
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        // Sengaja no-op: kolom legacy dibiarkan agar rollback aman.
    }
};
