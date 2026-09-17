<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stub minimal agar alur approval pembatalan nota bisa jalan.
     * Akan diperluas mengikuti skema transaksi app lama
     * (services JSON, cicilan, voucher, dsb) pada iterasi kasir.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // nomor nota
            $table->foreignUuid('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('patient_name')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 32)->default('PAID'); // PAID | CANCELED
            $table->boolean('is_locked')->default(false); // terkunci saat ada usulan batal pending
            $table->timestamp('canceled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_locked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
