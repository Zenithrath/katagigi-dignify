<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4.1: alamat pasien menyimpan kode wilayah Kemendagri (opsional)
     * agar payload FHIR Address SATUSEHAT memakai kode resmi, bukan nama bebas.
     */
    public function up(): void
    {
        Schema::table('patient_addresses', function (Blueprint $table) {
            $table->string('region_code', 10)->nullable()->after('zip_code');
            $table->index('region_code');
        });
    }

    public function down(): void
    {
        Schema::table('patient_addresses', function (Blueprint $table) {
            $table->dropIndex(['region_code']);
            $table->dropColumn('region_code');
        });
    }
};
