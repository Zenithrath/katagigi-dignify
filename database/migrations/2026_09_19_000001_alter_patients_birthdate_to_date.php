<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * D-01: ubah patients.birthdate string -> date (Fase 1).
     * Portabel SQLite/MySQL/PgSQL tanpa doctrine/dbal:
     * - SQLite: no-op (type affinity teks, format Y-m-d tetap valid).
     * - MySQL/PgSQL: ALTER via SQL native per driver.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `patients` MODIFY `birthdate` DATE NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "patients" ALTER COLUMN "birthdate" TYPE DATE USING "birthdate"::date');
            DB::statement('ALTER TABLE "patients" ALTER COLUMN "birthdate" DROP NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `patients` MODIFY `birthdate` VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "patients" ALTER COLUMN "birthdate" TYPE VARCHAR(255) USING "birthdate"::text');
        }
    }
};
