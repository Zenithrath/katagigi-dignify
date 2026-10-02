<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4.3: master penjamin (umum/asuransi/korporasi) + pemilihan penjamin
     * saat pendaftaran pasien.
     */
    public function up(): void
    {
        Schema::create('master_insurances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type', 16)->default('private'); // government | private | corporate
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->foreignUuid('insurance_id')->nullable()->after('marital_status')
                ->constrained('master_insurances')->nullOnDelete();
            $table->string('insurance_number', 64)->nullable()->after('insurance_id'); // no. kartu/BPJS
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('insurance_id');
            $table->dropColumn('insurance_number');
        });
        Schema::dropIfExists('master_insurances');
    }
};
