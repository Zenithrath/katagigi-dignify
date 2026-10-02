<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * D-06a: proposed_by/decided_by bigint → uuid agar cocok dengan users.id.
     * Rebuild portabel (tanpa doctrine/dbal): buat tabel baru → salin → tukar.
     * Baris legacy yang pengusulnya bukan uuid valid tidak dapat disalin apa
     * adanya — catat manual bila migrasi gagal di FK pada database lama.
     */
    public function up(): void
    {
        Schema::create('transaction_cancellation_requests_new', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignUuid('proposed_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status', 16)->default('PROPOSED'); // PROPOSED | APPROVED | REJECTED
            $table->foreignUuid('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'status']);
        });

        foreach (DB::table('transaction_cancellation_requests')->get() as $row) {
            DB::table('transaction_cancellation_requests_new')->insert((array) $row);
        }

        Schema::drop('transaction_cancellation_requests');
        Schema::rename('transaction_cancellation_requests_new', 'transaction_cancellation_requests');
    }

    public function down(): void
    {
        Schema::create('transaction_cancellation_requests_legacy', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('proposed_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status', 16)->default('PROPOSED');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'status']);
        });

        foreach (DB::table('transaction_cancellation_requests')->get() as $row) {
            DB::table('transaction_cancellation_requests_legacy')->insert((array) $row);
        }

        Schema::drop('transaction_cancellation_requests');
        Schema::rename('transaction_cancellation_requests_legacy', 'transaction_cancellation_requests');
    }
};
