<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 roadmap SATUSEHAT: kredensial per cabang (2 cabang = 2 klien
     * SATUSEHAT berbeda) + identifier Organization/Location per cabang.
     */
    public function up(): void
    {
        Schema::create('satusehat_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->unique()->constrained('branches')->cascadeOnDelete();
            $table->string('client_id');
            // Terenkripsi (cast encrypted) — tidak boleh pernah bocor ke UI.
            $table->text('client_secret');
            $table->string('organization_id')->nullable();
            $table->string('location_id')->nullable();
            $table->string('environment', 16)->default('sandbox'); // sandbox | production
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->string('satusehat_org_id')->nullable()->after('location_ihs');
            $table->string('satusehat_location_id')->nullable()->after('satusehat_org_id');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['satusehat_org_id', 'satusehat_location_id']);
        });
        Schema::dropIfExists('satusehat_credentials');
    }
};
