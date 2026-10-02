<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F3-T5: beban operasional + laporan keuangan.
 */
class ExpenseReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_record_expense_nurse_cannot(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $nurse = User::where('email', 'nurse@gmail.com')->first();

        $this->actingAs($admin)->post(route('expenses.store'), [
            'category' => 'Utilitas',
            'amount' => 750000,
            'spent_at' => date('Y-m-d'),
            'description' => 'Listrik',
        ])->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'category' => 'Utilitas',
            'amount' => 750000,
        ]);

        $this->actingAs($nurse)->post(route('expenses.store'), [
            'category' => 'Utilitas',
            'amount' => 1000,
            'spent_at' => date('Y-m-d'),
        ])->assertForbidden();

        $this->actingAs($nurse)->get(route('expenses.index'))->assertOk();
    }

    public function test_finance_report_reflects_invoices_payments_expenses(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
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
            'unit_price' => 400000,
        ]);

        $this->actingAs($admin)->post(route('visits.invoice.store', $visit->id))->assertRedirect();
        $invoice = $visit->invoices()->first();
        $this->actingAs($admin)->post(route('invoices.issue', $invoice->id))->assertRedirect();
        $this->actingAs($admin)->post(route('invoices.payments.store', $invoice->id), [
            'amount' => 150000,
            'method' => 'CASH',
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('expenses.store'), [
            'category' => 'Bahan',
            'amount' => 50000,
            'spent_at' => date('Y-m-d'),
        ])->assertRedirect();

        $this->actingAs($manajemen)->get(route('finance-report.index'))
            ->assertOk()
            ->assertSee('Rp400.000', false)   // ditagihkan
            ->assertSee('Rp150.000', false)   // terkumpul
            ->assertSee('Rp250.000', false)   // piutang
            ->assertSee('Rp50.000', false)    // beban
            ->assertSee('Rp100.000', false);  // bersih
    }
}
