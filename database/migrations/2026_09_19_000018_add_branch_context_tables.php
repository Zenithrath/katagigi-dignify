<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 T1: konteks cabang pada tabel klinis & billing.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignUuid('branch_id')->nullable()->after('code')->constrained('branches')->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
