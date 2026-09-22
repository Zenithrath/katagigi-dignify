<?php

namespace Database\Seeders;

use App\Models\Anamnesis;
use App\Models\Doctor;
use App\Models\Examination;
use App\Models\MedicalRecord;
use App\Models\Nurse;
use App\Models\OdontogramFinding;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy siap review yang SALING TERHUBUNG antar role:
 * - admin & nurse membuat pasien, appointment, nota transaksi (kasir).
 * - doctor mengerjakan visit (anamnesis → SOAP → odontogram → ICD-10/ICD-9 → resep).
 * - manajemen memutuskan pembatalan nota (alur usul-approve).
 * - setiap pasien punya "cerita klinis" lengkap: visit → invoice → payment →
 *   kwitansi → fee dokter, dan sebagian jadi rekam medis lama (medical_records).
 * - pasien dibuat dengan kelengkapan beragam (ada yang NIK/consent lengkap,
 *   ada yang belum) untuk mendemokan tab Lengkap vs Belum Lengkap.
 * Angka & isi medisnya ilustratif, bukan acuan klinis.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = DB::table('branches')->where('code', 'CBG-01')->value('id')
            ?? DB::table('branches')->value('id');

        $icd10 = DB::table('diagnosis_codes')->where('system', 'ICD10')->orderBy('code')->get();
        $icd9 = DB::table('diagnosis_codes')->where('system', 'ICD9')->orderBy('code')->get();

        if ($icd10->isEmpty()) {
            $this->command?->warn('Master kode diagnosis kosong — jalankan DiagnosisCodeSeeder dulu.');

            return;
        }

        // ── Akun demo per role (pembuat data tercatat nyata) ──
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $nurses = Nurse::all();

        $patients = Patient::all();
        if ($patients->isEmpty()) {
            $this->command?->warn('Tidak ada pasien — jalankan seed pasien dulu.');

            return;
        }

        // Safety net: kalau ada pasien tanpa NIK/consent (set incomplete),
        // lengkapi salinan pertama agar visit SIGNED bisa disinkronkan.
        if ($patients->every(fn ($p) => empty($p->nik) || ! $p->satusehat_consent)) {
            $first = $patients->first();
            $first->update([
                'nik' => fake()->numerify('################'),
                'satusehat_consent' => true,
            ]);
        }

        $doctors = Doctor::all();
        if ($doctors->isEmpty()) {
            $this->command?->warn('Tidak ada dokter — jalankan seed pasien/dokter dulu.');

            return;
        }

        // ── 1. Visit klinis: pasien pertama dengan status beragam (riwayat + antrian) ──
        $storyPatients = $patients->take(4);
        $statuses = [
            Visit::STATUS_SIGNED,
            Visit::STATUS_DONE,
            Visit::STATUS_IN_TREATMENT,
            Visit::STATUS_WAITING,
        ];

        $signedVisitId = null;
        foreach ($storyPatients as $i => $patient) {
            $doctor = $doctors[$i % $doctors->count()];
            $status = $statuses[$i % count($statuses)];

            $visit = Visit::create([
                'id' => (string) Str::uuid(),
                'visit_number' => 'VST-'.date('y').str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'branch_id' => $branchId,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->user_id,
                'visit_date' => $status === Visit::STATUS_SIGNED
                    ? date('Y-m-d', strtotime('-'.($i + 2).' days'))
                    : date('Y-m-d'),
                'clinical_status' => $status,
                'billing_status' => $status === Visit::STATUS_SIGNED ? Visit::BILLING_BILLED : Visit::BILLING_UNBILLED,
                'notes' => 'Data demo — jalur klinis lengkap (ilustrasi).',
                'signed_at' => $status === Visit::STATUS_SIGNED ? now()->subDays($i + 2) : null,
                'signed_by' => $status === Visit::STATUS_SIGNED ? $doctor->user_id : null,
            ]);

            // Anamnesis + pemeriksaan untuk semua visit demo.
            Anamnesis::create([
                'id' => (string) Str::uuid(),
                'visit_id' => $visit->id,
                'chief_complaint' => fake()->randomElement([
                    'Gigi berlubang sudah seminggu, nyeri saat makan manis.',
                    'Gusi berdarah saat menyikat gigi.',
                    'Gigi bungsu ngilu, mau dicek.',
                    'Kontrol tambalan gigi bulan lalu.',
                ]),
                'present_illness' => 'Nyeri berkurang timbul sejak ±1 minggu, tidak ada bengkak.',
                'past_medical_history' => fake()->randomElement(['Tidak ada', 'Hipertensi terkontrol', 'Maag']),
                'dental_history' => 'Terakhir scaling >1 tahun lalu; pernah tambalan gigi.',
                'allergies' => fake()->randomElement(['Tidak ada', 'Penisilin']),
                'medications' => 'Tidak ada',
            ]);

            Examination::create([
                'id' => (string) Str::uuid(),
                'visit_id' => $visit->id,
                'subjective' => 'Nyeri gigi regional, tidak menjalar.',
                'objective' => 'Higiene mulut sedang, tidak ada bengkak fasial.',
                'assessment' => 'Diagnosis kerja sesuai temuan odontogram di bawah.',
                'plan' => 'Konservasi/perawatan sesuai diagnosis; edukasi higien mulut.',
                'blood_pressure' => fake()->numerify('1##/##'),
            ]);

            // Odontogram: temuan ilustratif per pasien.
            $findings = [
                [['fdi' => '16', 'surface' => 'occlusal', 'condition' => 'caries', 'material' => null]],
                [['fdi' => '26', 'surface' => 'distal', 'condition' => 'caries', 'material' => null],
                    ['fdi' => '36', 'surface' => 'whole', 'condition' => 'filled', 'material' => 'Komposit']],
                [['fdi' => '38', 'surface' => 'whole', 'condition' => 'root', 'material' => null]],
                [['fdi' => '46', 'surface' => 'whole', 'condition' => 'crown', 'material' => 'Zirkonia']],
            ];
            foreach ($findings[$i] as $f) {
                OdontogramFinding::create([...$f, 'id' => (string) Str::uuid(), 'visit_id' => $visit->id]);
            }

            // Diagnosis ICD-10: utama + sekunder.
            $primary = $icd10[$i % $icd10->count()];
            $secondary = $icd10[($i + 3) % $icd10->count()];
            foreach ([[$primary, true], [$secondary, false]] as [$code, $isPrimary]) {
                DB::table('visit_diagnoses')->insert([
                    'id' => (string) Str::uuid(),
                    'visit_id' => $visit->id,
                    'tooth_fdi' => $findings[$i][0]['fdi'],
                    'diagnosis_code_id' => $code->id,
                    'system' => $code->system,
                    'code' => $code->code,
                    'display' => $code->display_id,
                    'is_primary' => $isPrimary,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tindakan ICD-9 dengan harga + gigi terkait.
            $treatment = $icd9[$i % $icd9->count()];
            DB::table('visit_treatments')->insert([
                'id' => (string) Str::uuid(),
                'visit_id' => $visit->id,
                'tooth_fdi' => $findings[$i][0]['fdi'],
                'procedure_code_id' => $treatment->id,
                'system' => $treatment->system,
                'code' => $treatment->code,
                'procedure' => $treatment->display_id,
                'quantity' => 1,
                'unit_price' => fake()->randomElement([150000, 300000, 450000, 600000]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Invoice + pembayaran + kwitansi + fee dokter untuk visit selesai
            // (DONE/SIGNED) — dibayar oleh ADMIN (kasir), bukan dokter.
            if (in_array($status, [Visit::STATUS_DONE, Visit::STATUS_SIGNED], true)) {
                $treatmentRow = DB::table('visit_treatments')->where('visit_id', $visit->id)->first();
                $unitPrice = (float) $treatmentRow->unit_price;
                $subtotal = $unitPrice;
                $tax = 0.0;
                $total = $subtotal + $tax;
                $now = now();
                $cashier = $admin ?? $nurse;

                $invoiceId = (string) Str::uuid();
                DB::table('invoices')->insert([
                    'id' => $invoiceId,
                    'number' => 'INV-'.date('y').str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                    'branch_id' => $branchId,
                    'patient_id' => $patient->id,
                    'visit_id' => $visit->id,
                    'appointment_id' => null,
                    'doctor_id' => $doctor->user_id,
                    'status' => $status === Visit::STATUS_SIGNED ? 'PAID' : 'ISSUED',
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'tax' => $tax,
                    'total' => $total,
                    'issued_by' => $cashier?->id,
                    'issued_at' => $now,
                    'notes' => 'Demo',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('invoice_items')->insert([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $invoiceId,
                    'item_type' => 'TREATMENT',
                    'tooth_fdi' => $treatmentRow->tooth_fdi,
                    'description' => $treatmentRow->procedure,
                    'reference_code' => $treatmentRow->code,
                    'quantity' => 1,
                    'unit_price' => $unitPrice,
                    'amount' => $unitPrice,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                if ($status === Visit::STATUS_SIGNED) {
                    $paymentId = (string) Str::uuid();
                    DB::table('invoice_payments')->insert([
                        'id' => $paymentId,
                        'invoice_id' => $invoiceId,
                        'amount' => $total,
                        'method' => 'CASH',
                        'notes' => 'Demo',
                        'paid_at' => $now,
                        'received_by' => $cashier?->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Kwitansi (satu per pembayaran) + fee dokter (posting saat lunas).
                    $feeAmount = round($total * 0.30);
                    DB::table('payment_receipts')->insert([
                        'id' => (string) Str::uuid(),
                        'payment_id' => $paymentId,
                        'number' => 'RCP-'.date('y').str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    DB::table('doctor_fees')->insert([
                        'id' => (string) Str::uuid(),
                        'doctor_id' => $doctor->user_id,
                        'invoice_id' => $invoiceId,
                        'base_amount' => $total,
                        'percentage' => 30,
                        'fee_amount' => $feeAmount,
                        'status' => 'UNPAID',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if ($status === Visit::STATUS_SIGNED && $signedVisitId === null) {
                $signedVisitId = $visit->id;
            }
        }

        // ── 2. Rekam medis lama: mirror dari visit SIGNED (jalur sync V2) ──
        if ($signedVisitId) {
            $visit = Visit::with(['patient', 'doctor.user'])->find($signedVisitId);
            $patient = $visit->patient;
            $doctorUser = $visit->doctor->user;
            $now = now();

            $recordId = (string) Str::uuid();
            $unitPrice = (float) (DB::table('visit_treatments')->where('visit_id', $visit->id)->value('unit_price') ?? 0);

            MedicalRecord::create([
                'id' => $recordId,
                'patient_id' => $patient->id,
                'patient_code' => $patient->code,
                'patient_name' => $patient->name,
                'patient_phone' => $patient->phone,
                'patient_address' => 'Alamat demo',
                'doctor_id' => $doctorUser->id,
                'doctor_name' => $doctorUser->name,
                'doctor_nipp' => (string) ($visit->doctor->nipp ?? ''),
                'doctor_niptk' => (string) ($visit->doctor->niptk ?? ''),
                'appointment_id' => $visit->appointment_id ?? (string) Str::uuid(),
                'appointment_date' => $visit->visit_date->format('Y-m-d'),
                'time_start' => '09:00:00',
                'time_end' => '10:00:00',
                'services' => json_encode([[
                    'id' => (string) Str::uuid(),
                    'price' => $unitPrice,
                    'quantity' => 1,
                    'subtotal' => $unitPrice,
                    'discount' => 0,
                    'code' => 'SRV-DEMO',
                    'name' => 'Tindakan demo',
                    'category' => 'Umum',
                ]]),
                'anamnesis' => 'Gigi berlubang, nyeri saat makan manis (demo).',
                'diagnosis' => 'Karies dentin (demo).',
                'therapy' => 'Restorasi komposit (demo).',
                'prescription' => 'Analgetik bila perlu (demo).',
                'checkup_result' => 'Perawatan selesai, kontrol 6 bulan (demo).',
                'next_schedule' => date('Y-m-d', strtotime('+6 months')),
                'price' => $unitPrice,
                'discount' => 0,
                'billing' => $unitPrice,
                'promat' => 'NO PROMAT',
                'blood_pressure' => '120/80',
                'cooperativity' => 'COOPERATIVE',
                'image_before' => null,
                'image_after' => null,
            ]);

            // Kode resmi ikut mirror (ICD-10 + ICD-9) — sesuai implementasi.
            $rows = [];
            foreach (DB::table('visit_diagnoses')->where('visit_id', $visit->id)->get() as $d) {
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'medical_record_id' => $recordId,
                    'diagnosis_code_id' => $d->diagnosis_code_id,
                    'system' => $d->system,
                    'code' => $d->code,
                    'display' => $d->display,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($rows !== []) {
                DB::table('medical_record_diagnoses')->insert($rows);
            }
        }

        // ── 3. Nota transaksi lama (kasir admin/nurse) + satu alur pembatalan ──
        // Nota terhubung ke appointment pasien yang sama sehingga riwayat lintas
        // modul (appointment → RM → nota) konsisten per pasien.
        $appointments = DB::table('appointments')->orderBy('date')->get();
        // FK transaction_services.service_id → services: pakai layanan asli.
        $demoServiceId = DB::table('services')->where('is_active', true)->value('id');
        $trxIndex = 0;
        foreach ($appointments as $appointment) {
            if ($trxIndex >= 6) {
                break;
            }

            $patient = $patients->firstWhere('id', $appointment->patient_id);
            $doctor = $doctors->firstWhere('user_id', $appointment->doctor_id);
            $cashier = $trxIndex % 2 === 0 ? $admin : $nurse;
            $assistant = $nurses->firstWhere('user_id', $cashier?->id);

            $price = fake()->randomElement([150000, 300000, 450000, 600000]);
            $discount = $trxIndex % 3 === 0 ? 50000 : 0;
            $now = now();

            $transactionId = (string) Str::uuid();
            DB::table('transactions')->insert([
                'id' => $transactionId,
                'sequence' => (int) (date('y').str_pad((string) (100 + $trxIndex), 5, '0', STR_PAD_LEFT)),
                'has_installment' => null,
                'down_payment_transaction_id' => null,
                'has_down_payment' => false,
                'is_endorsed' => false,
                'current_payment' => $price - $discount,
                'patient_id' => $appointment->patient_id,
                'patient_code' => $appointment->patient_code,
                'patient_name' => $appointment->patient_name,
                'patient_phone' => $appointment->patient_phone,
                'doctor_id' => $appointment->doctor_id,
                'doctor_nipp' => $appointment->doctor_nipp,
                'doctor_name' => $appointment->doctor_name,
                'nurse_id' => $assistant?->user_id,
                'nurse_nipp' => $assistant?->nipp,
                'nurse_name' => $assistant ? $cashier?->name : null,
                'appointment_id' => $appointment->id,
                'appointment_datetime' => $appointment->date.' '.$appointment->time_start,
                'next_schedule' => date('Y-m-d', strtotime('+1 month')),
                'services' => json_encode([[
                    'id' => (string) Str::uuid(),
                    'class' => 'service',
                    'price' => $price,
                    'quantity' => 1,
                    'subtotal' => $price,
                    'discount' => $discount,
                    'code' => 'SRV-DEMO',
                    'name' => 'Tindakan demo',
                    'category' => 'Umum',
                ]]),
                'price' => $price,
                'discount' => $discount,
                'billing' => $price - $discount,
                'payment_method' => fake()->randomElement(['CASH', 'TRANSFER', 'QRIS']),
                'canceled_at' => null,
                'cancel_reason' => null,
                'voucher_code' => null,
                'referenced_installment_id' => null,
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('transaction_services')->insert([
                'id' => (string) Str::uuid(),
                'service_id' => $demoServiceId,
                'service_name' => 'Tindakan demo',
                'transaction_id' => $transactionId,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Satu nota diusulkan batal oleh ADMIN lalu DISSETUJUI manajemen —
            // mendemokan alur usul-kunci-approve lintas role.
            if ($trxIndex === 0 && $admin && $manajemen) {
                $proposalId = (string) Str::uuid();
                DB::table('transaction_cancellation_requests')->insert([
                    'id' => $proposalId,
                    'transaction_id' => $transactionId,
                    'proposed_by' => $admin->id,
                    'reason' => 'Salah input layanan (demo)',
                    'status' => 'APPROVED',
                    'decided_by' => $manajemen->id,
                    'decided_at' => $now,
                    'decision_note' => 'Setuju, data duplikat (demo)',
                    'created_at' => $now->copy()->subMinutes(30),
                    'updated_at' => $now,
                ]);
                DB::table('transactions')->where('id', $transactionId)->update([
                    'canceled_at' => $now,
                    'cancel_reason' => 'Salah input layanan (demo)',
                    'is_locked' => false,
                ]);
            }

            $trxIndex++;
        }

        // ── 4. Beban operasional oleh admin (laporan keuangan manajemen) ──
        if ($admin) {
            $expenseTypes = [
                ['Bahan habis pakai', 750000],
                ['Listrik & air', 1200000],
                ['Gaji staf partial', 8000000],
            ];
            foreach ($expenseTypes as $ei => [$category, $amount]) {
                DB::table('expenses')->insert([
                    'id' => (string) Str::uuid(),
                    'branch_id' => $branchId,
                    'category' => $category,
                    'description' => 'Demo beban '.($ei + 1),
                    'amount' => $amount,
                    'spent_at' => date('Y-m-'.str_pad((string) ($ei * 5 + 3), 2, '0', STR_PAD_LEFT)),
                    'created_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ── 5. Stok inventory oleh admin + 1 item di bawah minimum ──
        $inventoryItems = [
            ['INV-001', 'Sarung tangan latex', 'box', 10, 25, 85000],
            ['INV-002', 'Komposit nano hybrid', 'syringe', 5, 3, 450000],
            ['INV-003', 'Benang gigi sample', 'pcs', 50, 12, 5000],
        ];
        foreach ($inventoryItems as $ci => [$code, $name, $unit, $minStock, $qty, $buyPrice]) {
            $itemId = (string) Str::uuid();
            DB::table('inventory_items')->insert([
                'id' => $itemId,
                'code' => $code,
                'name' => $name,
                'unit' => $unit,
                'min_stock' => $minStock,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('stock_batches')->insert([
                'id' => (string) Str::uuid(),
                'inventory_item_id' => $itemId,
                'batch_no' => 'BATCH-'.($ci + 1),
                'expiry_date' => date('Y-m-d', strtotime('+'.($ci * 6 + 8).' months')),
                'quantity' => $qty,
                'buy_price' => $buyPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('stock_movements')->insert([
                'id' => (string) Str::uuid(),
                'inventory_item_id' => $itemId,
                'batch_id' => DB::table('stock_batches')->where('inventory_item_id', $itemId)->value('id'),
                'type' => 'IN',
                'quantity' => $qty,
                'reference' => 'Pembelian awal (demo)',
                'notes' => null,
                'created_by' => $admin?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command?->info('Demo data lintas role siap: visit, invoice, payment, receipt, fee, nota, pembatalan, beban, inventory.');
    }
}
