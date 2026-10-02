<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Jadwal praktik default: Senin–Sabtu 08:00–17:00 AVAILABLE
 * untuk semua dokter. Tanpa jadwal, validasi appointment
 * (AvailableDoctor/DoctorSchedule) selalu menolak.
 */
class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];

        foreach (DB::table('doctors')->pluck('user_id') as $doctorId) {
            foreach ($days as $day) {
                DB::table('schedules')->updateOrInsert(
                    ['doctor_id' => $doctorId, 'day' => $day],
                    [
                        'id' => DB::table('schedules')
                            ->where('doctor_id', $doctorId)->where('day', $day)->value('id')
                            ?? Uuid::uuid4()->toString(),
                        'availability' => 'AVAILABLE',
                        'time_start' => '08:00:00',
                        'time_end' => '17:00:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
