<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\DoctorFee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('read turnover');

        $month = $request->input('month', date('Y-m'));
        [$year, $mon] = array_pad(explode('-', $month), 2, null);
        $year = (int) ($year ?: date('Y'));
        $mon = (int) ($mon ?: date('m'));

        $invoices = Invoice::whereYear('created_at', $year)->whereMonth('created_at', $mon)
            ->where('status', '!=', Invoice::STATUS_VOID);
        $invoiced = (clone $invoices)->sum('total');
        $collected = InvoicePayment::whereHas('invoice', fn ($q) => $q->whereYear('invoices.created_at', $year)->whereMonth('invoices.created_at', $mon))->sum('amount');
        $outstanding = Invoice::whereYear('created_at', $year)->whereMonth('created_at', $mon)
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIALLY_PAID])
            ->get()->sum(fn ($inv) => $inv->amountDue());
        $expenses = Expense::whereYear('spent_at', $year)->whereMonth('spent_at', $mon)->sum('amount');
        $feesUnpaid = DoctorFee::whereYear('created_at', $year)->whereMonth('created_at', $mon)
            ->where('status', DoctorFee::STATUS_UNPAID)->sum('fee_amount');

        $perDoctor = Invoice::with('doctor.user:id,name')
            ->selectRaw('doctor_id, count(*) as invoice_count, sum(total) as total')
            ->whereYear('created_at', $year)->whereMonth('created_at', $mon)
            ->where('status', '!=', Invoice::STATUS_VOID)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->get();

        $perMethod = InvoicePayment::selectRaw('method, sum(amount) as total, count(*) as count')
            ->whereYear('paid_at', $year)->whereMonth('paid_at', $mon)
            ->groupBy('method')
            ->get();

        return view('pages.report.finance.index', [
            'month' => sprintf('%04d-%02d', $year, $mon),
            'invoiced' => $invoiced,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'expenses' => $expenses,
            'feesUnpaid' => $feesUnpaid,
            'net' => $collected - $expenses,
            'perDoctor' => $perDoctor,
            'perMethod' => $perMethod,
        ]);
    }
}
