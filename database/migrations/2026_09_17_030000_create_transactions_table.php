<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema final tabel transactions dari app lama + kolom V2:
     * - is_locked: terkunci saat ada usulan pembatalan PROPOSED
     * - sequence: nomor nota YYNNNNN — trigger Postgres lama diganti
     *   generator level aplikasi (TransactionService::nextSequence) agar
     *   portabel sqlite/mysql/pgsql.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('sequence')->unique();
            $table->uuid('down_payment_transaction_id')->nullable();
            $table->boolean('has_down_payment')->default(false);
            $table->boolean('is_endorsed')->default(false);
            $table->boolean('has_installment')->nullable();
            $table->double('current_payment')->default(0);
            $table->uuid('patient_id');
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->string('patient_code');
            $table->uuid('doctor_id');
            $table->string('doctor_nipp')->nullable();
            $table->string('doctor_name');
            $table->uuid('nurse_id')->nullable();
            $table->string('nurse_nipp')->nullable();
            $table->string('nurse_name')->nullable();
            $table->uuid('appointment_id');
            $table->string('appointment_datetime');
            $table->date('next_schedule')->nullable();
            $table->longText('services');
            $table->float('price');
            $table->float('discount');
            $table->float('billing');
            $table->string('payment_method')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->string('voucher_code')->nullable();
            $table->uuid('referenced_installment_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('user_id')->on('doctors')->cascadeOnDelete();
            $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
        });

        Schema::create('transaction_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_id');
            $table->string('service_name');
            $table->uuid('transaction_id');
            $table->string('price');
            $table->timestamps();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        Schema::create('transaction_nurses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('nurse_id');
            $table->string('nurse_name');
            $table->uuid('transaction_id');
            $table->timestamps();
            $table->foreign('nurse_id')->references('user_id')->on('nurses')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->uuid('transaction_id')->primary();
            $table->uuid('patient_id');
            $table->float('amount');
            $table->string('status');
            $table->timestamps();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        Schema::create('installment_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('installment_id');
            $table->uuid('transaction_id')->nullable();
            $table->string('type');
            $table->integer('step');
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->float('amount');
            $table->string('status');
            $table->timestamps();
            $table->foreign('installment_id')->references('transaction_id')->on('installments')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('referenced_installment_id')->references('transaction_id')->on('installments')->cascadeOnDelete();
            $table->foreign('down_payment_transaction_id')->references('id')->on('transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['referenced_installment_id']);
            $table->dropForeign(['down_payment_transaction_id']);
        });
        Schema::dropIfExists('installment_steps');
        Schema::dropIfExists('installments');
        Schema::dropIfExists('transaction_nurses');
        Schema::dropIfExists('transaction_services');
        Schema::dropIfExists('transactions');
    }
};
