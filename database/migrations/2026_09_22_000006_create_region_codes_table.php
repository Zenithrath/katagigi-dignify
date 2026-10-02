<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wilayah administrasi Kemendagri (provinsi / kota / kecamatan / desa)
     * untuk alamat pasien & Organization SATUSEHAT.
     */
    public function up(): void
    {
        Schema::create('region_codes', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name');
            $table->string('level', 16);
            $table->string('parent_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_code', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_codes');
    }
};
