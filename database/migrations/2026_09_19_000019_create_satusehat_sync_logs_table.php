<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 T2: jejak sinkronisasi SATUSEHAT per resource FHIR.
     */
    public function up(): void
    {
        Schema::create('satusehat_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignUuid('visit_id')->nullable()->constrained('visits')->cascadeOnDelete();
            // Patient | Encounter | Condition | Procedure | Practitioner
            $table->string('resource_type', 32);
            $table->string('local_id')->nullable();
            $table->string('external_id')->nullable();
            // PENDING | SUCCESS | FAILED | SKIPPED
            $table->string('status', 16)->default('PENDING');
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('error')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['visit_id', 'resource_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satusehat_sync_logs');
    }
};
