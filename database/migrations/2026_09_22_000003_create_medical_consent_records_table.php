<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Informed consent digital (tabel baru; kolom lama tidak diubah).
     */
    public function up(): void
    {
        Schema::create('medical_consent_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors', 'user_id')->nullOnDelete();
            $table->string('consent_type', 32)->default('treatment');
            $table->text('consent_text');
            $table->boolean('granted')->default(false);
            $table->string('granted_by_name')->nullable();
            $table->string('granted_by_relation', 32)->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'consent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_consent_records');
    }
};
