<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosis_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // ICD10 = diagnosa, ICD9 = tindakan/prosedur gigi, SNOMED = istilah klinis
            $table->string('system', 16); // ICD10 | ICD9 | SNOMED
            $table->string('code', 32);
            $table->string('display_id');
            $table->string('display_en')->nullable();
            // Sinonim bahasa awam: "gigi berlubang", "ngilu", "bengkak gusi" ...
            $table->json('keywords')->nullable();
            $table->string('category')->nullable(); // cth: konservasi, bedah-mulut, ortodonsia
            $table->string('source')->nullable(); // Kemenkes / master internal
            $table->string('version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['system', 'code']);
            $table->index(['system', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosis_codes');
    }
};
