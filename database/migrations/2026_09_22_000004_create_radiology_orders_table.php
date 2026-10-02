<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('ordered_by')->nullable()->constrained('doctors', 'user_id')->nullOnDelete();
            $table->string('modality', 32)->default('DX');
            $table->string('body_site')->nullable();
            $table->text('clinical_indication');
            $table->string('priority', 16)->default('routine');
            $table->string('status', 16)->default('ORDERED');
            $table->timestamp('performed_at')->nullable();
            $table->text('result_text')->nullable();
            $table->string('result_path')->nullable();
            $table->string('satusehat_diagnostic_report_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index('satusehat_diagnostic_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_orders');
    }
};
