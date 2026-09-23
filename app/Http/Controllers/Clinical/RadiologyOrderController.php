<?php

namespace App\Http\Controllers\Clinical;

use App\Helpers\Audit;
use App\Http\Controllers\Controller;
use App\Models\RadiologyOrder;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RadiologyOrderController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('write radiology');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'modality' => ['required', Rule::in(array_keys(RadiologyOrder::MODALITIES))],
            'body_site' => 'nullable|string|max:255',
            'clinical_indication' => 'required|string|max:2000',
            'priority' => ['nullable', Rule::in(array_keys(RadiologyOrder::PRIORITIES))],
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

        Audit::log('radiology.create', 'radiology_orders', $order->id, null, ['visit_id' => $visit->id]);

        return back()->with('success', 'Order radiologi dibuat.');
    }

    public function updateResult(Request $request, $visitId, $order)
    {
        $this->authorize('write radiology');

        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $order = RadiologyOrder::where('visit_id', $visit->id)->findOrFail($order);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(RadiologyOrder::RESULT_STATUSES))],
            'performed_at' => 'nullable|date',
            'result_text' => 'nullable|string|max:16000',
            'result_file' => 'nullable|file|max:20480',
        ]);

        // Hasil radiologi = berkas medis: disk private, hanya lewat signed URL.
        if ($request->hasFile('result_file')) {
            if ($order->result_path) {
                Storage::disk('local')->delete($order->result_path);
            }
            $order->result_path = $request->file('result_file')->store('radiology/'.$order->visit_id, 'local');
        }

        $order->fill($validated);
        $order->save();

        Audit::log('radiology.result', 'radiology_orders', $order->id, null, ['status' => $order->status]);

        return back()->with('success', 'Hasil radiologi diperbarui.');
    }

    public function destroy($visitId, $order)
    {
        $this->authorize('write radiology');

        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $order = RadiologyOrder::where('visit_id', $visit->id)->findOrFail($order);

        if ($order->result_path) {
            Storage::disk('local')->delete($order->result_path);
        }

        $order->delete();
        Audit::log('radiology.delete', 'radiology_orders', $order->id, null, ['visit_id' => $visitId]);

        return back()->with('success', 'Order radiologi dihapus.');
    }

    /**
     * Unduh berkas hasil lewat signed URL (middleware signed + read visit).
     */
    public function resultFile($order)
    {
        $this->authorize('read visit');

        $order = RadiologyOrder::findOrFail($order);
        $path = $order->result_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            'radiologi-'.$order->id.'.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }
}
