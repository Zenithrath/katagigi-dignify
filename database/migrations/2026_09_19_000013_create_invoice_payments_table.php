<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 T2: pembayaran tagihan (satu tagihan boleh beberapa kali bayar).
     */
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->float('amount');
            // CASH | TRANSFER | QRIS | DEBIT | CREDIT
            $table->string('method', 16);
            $table->dateTime('paid_at');
            $table->foreignUuid('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
