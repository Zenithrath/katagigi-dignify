<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Master dokter demo: profil doctors untuk akun doctor@gmail.com
 * (dibuat RolesAndPermissionsSeeder) + 1 dokter tambahan.
 * Tanpa baris doctors, dropdown dokter kosong dan validasi
 * AvailableDoctor/DoctorSchedule selalu gagal.
 */
class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::firstOrCreate(
            ['email' => 'doctor@gmail.com'],
            ['name' => 'Doctor', 'password' => Hash::make('password')]
        );

        $doctors = [
            ['user_id' => $demo->id, 'nipp' => 'DOK001', 'niptk' => 'T001'],
        ];

        foreach ($doctors as $d) {
            DB::table('doctors')->updateOrInsert(
                ['user_id' => $d['user_id']],
                [
                    'nipp' => $d['nipp'],
                    'niptk' => $d['niptk'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $demo->assignRole('doctor');
    }
}
