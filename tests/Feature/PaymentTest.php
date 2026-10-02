<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F3-T2: pembayaran bertahap + kwitansi + status otomatis.
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function issuedInvoice(int $total = 300000): object
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'clinical_status' => Visit::STATUS_SIGNED,
        ]);
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $visit->treatments()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'procedure_code_id' => $icd9->id,
            'system' => 'ICD9',
            'code' => '23.2',
            'procedure' => 'Penambalan gigi',
            'quantity' => 1,
            'unit_price' => $total,
        ]);

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();
        $invoice = $visit->invoices()->first();
        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id))->assertRedirect();

        return $invoice->fresh();
    }

    public function test_partial_then_full_payment_updates_status(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $invoice = $this->issuedInvoice();

        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 100000,
            'method' => 'CASH',
        ])->assertRedirect();
        $invoice = $invoice->fresh();
        $this->assertEquals('PARTIALLY_PAID', $invoice->status);
        $this->assertEquals(200000.0, $invoice->amountDue());
        $this->assertNotNull($invoice->payments()->first()->receipt);

        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 200000,
            'method' => 'QRIS',
        ])->assertRedirect();
        $invoice = $invoice->fresh();
        $this->assertEquals('PAID', $invoice->status);
        $this->assertEquals(0.0, $invoice->amountDue());
        $this->assertEquals(2, $invoice->payments()->count());
    }

    public function test_overpayment_and_draft_payment_rejected(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $invoice = $this->issuedInvoice(100000);

        $count = $invoice->payments()->count();
        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 150000,
            'method' => 'CASH',
        ])->assertRedirect();
        $this->assertEquals($count, $invoice->payments()->count());
        $this->assertEquals('ISSUED', $invoice->fresh()->status);

        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 100000,
            'method' => 'SALDO', // metode tak dikenal
        ])->assertSessionHasErrors('method');
    }

    public function test_receipt_printable(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $invoice = $this->issuedInvoice(50000);

        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 50000,
            'method' => 'TRANSFER',
        ])->assertRedirect();
        $payment = $invoice->payments()->first();

        $this->actingAs($admin)->get(route('payments.receipt', $payment->id))
            ->assertOk()
            ->assertSee('Kwitansi Pembayaran', false)
            ->assertSee($payment->receipt->number, false);
    }
}
