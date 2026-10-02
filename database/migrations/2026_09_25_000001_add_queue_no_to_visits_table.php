<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomor antrean per dokter per hari: check-in memberi nomor urut
     * berikutnya untuk (doctor_id, visit_date) agar list tiap dokter
     * tidak campur.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->unsignedInteger('queue_no')->nullable()->after('visit_number');
            $table->index(['doctor_id', 'visit_date'], 'visits_doctor_date_index');
        });

        // Backfill: nomor urut per dokter per tanggal sesuai urutan check-in.
        // whereDate: SQLite menyimpan kolom date sebagai 'Y-m-d H:i:s'.
        $dates = DB::table('visits')->selectRaw('doctor_id, date(visit_date) as d')->distinct()->get();
        foreach ($dates as $group) {
            $no = 0;
            $ids = DB::table('visits')
                ->where('doctor_id', $group->doctor_id)
                ->whereDate('visit_date', $group->d)
                ->orderBy('created_at')
                ->pluck('id');
            foreach ($ids as $id) {
                DB::table('visits')->where('id', $id)->update(['queue_no' => ++$no]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_doctor_date_index');
            $table->dropColumn('queue_no');
        });
    }
};
