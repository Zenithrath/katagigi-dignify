<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('organization_ihs')->nullable()->unique();
            $table->string('organization_name');
            $table->string('nakes_facility_code', 32)->nullable();
            $table->string('location_ihs')->nullable();
            $table->string('location_name')->nullable();
            $table->string('region_code', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('practitioner_ihs')->nullable();
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('region_code');
        });

        if (! Schema::hasColumn('branches', 'organization_ihs')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('organization_ihs')->nullable()->after('phone');
                $table->string('location_ihs')->nullable()->after('organization_ihs');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'organization_ihs')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn(['organization_ihs', 'location_ihs']);
            });
        }
        Schema::dropIfExists('organization_profiles');
    }
};
