<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 Task 5: diagnosis ICD-10 + tindakan ICD-9-CM per visit/gigi.
     * tooth_fdi '' = tidak spesifik gigi (string agar unique bekerja).
     */
    public function up(): void
    {
        Schema::create('visit_diagnoses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('tooth_fdi', 8)->default('');
            $table->foreignUuid('diagnosis_code_id')->constrained('diagnosis_codes')->restrictOnDelete();
            $table->string('system', 16);
            $table->string('code', 32);
            $table->string('display');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->unique(['visit_id', 'diagnosis_code_id', 'tooth_fdi']);
        });

        Schema::create('visit_treatments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('tooth_fdi', 8)->default('');
            $table->foreignUuid('procedure_code_id')->constrained('diagnosis_codes')->restrictOnDelete();
            $table->string('system', 16);
            $table->string('code', 32);
            $table->string('procedure');
            $table->integer('quantity')->default(1);
            $table->float('unit_price')->default(0);
            $table->timestamps();

            $table->unique(['visit_id', 'procedure_code_id', 'tooth_fdi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_treatments');
        Schema::dropIfExists('visit_diagnoses');
    }
};
