<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 T4: inventory ringan (item + batch + jurnal movement).
     * Stok = jumlah qty batch; movement = jejak audit.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('unit', 16)->default('pcs');
            $table->float('min_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('batch_no');
            $table->date('expiry_date')->nullable();
            $table->float('quantity')->default(0);
            $table->float('buy_price')->default(0);
            $table->timestamps();

            $table->unique(['inventory_item_id', 'batch_no']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignUuid('batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            // IN | OUT | ADJUST
            $table->string('type', 16);
            $table->float('quantity');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('inventory_items');
    }
};
