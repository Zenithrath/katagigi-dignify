<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 T3: aturan jasa medis + posting fee per tagihan lunas.
     */
    public function up(): void
    {
        Schema::create('doctor_fee_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('doctor_id')->unique();
            $table->float('percentage')->default(30);
            $table->float('xray_percentage')->default(20);
            $table->float('shift_allowance')->default(50000);
            $table->timestamps();

            $table->foreign('doctor_id')->references('user_id')->on('doctors')->cascadeOnDelete();
        });

        Schema::create('doctor_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('doctor_id');
            $table->foreignUuid('invoice_id')->unique()->constrained('invoices')->cascadeOnDelete();
            $table->float('base_amount')->default(0);
            $table->float('percentage')->default(30);
            $table->float('fee_amount')->default(0);
            $table->string('status', 16)->default('UNPAID');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('user_id')->on('doctors')->cascadeOnDelete();
            $table->index(['doctor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_fees');
        Schema::dropIfExists('doctor_fee_rules');
    }
};
