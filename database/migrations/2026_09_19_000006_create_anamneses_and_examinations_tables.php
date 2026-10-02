<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 Task 3: anamnesis (1-1 per visit) + pemeriksaan SOAP (1-1 per visit).
     */
    public function up(): void
    {
        Schema::create('anamneses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->unique()->constrained('visits')->cascadeOnDelete();
            $table->text('chief_complaint');
            $table->text('present_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('dental_history')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->timestamps();
        });

        Schema::create('examinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->unique()->constrained('visits')->cascadeOnDelete();
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->string('blood_pressure', 16)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
        Schema::dropIfExists('anamneses');
    }
};
