<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\Billing\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function store(Request $request, $invoiceId)
    {
        $this->authorize('create transaction');
        $invoice = Invoice::findOrFail($invoiceId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => ['required', Rule::in(InvoicePayment::METHODS)],
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->payments->pay($invoice, $validated);
        if ($payment instanceof Exception) {
            return back()->withErrors('error', $payment->getMessage());
        }

        return back()->with('success', 'Pembayaran Rp'.number_format($payment->amount, 0, ',', '.').' tercatat.');
    }

    public function receipt($paymentId)
    {
        $this->authorize('read transaction');

        $payment = InvoicePayment::with(['invoice.patient', 'invoice.doctor.user', 'receiver:id,name', 'receipt'])
            ->findOrFail($paymentId);

        return view('pages.billing.receipts.show', ['payment' => $payment]);
    }
}
