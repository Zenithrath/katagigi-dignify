<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3.2: pemeriksaan dental standar Kemenkes — oklusi, torus,
     * palatum, diastema, dan relasi sentral (molar/canine) di examinations.
     */
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->string('occlusion', 32)->nullable()->after('blood_pressure');   // normal / deep bite / open bite / cross bite / edge to edge
            $table->string('torus', 32)->nullable()->after('occlusion');            // absent / palatinus / mandibularis / both
            $table->string('palatum', 32)->nullable()->after('torus');              // normal / high / cleft
            $table->string('diastema', 32)->nullable()->after('palatum');           // absent / present / closed
            $table->string('molar_relation', 32)->nullable()->after('diastema');    // class I / II / III
            $table->string('canine_relation', 32)->nullable()->after('molar_relation');
            $table->text('other_oral_findings')->nullable()->after('canine_relation');
        });
    }

    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn([
                'occlusion', 'torus', 'palatum', 'diastema',
                'molar_relation', 'canine_relation', 'other_oral_findings',
            ]);
        });
    }
};
