<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // kode RM internal klinik
            $table->string('name');
            // SATUSEHAT-ready: NIK wajib diisi bertahap, IHS ID hasil bridging
            $table->string('nik', 16)->nullable()->unique();
            $table->string('ihs_id')->nullable()->unique();
            $table->string('birth_place')->nullable();
            $table->string('phone');
            $table->string('email')->nullable()->unique();
            $table->date('birthdate');
            $table->string('gender', 16); // male | female (ikut FHIR)
            $table->string('religion', 32)->nullable();
            $table->boolean('satusehat_consent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
