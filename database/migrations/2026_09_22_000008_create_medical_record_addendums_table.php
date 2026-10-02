<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Addendum rekam medis (Permenkes 24/2022): setiap koreksi pada data medis
     * menyimpan nilai lama, nilai baru, alasan, dan aktor — tanpa hard delete.
     */
    public function up(): void
    {
        Schema::create('medical_record_addendums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('model_type', 64);
            $table->uuid('model_id');
            $table->string('field', 128);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_addendums');
    }
};
