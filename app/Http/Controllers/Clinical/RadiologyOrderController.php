<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Helpers\Audit;
use App\Models\RadiologyOrder;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RadiologyOrderController extends Controller
{
    private function ensureClinician(): void
    {
        abort_unless(
            Auth::user()->hasRole(['doctor', 'manajemen']),
            403,
            'Order radiologi hanya boleh dibuat dokter.'
        );
    }

    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = Visit::findOrFail($visitId);

        $validated = $request->validate([
            'modality' => 'required|in:DX,CR,DR,CT,MG,US,MR',
            'body_site' => 'nullable|string|max:255',
            'clinical_indication' => 'required|string|max:2000',
            'priority' => 'nullable|in:routine,urgent,stat',
            'notes' => 'nullable|string|max:4000',
        ]);

        $order = RadiologyOrder::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'ordered_by' => $visit->doctor_id,
            ...$validated,
            'priority' => $validated['priority'] ?? 'routine',
            'status' => RadiologyOrder::STATUS_ORDERED,
        ]);

        Audit::record('radiology.create', 'radiology_orders', $order->id, ['visit_id' => $visit->id]);

        return back()->with('success', 'Order radiologi dibuat.');
    }

    public function updateResult(Request $request, $visitId, $order)
    {
        $this->authorize('update visit');
        $this->ensureClinician();

        $order = RadiologyOrder::where('visit_id', $visitId)->findOrFail($order);

        $validated = $request->validate([
            'status' => 'required|in:SCHEDULED,IN_PROGRESS,COMPLETED,CANCELLED',
            'performed_at' => 'nullable|date',
            'result_text' => 'nullable|string|max:16000',
            'result_file' => 'nullable|file|max:20480',
        ]);

        if ($request->hasFile('result_file')) {
            if ($order->result_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($order->result_path);
            }
            $order->result_path = $request->file('result_file')->store('radiology/'.$order->visit_id, 'public');
        }

        $order->fill($validated);
        $order->save();

        Audit::record('radiology.result', 'radiology_orders', $order->id, ['status' => $order->status]);

        return back()->with('success', 'Hasil radiologi diperbarui.');
    }

    public function destroy($visitId, $order)
    {
        $this->authorize('update visit');
        $this->ensureClinician();

        $order = RadiologyOrder::where('visit_id', $visitId)->findOrFail($order);
        $order->delete();
        Audit::record('radiology.delete', 'radiology_orders', $order->id, ['visit_id' => $visitId]);

        return back()->with('success', 'Order radiologi dihapus.');
    }
}
