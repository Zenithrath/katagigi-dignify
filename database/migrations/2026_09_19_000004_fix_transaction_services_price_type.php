<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * D-06f: transaction_services.price string → float agar selaras dengan
     * transactions.price dan services.lower/upper_price (aritmetika & laporan).
     * Rebuild portabel (tanpa doctrine/dbal): buat tabel baru → salin dengan
     * cast → tukar.
     */
    public function up(): void
    {
        Schema::create('transaction_services_new', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_id');
            $table->string('service_name');
            $table->uuid('transaction_id');
            $table->float('price');
            $table->timestamps();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        foreach (DB::table('transaction_services')->get() as $row) {
            $row = (array) $row;
            $row['price'] = (float) $row['price'];
            DB::table('transaction_services_new')->insert($row);
        }

        Schema::drop('transaction_services');
        Schema::rename('transaction_services_new', 'transaction_services');
    }

    public function down(): void
    {
        Schema::create('transaction_services_legacy', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_id');
            $table->string('service_name');
            $table->uuid('transaction_id');
            $table->string('price');
            $table->timestamps();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete();
        });

        foreach (DB::table('transaction_services')->get() as $row) {
            $row = (array) $row;
            $row['price'] = (string) $row['price'];
            DB::table('transaction_services_legacy')->insert($row);
        }

        Schema::drop('transaction_services');
        Schema::rename('transaction_services_legacy', 'transaction_services');
    }
};
