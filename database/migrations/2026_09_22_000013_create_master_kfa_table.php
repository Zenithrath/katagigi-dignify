<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4.2: master KFA lokal (kamus kode obat/BHP Kemenkes) untuk
     * autocomplete resep + validator kfa_code sebelum kirim MedicationRequest.
     */
    public function up(): void
    {
        Schema::create('master_kfa', function (Blueprint $table) {
            $table->string('code', 64)->primary();
            $table->string('name');
            $table->string('dosage_form', 64)->nullable();
            $table->boolean('is_drug')->default(true); // false = alat kesehatan/BHP
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kfa');
    }
};
