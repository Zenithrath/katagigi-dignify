<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F3-T6 bug sweep: semua halaman baru ter-render per role + idempotensi tagihan.
 */
class OperationalSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manajemen_can_open_all_operational_pages(): void
    {
        $user = User::where('email', 'manajemen@gmail.com')->first();

        foreach (['invoices.index', 'doctor-fees.index', 'inventory.index', 'expenses.index', 'finance-report.index', 'workspace.index', 'calendar.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_role_scoped_operational_pages(): void
    {
        $doctor = Doctor::factory()->create();
        $doctorUser = $doctor->user;
        $doctorUser->assignRole('doctor');
        $nurse = User::where('email', 'nurse@gmail.com')->first();

        // Dokter: fee + laporan + inventory + beban (baca).
        foreach (['doctor-fees.index', 'finance-report.index', 'inventory.index', 'expenses.index'] as $route) {
            $this->actingAs($doctorUser)->get(route($route))->assertOk();
        }

        // Nurse: inventory + beban baca; fee + laporan ikut read turnover (boleh).
        $this->actingAs($nurse)->get(route('inventory.index'))->assertOk();
        $this->actingAs($nurse)->get(route('expenses.index'))->assertOk();

        // Nurse tak boleh kelola inventory/expense/fee.
        $this->actingAs($nurse)->get(route('inventory.create'))->assertForbidden();
        $this->actingAs($nurse)->post(route('doctor-fees.pay', (string) \Illuminate\Support\Str::uuid()))->assertForbidden();
    }

    public function test_invoice_creation_is_idempotent_per_visit(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $visit = Visit::factory()->create(['clinical_status' => Visit::STATUS_SIGNED]);

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();
        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();

        // Klik ganda tidak menggandakan DRAFT.
        $this->assertEquals(1, $visit->invoices()->count());
    }

    public function test_invoice_show_and_receipt_render(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $visit = Visit::factory()->create(['clinical_status' => Visit::STATUS_SIGNED]);
        $icd9 = DB::table('diagnosis_codes')->where('code', '23.2')->first();
        $visit->treatments()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'procedure_code_id' => $icd9->id,
            'system' => 'ICD9',
            'code' => '23.2',
            'procedure' => 'Penambalan gigi',
            'quantity' => 1,
            'unit_price' => 120000,
        ]);

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id));
        $invoice = $visit->invoices()->first();
        $this->actingAs($admin)->get(route('invoices.index'))->assertOk();
        $this->actingAs($admin)->get(route('invoices.show', $invoice->id))
            ->assertOk()
            ->assertSee($invoice->number, false);

        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id))->assertRedirect();
        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 120000,
            'method' => 'CASH',
        ])->assertRedirect();
        $payment = $invoice->payments()->first();
        $this->actingAs($admin)->get(route('payments.receipt', $payment->id))->assertOk();
    }
}
