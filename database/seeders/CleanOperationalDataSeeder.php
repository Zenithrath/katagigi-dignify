<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus SEMUA data operasional/dummy, sisakan akun, role/permission,
 * dan master (layanan, kategori, kode diagnosis, wilayah, KFA, penjamin,
 * template WhatsApp, cabang). Dipakai untuk mulai testing dari nol:
 *
 *   php artisan db:seed --class=CleanOperationalDataSeeder --force
 */
class CleanOperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan child → parent. Tabel yang tidak ada dilewati.
        $tables = [
            // Jejak & log
            'audit_logs',
            'satusehat_sync_logs',
            // Payroll & absensi
            'doctor_fees',
            'assistant_overtimes',
            'nurse_attendances',
            'holidays',
            // Keuangan
            'payment_receipts',
            'invoice_payments',
            'invoice_items',
            'invoices',
            'transaction_cancellation_requests',
            'transaction_services',
            'installments',
            'transactions',
            'expenses',
            // Inventori
            'stock_movements',
            'stock_batches',
            'inventory_items',
            // Klinis visit
            'medical_record_addendums',
            'medical_record_diagnoses',
            'medical_records',
            'vital_signs',
            'oral_health_indices',
            'medical_consent_records',
            'radiology_orders',
            'odontogram_findings',
            'visit_attachments',
            'prescription_items',
            'prescriptions',
            'treatment_plan_items',
            'treatment_plans',
            'visit_diagnoses',
            'visit_treatments',
            'anamneses',
            'examinations',
            'visits',
            // Layanan v1
            'appointments',
            'schedules',
            'patient_addresses',
            'patients',
            // WhatsApp
            'whatsapp_logs',
            'whatsapp_outbox',
        ];

        DB::transaction(function () use ($tables) {
            // Nonaktifkan pemeriksaan FK untuk urutan yang tidak terduga.
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $deleted = DB::table($table)->delete();
                    $this->command?->line("{$table}: {$deleted} baris dihapus");
                }
            }

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }
        });

        $this->command?->info('Data operasional dibersihkan. Akun, role, dan master dipertahankan.');
    }
}
