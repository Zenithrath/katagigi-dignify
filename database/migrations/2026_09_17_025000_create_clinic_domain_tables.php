<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema final domain klinik dari app lama (gabungan ~30 migrasi).
     * Tabel terkait transaksi ada di 030000 (butuh tabel transactions dulu).
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('zip_code')->nullable();
            $table->string('tonarigumi')->nullable();
            $table->string('street')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('regency')->nullable();
            $table->string('province')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('nipp');
            $table->string('niptk')->nullable();
            $table->text('profile_picture')->nullable();
            $table->text('cover_picture')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('nipp');
            $table->string('niptk')->nullable();
            $table->integer('target')->default(55);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('ihs_id')->nullable()->unique();
            $table->text('profile_picture')->nullable();
            $table->text('cover_picture')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('nurses', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('nipp');
            $table->string('niptk')->nullable();
            $table->text('profile_picture')->nullable();
            $table->text('cover_picture')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->uuid('patient_id')->primary();
            $table->string('zip_code')->nullable();
            $table->string('tonarigumi')->nullable();
            $table->string('street')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('regency')->nullable();
            $table->string('province')->nullable();
            $table->timestamps();
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('REG');
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->float('lower_price');
            $table->float('upper_price');
            $table->string('doctor_commision')->nullable();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories');
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('doctor_id');
            $table->enum('day', ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY']);
            $table->enum('availability', ['AVAILABLE', 'UNAVAILABLE'])->default('AVAILABLE');
            $table->time('time_start');
            $table->time('time_end');
            $table->timestamps();
            $table->foreign('doctor_id')->references('user_id')->on('doctors')->cascadeOnDelete();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->string('patient_code');
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->uuid('doctor_id');
            $table->string('doctor_name');
            $table->string('doctor_nipp')->nullable();
            $table->string('doctor_niptk')->nullable();
            $table->date('date');
            $table->longText('services');
            $table->time('time_start');
            $table->time('time_end');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->timestamps();
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('user_id')->on('doctors')->cascadeOnDelete();
        });

        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->string('patient_code');
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->string('patient_address');
            $table->uuid('doctor_id');
            $table->string('doctor_name');
            $table->string('doctor_nipp')->nullable();
            $table->string('doctor_niptk')->nullable();
            $table->uuid('appointment_id');
            $table->date('appointment_date');
            $table->time('time_start');
            $table->time('time_end');
            $table->longText('services');
            $table->longText('anamnesis');
            $table->longText('diagnosis');
            $table->longText('therapy');
            $table->longText('prescription')->nullable();
            $table->longText('checkup_result')->nullable();
            $table->string('next_schedule')->nullable();
            $table->float('price');
            $table->float('discount');
            $table->float('billing');
            $table->enum('promat', ['PROMAT', 'NO PROMAT']);
            $table->string('blood_pressure')->nullable();
            $table->enum('cooperativity', ['COOPERATIVE', 'LESS COOPERATIVE', 'NOT COOPERATIVE']);
            $table->longText('image_before')->nullable();
            $table->longText('image_after')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variables');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('services');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('patient_addresses');
        Schema::dropIfExists('nurses');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('user_addresses');
    }
};
