<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 44 permission warisan app lama (11 modul x read/create/update/delete)
        $modules = [
            'schedule', 'appointment', 'service', 'category',
            'admin', 'doctor', 'nurse', 'patient',
            'medical record', 'transaction', 'turnover',
        ];
        $legacy = [];
        foreach ($modules as $module) {
            foreach (['read', 'create', 'update', 'delete'] as $action) {
                $legacy[] = "$action $module";
            }
        }

        // Permission baru V2
        $fresh = [
            'request cancellation',   // admin operasional: mengusulkan pembatalan nota
            'approve cancellation',   // manajemen: menyetujui / menolak usulan
            'read diagnosis code',
            'manage diagnosis code',  // manajemen: kelola master ICD/SNOMED
            // Fase 2: kunjungan klinis
            'read visit',
            'create visit',
            'update visit',
            'sign visit',             // dokter: kunci visit (SIGNED)
            // Fase 3: pencairan jasa medis (manajemen)
            'manage doctor fee',
            // Fase 3: inventory (admin kelola, nakes baca)
            'read inventory',
            'manage inventory',
            // Fase 3: beban operasional (admin kelola, nakes baca)
            'read expense',
            'manage expense',
            // Fase 4: cabang (manajemen)
            'manage branch',
        ];

        foreach ([...$legacy, ...$fresh] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // MANAJEMEN = ex-admin lama: semua akses + approval
        $manajemen = Role::firstOrCreate(['name' => 'manajemen', 'guard_name' => 'web']);
        $manajemen->syncPermissions(Permission::all());

        // ADMIN operasional: reservasi + penjadwalan + kasir (buat nota, reschedule
        // kontrol) + usul batal. Tanpa: kelola user, hapus master, batal langsung, approve.
        // D-04: kasir dirangkap admin sesuai praktik klinik (PRD §1 keputusan 3).
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'create schedule', 'read schedule', 'update schedule',
            'create appointment', 'read appointment', 'update appointment',
            'create patient', 'read patient', 'update patient',
            'read medical record',
            'create transaction', 'read transaction', 'update transaction',
            'read turnover',
            'read diagnosis code',
            'request cancellation',
            'read visit', 'create visit', 'update visit',
            'read inventory', 'manage inventory',
            'read expense', 'manage expense',
        ]);

        // DOCTOR: baca + tulis rekam medis (sama seperti app lama)
        $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $doctor->syncPermissions([
            'read patient',
            'read medical record', 'create medical record',
            'update medical record', 'delete medical record',
            'read appointment',
            'read schedule',
            'read transaction',
            'read turnover',
            'read diagnosis code',
            'read visit', 'create visit', 'update visit', 'sign visit',
            'read inventory',
            'read expense',
        ]);

        // NURSE: front-office. D-04: tanpa hapus pasien (hanya manajemen, PRD §4);
        // kasir (buat nota, reschedule) dirangkap nurse bila ditugaskan (PRD §1).
        $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web']);
        $nurse->syncPermissions([
            'read patient', 'create patient', 'update patient',
            'read medical record',
            'read appointment', 'create appointment',
            'update appointment', 'delete appointment',
            'read schedule',
            'create transaction', 'read transaction', 'update transaction',
            'read turnover',
            'read diagnosis code',
            'read visit', 'create visit', 'update visit',
            'read inventory',
            'read expense',
        ]);

        $demos = [
            ['Manajemen', 'manajemen@gmail.com', 'manajemen'],
            ['Admin', 'admin@gmail.com', 'admin'],
            ['Doctor', 'doctor@gmail.com', 'doctor'],
            ['Nurse', 'nurse@gmail.com', 'nurse'],
        ];
        foreach ($demos as [$name, $email, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password')]
            );
            $user->syncRoles([$role]);
        }
    }
}
