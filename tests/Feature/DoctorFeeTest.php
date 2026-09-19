<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F3-T3: posting fee saat lunas + pencairan + aturan per dokter.
 */
class DoctorFeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function paidInvoiceWithItems(array $items): object
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'clinical_status' => Visit::STATUS_SIGNED,
        ]);
        foreach ($items as $code => $amount) {
            DB::table('diagnosis_codes')->updateOrInsert(
                ['system' => 'ICD9', 'code' => $code],
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'display_id' => 'Tes '.$code,
                    'display_en' => 'Test '.$code,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $codeRow = DB::table('diagnosis_codes')->where('code', $code)->first();
            $visit->treatments()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'procedure_code_id' => $codeRow->id,
                'system' => 'ICD9',
                'code' => $code,
                'procedure' => 'Tes '.$code,
                'quantity' => 1,
                'unit_price' => $amount,
            ]);
        }

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();
        $invoice = $visit->invoices()->first();
        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id))->assertRedirect();
        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => $invoice->fresh()->total,
            'method' => 'CASH',
        ])->assertRedirect();

        return $invoice->fresh();
    }

    public function test_fee_posted_on_paid_with_xray_split(): void
    {
        // Aturan khusus dokter: 40% tindakan, 10% rontgen.
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $invoice = $this->paidInvoiceWithItems(['23.2' => 200000, '87.11' => 100000]);
        $doctorId = $invoice->doctor_id;

        // Posting awal memakai tarif default (30%/20%).
        $fee = $invoice->fees()->first();
        $this->assertNotNull($fee);
        $this->assertEquals(200000 * 0.30 + 100000 * 0.20, $fee->fee_amount);

        // Pembayaran kedua tak membuat posting ganda — uji via invoice lain + aturan khusus.
        DB::table('doctor_fee_rules')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'doctor_id' => $doctorId,
            'percentage' => 40,
            'xray_percentage' => 10,
            'shift_allowance' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($manajemen)->get(route('doctor-fees.index'))
            ->assertOk()
            ->assertSee($invoice->number, false);

        $this->actingAs($manajemen)->post(route('doctor-fees.pay', $fee->id))->assertRedirect();
        $this->assertEquals('PAID', $fee->fresh()->status);
    }

    public function test_payout_restricted_to_manajemen(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $invoice = $this->paidInvoiceWithItems(['23.2' => 100000]);
        $fee = $invoice->fees()->first();

        $this->actingAs($admin)->post(route('doctor-fees.pay', $fee->id))->assertForbidden();
        $this->assertEquals('UNPAID', $fee->fresh()->status);
    }
}
