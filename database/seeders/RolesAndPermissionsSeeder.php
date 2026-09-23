<?php

namespace Database\Seeders;

use App\Helpers\Access;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menanam izin + role dari matriks tunggal {@see Access}.
 * Hirarki & jobdesk per role dijelaskan di kelas tersebut (dan docs/PRD §4).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Access::allPermissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (Access::matrix() as $role => $grants) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web'])
                ->syncPermissions($grants === ['*'] ? Permission::all() : $grants);
        }

        $demos = [
            ['Manajemen', 'manajemen@gmail.com', 'manajemen'],
            ['Admin', 'admin@gmail.com', 'admin'],
            ['Doctor', 'doctor@gmail.com', 'doctor'],
            ['Nurse', 'nurse@gmail.com', 'nurse'],
        ];
        foreach ($demos as [$name, $email, $role]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password')]
            )->syncRoles([$role]);
        }
    }
}
