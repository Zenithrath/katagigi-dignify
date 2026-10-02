<?php

namespace App\Helpers;

/**
 * Matriks peran & izin — satu sumber kebenaran untuk `RolesAndPermissionsSeeder`
 * dan pengujian. Semua pemeriksaan otorisasi di controller/view memakai nama izin
 * dari daftar ini, bukan daftar role yang ditulis ulang di banyak tempat.
 *
 * Hierarki (tertinggi → terendah):
 *   manajemen (Owner)      seluruh izin; satu-satunya yang boleh menghapus master,
 *                          menyetujui pembatalan nota, mencairkan jasa medis,
 *                          mengelola cabang/SATUSEHAT/WhatsApp, dan membaca jejak audit.
 *   admin (Front Office)   operasional: pasien (tanpa hapus), jadwal, appointment,
 *                          kasir/tagihan, inventory, beban, absensi & payroll asisten,
 *                          usul pembatalan nota, pencatatan consent.
 *                          TIDAK menulis isi rekam medis.
 *   doctor                 klinis: menulis seluruh isi rekam medis miliknya + sign/final;
 *                          baca pasien/jadwal/transaksi (baca saja); tanpa master & approval.
 *   nurse (Asisten)        pendukung: baca pasien/RM, kelola antrian, draf anamnesis,
 *                          tanda vital, OHI-S, lampiran, pencatatan consent, absensi sendiri;
 *                          kasir bila ditugaskan. TIDAK: odontogram, SOAP, diagnosis,
 *                          tindakan, resep, radiologi, sign/final.
 */
class Access
{
    /** Role valid, urut sesuai hierarki. */
    public const HIERARCHY = ['manajemen', 'admin', 'doctor', 'nurse'];

    /** Modul warisan v1: 11 modul × read/create/update/delete. */
    private const LEGACY_MODULES = [
        'schedule', 'appointment', 'service', 'category',
        'admin', 'doctor', 'nurse', 'patient',
        'medical record', 'transaction', 'turnover',
    ];

    /** Izin V2 (di luar 44 izin warisan). */
    public const EXTRA_PERMISSIONS = [
        // Alur pembatalan nota (usul-kunci-approve)
        'request cancellation', 'approve cancellation',
        // Kamus diagnosis
        'read diagnosis code', 'manage diagnosis code',
        // Kunjungan & kunci-final
        'read visit', 'create visit', 'update visit', 'sign visit',
        // Penulisan isi rekam medis (per modul — dipakai controller & view)
        'write anamnesis', 'write examination', 'write odontogram', 'write diagnosis',
        'write treatment', 'write treatment plan', 'write prescription',
        'write vital sign', 'write oral health index', 'write radiology',
        'record consent', 'revoke consent', 'upload visit attachment',
        // Keuangan & operasional
        'manage doctor fee', 'read inventory', 'manage inventory',
        'read expense', 'manage expense',
        'manage holiday', 'manage attendance', 'record own attendance', 'read assistant payroll',
        // Pengaturan tingkat manajemen
        'manage branch', 'manage satusehat', 'manage whatsapp', 'read audit log',
    ];

    /** Semua nama izin (warisan + V2). */
    public static function allPermissions(): array
    {
        $permissions = [];
        foreach (self::LEGACY_MODULES as $module) {
            foreach (['read', 'create', 'update', 'delete'] as $action) {
                $permissions[] = "$action $module";
            }
        }

        return array_values(array_unique([...$permissions, ...self::EXTRA_PERMISSIONS]));
    }

    /**
     * Peta role → izin. `['*']` = seluruh izin.
     *
     * @return array<string, list<string>>
     */
    public static function matrix(): array
    {
        return [
            'manajemen' => ['*'],

            'admin' => [
                'read schedule', 'create schedule', 'update schedule',
                'read appointment', 'create appointment', 'update appointment',
                'read patient', 'create patient', 'update patient',
                'read medical record',
                'read transaction', 'create transaction', 'update transaction', 'read turnover',
                'read diagnosis code', 'request cancellation',
                'read visit', 'create visit', 'update visit',
                'read inventory', 'manage inventory',
                'read expense', 'manage expense',
                'record consent',
                'manage holiday', 'manage attendance', 'read assistant payroll',
                'manage whatsapp',
            ],

            'doctor' => [
                'read patient',
                'read medical record', 'create medical record', 'update medical record', 'delete medical record',
                'read appointment', 'read schedule',
                'read transaction', 'read turnover',
                'read diagnosis code',
                'read visit', 'create visit', 'update visit', 'sign visit',
                'write anamnesis', 'write examination', 'write odontogram', 'write diagnosis',
                'write treatment', 'write treatment plan', 'write prescription',
                'write vital sign', 'write oral health index', 'write radiology',
                'record consent', 'revoke consent', 'upload visit attachment',
                'read inventory', 'read expense',
            ],

            'nurse' => [
                'read patient', 'create patient', 'update patient',
                'read medical record',
                'read appointment', 'create appointment', 'update appointment', 'delete appointment',
                'read schedule',
                'read transaction', 'create transaction', 'update transaction', 'read turnover',
                'read diagnosis code',
                'read visit', 'create visit', 'update visit',
                'write anamnesis', 'write vital sign', 'write oral health index',
                'record consent', 'upload visit attachment',
                'record own attendance',
                'read inventory', 'read expense',
            ],
        ];
    }
}
