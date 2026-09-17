<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kode diagnosis resmi (ICD-10/ICD-9/SNOMED) per rekam medis.
     * Kolom diagnosis/therapy teks lama tetap ada sebagai catatan tambahan.
     */
    public function up(): void
    {
        Schema::create('medical_record_diagnoses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('medical_record_id');
            $table->uuid('diagnosis_code_id');
            // Snapshot agar riwayat tetap benar walau master berubah
            $table->string('system', 16);
            $table->string('code', 32);
            $table->string('display');
            $table->timestamps();

            $table->foreign('medical_record_id')->references('id')->on('medical_records')->cascadeOnDelete();
            $table->foreign('diagnosis_code_id')->references('id')->on('diagnosis_codes');
            $table->unique(['medical_record_id', 'diagnosis_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_diagnoses');
    }
};
