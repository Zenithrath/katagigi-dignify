<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Visit;
use App\Services\Billing\InvoiceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoices) {}

    public function index(Request $request)
    {
        $this->authorize('read transaction');

        $query = Invoice::with(['patient:id,code,name', 'doctor.user:id,name'])
            ->orderBy('created_at', 'desc');

        // Dokter: batasi tagihan kasusnya (PRD §4 VIEW terbatas).
        if (Auth::user()->hasRole('doctor')) {
            $query->where('doctor_id', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('pages.billing.invoices.index', [
            'invoices' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show($id)
    {
        $this->authorize('read transaction');

        $invoice = Invoice::with(['patient', 'doctor.user', 'visit', 'items', 'payments.receipt', 'payments.receiver:id,name'])
            ->findOrFail($id);

        if (Auth::user()->hasRole('doctor')) {
            abort_unless($invoice->doctor_id === Auth::id(), 403);
        }

        return view('pages.billing.invoices.show', ['invoice' => $invoice]);
    }

    public function fromVisit($visitId)
    {
        $this->authorize('create transaction');
        $visit = Visit::with('treatments')->findOrFail($visitId);

        $invoice = $this->invoices->createFromVisit($visit);
        if ($invoice instanceof Exception) {
            return back()->withErrors('error', 'Gagal membuat tagihan.');
        }

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Tagihan '.$invoice->number.' dibuat dari visit.');
    }

    public function storeItem(Request $request, $id)
    {
        $this->authorize('update transaction');
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'item_type' => ['required', Rule::in([InvoiceItem::TYPE_MEDICINE, InvoiceItem::TYPE_OTHER])],
            'description' => 'required|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $item = $this->invoices->addItem($invoice, $validated);
        if ($item instanceof Exception) {
            return back()->withErrors('error', $item->getMessage());
        }

        return back()->with('success', 'Item ditambahkan.');
    }

    public function destroyItem($id, $itemId)
    {
        $this->authorize('update transaction');
        $invoice = Invoice::findOrFail($id);

        $status = $this->invoices->removeItem($invoice, $itemId);
        if ($status instanceof Exception) {
            return back()->withErrors('error', $status->getMessage());
        }

        return back()->with('success', 'Item dihapus.');
    }

    public function issue(Request $request, $id)
    {
        $this->authorize('update transaction');
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $result = $this->invoices->issue($invoice, $validated);
        if ($result instanceof Exception) {
            return back()->withErrors('error', $result->getMessage());
        }

        return back()->with('success', 'Tagihan '.$result->number.' diterbitkan.');
    }

    public function void($id)
    {
        $this->authorize('update transaction');
        $invoice = Invoice::findOrFail($id);

        $result = $this->invoices->void($invoice);
        if ($result instanceof Exception) {
            return back()->withErrors('error', $result->getMessage());
        }

        return back()->with('success', 'Tagihan dibatalkan (VOID).');
    }
}
