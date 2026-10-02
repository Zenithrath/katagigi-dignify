<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 T1: tagihan (split dari transactions; data lama dipertahankan).
     * Status: DRAFT → ISSUED → PARTIALLY_PAID → PAID | VOID.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 32)->unique();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->uuid('doctor_id')->nullable();
            $table->string('status', 16)->default('DRAFT');
            $table->float('subtotal')->default(0);
            $table->float('discount')->default(0);
            $table->float('tax')->default(0);
            $table->float('total')->default(0);
            $table->foreignUuid('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('user_id')->on('doctors')->nullOnDelete();
            $table->index(['status', 'created_at']);
            $table->index(['patient_id', 'created_at']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            // TREATMENT (dari visit_treatments) | MEDICINE | OTHER
            $table->string('item_type', 16)->default('OTHER');
            $table->string('description');
            $table->string('tooth_fdi', 8)->default('');
            $table->string('reference_code', 64)->nullable();
            $table->integer('quantity')->default(1);
            $table->float('unit_price')->default(0);
            $table->float('amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
