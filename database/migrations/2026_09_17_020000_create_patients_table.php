<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema final tabel patients dari app lama + kolom SATUSEHAT-ready V2
     * (nik, ihs_id, birth_place, satusehat_consent).
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code');
            $table->string('email')->nullable();
            $table->string('payment_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('birthdate')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('ihs_id')->nullable()->unique();
            $table->enum('religion', [
                'ISLAM', 'CATHOLIC', 'BUDDHISM', 'HINDUISM',
                'KONGHUCHU', 'OTHER', 'CHRISTIANITY',
            ])->nullable()->default('OTHER');
            $table->enum('gender', ['MALE', 'FEMALE'])->default('MALE');
            $table->string('picture')->nullable();
            $table->longText('sosmed')->nullable();
            $table->boolean('satusehat_consent')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
