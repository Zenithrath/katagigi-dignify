<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 Task 4: temuan odontogram per visit + gigi FDI + permukaan.
     */
    public function up(): void
    {
        Schema::create('odontogram_findings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('fdi', 8);
            $table->string('surface', 16)->default('whole');
            $table->string('condition', 32);
            $table->string('material')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['visit_id', 'fdi', 'surface']);
            $table->index(['visit_id', 'fdi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_findings');
    }
};
