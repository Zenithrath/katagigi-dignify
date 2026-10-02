<?php

namespace Tests\Unit;

use App\Helpers\Access;
use PHPUnit\Framework\TestCase;

/**
 * Matriks RBAC: hierarki, cakupan izin per role, dan konsistensi daftar izin.
 */
class AccessTest extends TestCase
{
    public function test_role_hierarchy_is_documented_top_down(): void
    {
        $this->assertSame(['manajemen', 'admin', 'doctor', 'nurse'], Access::HIERARCHY);
        $this->assertSame(Access::HIERARCHY, array_keys(Access::matrix()));
    }

    public function test_manajemen_owns_every_permission(): void
    {
        $this->assertSame(['*'], Access::matrix()['manajemen']);
    }

    public function test_clinical_writes_are_scoped_to_doctor_and_manajemen(): void
    {
        $clinical = [
            'write examination', 'write odontogram', 'write diagnosis', 'write treatment',
            'write treatment plan', 'write prescription', 'write radiology', 'revoke consent',
        ];

        foreach ($clinical as $permission) {
            $this->assertContains($permission, Access::matrix()['doctor'], "$permission harus milik doctor");
            $this->assertNotContains($permission, Access::matrix()['admin'], "$permission tidak untuk admin (front office)");
            $this->assertNotContains($permission, Access::matrix()['nurse'], "$permission tidak untuk nurse (asisten)");
        }
    }

    public function test_nurse_assists_without_owning_the_clinical_record(): void
    {
        $nurse = Access::matrix()['nurse'];

        // Pendukung: draf anamnesis, tanda vital, OHI-S, lampiran, absensi sendiri.
        foreach (['write anamnesis', 'write vital sign', 'write oral health index', 'upload visit attachment', 'record own attendance'] as $permission) {
            $this->assertContains($permission, $nurse);
        }
        // Tanpa kewenangan final medis & keuangan.
        foreach (['sign visit', 'delete patient', 'approve cancellation', 'manage doctor fee', 'manage satusehat', 'read audit log', 'manage branch'] as $permission) {
            $this->assertNotContains($permission, $nurse);
        }
    }

    public function test_admin_handles_operations_but_not_master_privileges(): void
    {
        $admin = Access::matrix()['admin'];

        foreach (['read patient', 'create patient', 'update patient', 'manage inventory', 'manage expense', 'request cancellation', 'read assistant payroll', 'record consent'] as $permission) {
            $this->assertContains($permission, $admin);
        }
        foreach (['delete patient', 'approve cancellation', 'manage doctor fee', 'manage branch', 'manage satusehat', 'read audit log', 'delete medical record', 'revoke consent'] as $permission) {
            $this->assertNotContains($permission, $admin);
        }
    }

    public function test_every_granted_permission_exists_in_the_catalogue(): void
    {
        $catalogue = Access::allPermissions();

        foreach (Access::matrix() as $role => $grants) {
            foreach ($grants as $permission) {
                if ($permission === '*') {
                    continue;
                }
                $this->assertContains($permission, $catalogue, "$role memakai izin tak dikenal: $permission");
            }
        }
    }

    public function test_permission_catalogue_is_unique_and_includes_legacy_modules(): void
    {
        $catalogue = Access::allPermissions();

        $this->assertSame($catalogue, array_values(array_unique($catalogue)));
        // 44 izin warisan (11 modul × 4 aksi) + izin V2.
        $this->assertCount(44 + count(Access::EXTRA_PERMISSIONS), $catalogue);
        $this->assertContains('read patient', $catalogue);
        $this->assertContains('write odontogram', $catalogue);
    }
}
