<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alur usul-kunci-approve: admin mengusulkan, manajemen menyetujui/menolak.
     * Satu transaksi hanya boleh punya 1 usulan PROPOSED dalam satu waktu.
     */
    public function up(): void
    {
        Schema::create('transaction_cancellation_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('proposed_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status', 16)->default('PROPOSED'); // PROPOSED | APPROVED | REJECTED
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_cancellation_requests');
    }
};
