<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 Task 6: rencana perawatan + item per visit.
     */
    public function up(): void
    {
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('title');
            // PLANNED → SCHEDULED → IN_PROGRESS → COMPLETED | CANCELLED
            $table->string('status', 16)->default('PLANNED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('treatment_plan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
            $table->string('tooth_fdi', 8)->default('');
            $table->string('description');
            $table->float('estimated_price')->default(0);
            $table->integer('priority')->default(2);
            $table->string('status', 16)->default('PLANNED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_items');
        Schema::dropIfExists('treatment_plans');
    }
};
