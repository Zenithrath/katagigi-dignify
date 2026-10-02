<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kelengkapan demografi SSP: status perkawinan pasien dipetakan ke
     * Patient.maritalStatus (v3-MaritalStatus) saat sinkronisasi.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('marital_status', 1)->nullable()->after('religion'); // S/M/W/D per v3-MaritalStatus
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('marital_status');
        });
    }
};
