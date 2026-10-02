<?php

namespace App\Http\Controllers;

use App\Helpers\Audit;
use App\Models\Transaction;
use App\Models\TransactionCancellationRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Alur usul-kunci-approve pembatalan nota:
 * - admin operasional: propose (nota terkunci)
 * - manajemen: approve (eksekusi batal) / reject (buka kunci)
 */
class CancellationController extends Controller
{
    public function __construct(private TransactionService $transactions) {}

    public function propose(Request $request, string $id)
    {
        $this->authorize('request cancellation');
        $validated = $request->validate(['cancel_reason' => ['required', 'string', 'max:255']]);

        $transaction = Transaction::findOrFail($id);
        abort_if($transaction->canceled_at, 422, 'Nota sudah dibatalkan.');
        abort_if($transaction->is_locked, 422, 'Nota terkunci menunggu keputusan.');
        abort_if(
            $transaction->cancellationRequests()->where('status', 'PROPOSED')->exists(),
            422,
            'Sudah ada usulan yang menunggu.'
        );

        DB::transaction(function () use ($transaction, $validated) {
            $transaction->cancellationRequests()->create([
                'id' => (string) Str::uuid(),
                'proposed_by' => auth()->id(),
                'reason' => $validated['cancel_reason'],
                'status' => 'PROPOSED',
            ]);
            $transaction->update(['is_locked' => true]);
        });

        Audit::log('propose-cancellation', 'transaction', $transaction->id, null, ['is_locked' => true], $validated['cancel_reason']);

        return back()->with('success', 'Usulan pembatalan dikirim ke manajemen.');
    }

    public function approve(Request $request, string $id)
    {
        $this->authorize('approve cancellation');
        $validated = $request->validate(['decision_note' => ['nullable', 'string', 'max:255']]);

        $proposal = TransactionCancellationRequest::with('transaction')
            ->where('status', 'PROPOSED')
            ->findOrFail($id);

        DB::transaction(function () use ($proposal, $validated) {
            $this->transactions->cancel($proposal->transaction_id, $proposal->reason);
            $proposal->update([
                'status' => 'APPROVED',
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'decision_note' => $validated['decision_note'] ?? null,
            ]);
            $proposal->transaction->update(['is_locked' => false]);
        });

        Audit::log('cancel-transaction', 'transaction', $proposal->transaction_id, ['status' => 'ACTIVE'], ['status' => 'CANCELED'], $proposal->reason);

        return back()->with('success', 'Pembatalan nota disetujui.');
    }

    public function reject(Request $request, string $id)
    {
        $this->authorize('approve cancellation');
        $validated = $request->validate(['decision_note' => ['nullable', 'string', 'max:255']]);

        $proposal = TransactionCancellationRequest::where('status', 'PROPOSED')->findOrFail($id);

        DB::transaction(function () use ($proposal, $validated) {
            $proposal->update([
                'status' => 'REJECTED',
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'decision_note' => $validated['decision_note'] ?? null,
            ]);
            $proposal->transaction->update(['is_locked' => false]);
        });

        Audit::log('reject-cancellation', 'transaction_cancellation_request', $proposal->id, ['status' => 'PROPOSED'], ['status' => 'REJECTED'], $validated['decision_note'] ?? null);

        return back()->with('success', 'Usulan pembatalan ditolak, nota dibuka kembali.');
    }
}
