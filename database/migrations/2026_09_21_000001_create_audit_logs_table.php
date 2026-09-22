<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4: audit_logs formal (wajib Permenkes 24/2022 + PRD §3.4).
     * Mencatat who (user), what (action/entity), when (created_at),
     * old/new (JSON), why (reason) — tanpa hard-delete rekam medis.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64); // sign-visit | update-medical-record | cancel-transaction | ...
            $table->string('entity_type', 64); // visit | medical_record | transaction | ...
            $table->string('entity_id', 64)->nullable();
            $table->json('old')->nullable();
            $table->json('new')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
