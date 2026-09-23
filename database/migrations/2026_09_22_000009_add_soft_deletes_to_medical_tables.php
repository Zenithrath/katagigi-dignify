<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom soft-delete untuk model medis yang wajib SoftDeletes
     * (Permenkes 24/2022 — no hard delete).
     */
    public function up(): void
    {
        foreach (['medical_records', 'odontogram_findings', 'visit_diagnoses', 'visit_treatments', 'vital_signs'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['medical_records', 'odontogram_findings', 'visit_diagnoses', 'visit_treatments', 'vital_signs'] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropSoftDeletes();
                });
            }
        }
    }
};
