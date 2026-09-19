<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F3-T1: tagihan dari visit.
 */
class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function signedVisitWithTreatment(): Visit
    {
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'clinical_status' => \App\Models\Visit::STATUS_SIGNED,
        ]);
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $visit->treatments()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tooth_fdi' => '36',
            'procedure_code_id' => $icd9->id,
            'system' => 'ICD9',
            'code' => '23.2',
            'procedure' => 'Penambalan gigi',
            'quantity' => 2,
            'unit_price' => 150000,
        ]);

        return $visit->fresh();
    }

    public function test_create_invoice_from_visit_copies_treatments(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $visit = $this->signedVisitWithTreatment();

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();
        $invoice = $visit->invoices()->first();
        $this->assertNotNull($invoice);
        $this->assertMatchesRegularExpression('/^INV-\d{7}$/', $invoice->number);
        $this->assertEquals('DRAFT', $invoice->status);
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals(300000.0, $invoice->fresh()->total);
    }

    public function test_issue_requires_signed_visit_and_updates_billing(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $doctor = Doctor::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->user_id,
            'clinical_status' => \App\Models\Visit::STATUS_DONE,
        ]);
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $visit->treatments()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'procedure_code_id' => $icd9->id,
            'system' => 'ICD9',
            'code' => '23.2',
            'procedure' => 'Penambalan gigi',
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();
        $invoice = $visit->invoices()->first();

        // Visit DONE (belum SIGNED) → terbit ditolak (redirect + tetap DRAFT).
        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id), [
            'discount' => 10000,
        ])->assertRedirect();
        $this->assertEquals('DRAFT', $invoice->fresh()->status);

        $visit->update(['clinical_status' => \App\Models\Visit::STATUS_SIGNED]);
        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id), [
            'discount' => 10000,
        ])->assertRedirect();
        $invoice = $invoice->fresh();
        $this->assertEquals('ISSUED', $invoice->status);
        $this->assertEquals(90000.0, $invoice->total);
        $this->assertEquals('BILLED', $visit->fresh()->billing_status);
    }

    public function test_doctor_sees_only_own_invoices(): void
    {
        $doctor = Doctor::factory()->create();
        $doctorUser = $doctor->user;
        $doctorUser->assignRole('doctor');
        $other = Doctor::factory()->create();

        $mine = $this->signedVisitWithTreatment();
        $mine->update(['doctor_id' => $doctor->user_id]);
        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($admin)->post(route('visits.invoice.store', $mine->id));
        $mineInvoice = $mine->invoices()->first();

        $theirs = $this->signedVisitWithTreatment();
        $theirs->update(['doctor_id' => $other->user_id]);
        $this->actingAs($admin)->post(route('visits.invoice.store', $theirs->id));
        $theirsInvoice = $theirs->invoices()->first();

        // Konsumsi flash (menyebut nomor tagihan) agar assertDontSee murni isi tabel.
        $this->actingAs($admin)->get(route('invoices.index'))->assertOk();

        $this->actingAs($doctorUser)->get(route('invoices.index'))
            ->assertOk()
            ->assertSee($mineInvoice->number, false)
            ->assertDontSee($theirsInvoice->number, false);

        $this->actingAs($doctorUser)->get(route('invoices.show', $theirsInvoice->id))->assertForbidden();
    }

    public function test_void_and_item_rules(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $visit = $this->signedVisitWithTreatment();
        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id));
        $invoice = $visit->invoices()->first();

        $this->actingAs($admin)->post(route('invoices.items.store', $invoice->id), [
            'item_type' => 'OTHER',
            'description' => 'Biaya pendaftaran',
            'quantity' => 1,
            'unit_price' => 25000,
        ])->assertRedirect();
        $this->assertEquals(325000.0, $invoice->fresh()->total);

        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id))->assertRedirect();

        // Item tak bisa diubah setelah ISSUED; VOID tanpa pembayaran bisa.
        $count = $invoice->items()->count();
        $this->actingAs($admin)->post(route('invoices.items.store', $invoice->id), [
            'item_type' => 'OTHER',
            'description' => 'X',
        ])->assertRedirect();
        $this->assertEquals($count, $invoice->items()->count());
        $this->actingAs($admin)->post(route('invoices.void', $invoice->id))->assertRedirect();
        $this->assertEquals('VOID', $invoice->fresh()->status);
    }
}
