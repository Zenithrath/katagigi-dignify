<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 fondasi: multi-cabang (single dulu) + kunjungan klinis.
     * Antrian = visits berstatus (clinical_status), bukan tabel terpisah.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 16)->unique();
            $table->string('org')->default('Klinik Kata Gigi');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('visit_number', 32)->unique();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->uuid('doctor_id');
            $table->date('visit_date');
            // REGISTERED → WAITING → CALLED → IN_TREATMENT → DONE → SIGNED
            $table->string('clinical_status', 16)->default('REGISTERED');
            // UNBILLED → BILLED (split invoice menyusul Fase 3)
            $table->string('billing_status', 16)->default('UNBILLED');
            $table->text('notes')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->foreignUuid('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('doctor_id')->references('user_id')->on('doctors')->cascadeOnDelete();
            $table->index(['visit_date', 'clinical_status']);
            $table->index(['patient_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
        Schema::dropIfExists('branches');
    }
};
