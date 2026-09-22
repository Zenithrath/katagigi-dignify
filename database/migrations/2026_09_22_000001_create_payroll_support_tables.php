<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel BARU untuk payroll asisten — tidak mengubah tabel lama mana pun.
     * - holidays: daftar tanggal merah (input manual admin).
     * - nurse_attendances: jam masuk/pulang perawat per hari (diinput asisten).
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('nurse_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->index();
            $table->date('date')->index();
            $table->time('clock_in');
            $table->time('clock_out');
            $table->time('scheduled_end');
            $table->string('note')->nullable();
            $table->string('input_by');
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurse_attendances');
        Schema::dropIfExists('holidays');
    }
};
