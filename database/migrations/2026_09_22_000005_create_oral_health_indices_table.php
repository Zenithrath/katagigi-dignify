<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indeks kesehatan mulut: OHI-S (Oral Hygiene Index — Simplified) & DMF-T.
     */
    public function up(): void
    {
        Schema::create('oral_health_indices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->unique()->constrained('visits')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->decimal('ohis_debris', 3, 1)->nullable();
            $table->decimal('ohis_calculus', 3, 1)->nullable();
            $table->decimal('ohis_total', 3, 1)->nullable();
            $table->unsignedTinyInteger('d_count')->default(0);
            $table->unsignedTinyInteger('m_count')->default(0);
            $table->unsignedTinyInteger('f_count')->default(0);
            $table->decimal('dmt_index', 3, 1)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oral_health_indices');
    }
};
