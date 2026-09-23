<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
    private function visit($visitId): Visit
    {
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        return $visit;
    }

    public function store(Request $request, $visitId)
    {
        $this->authorize('write prescription');
        $visit = $this->visit($visitId);

        $validated = $request->validate([
            'prescribed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Prescription::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'prescribed_at' => $validated['prescribed_at'] ?? date('Y-m-d'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Resep dibuat.');
    }

    public function destroy($visitId, $id)
    {
        $this->authorize('write prescription');
        $visit = $this->visit($visitId);

        Prescription::where('visit_id', $visit->id)->where('id', $id)->firstOrFail()->delete();

        return back()->with('success', 'Resep dihapus.');
    }

    public function storeItem(Request $request, $visitId, $prescriptionId)
    {
        $this->authorize('write prescription');
        $visit = $this->visit($visitId);
        $prescription = Prescription::where('visit_id', $visit->id)->where('id', $prescriptionId)->firstOrFail();

        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            // Fase 4.2: kode KFA divalidasi terhadap kamus lokal bila diisi.
            'kfa_code' => ['nullable', 'string', 'max:64', Rule::exists('master_kfa', 'code')->where('is_active', true)],
            'dosage' => 'nullable|string|max:64',
            'frequency' => 'nullable|string|max:64',
            'duration' => 'nullable|string|max:64',
            'quantity' => 'nullable|integer|min:1',
            'route' => 'nullable|string|max:32',
            'instruction' => 'nullable|string',
        ]);

        PrescriptionItem::create([
            'id' => (string) Str::uuid(),
            'prescription_id' => $prescription->id,
            'medicine_name' => $validated['medicine_name'],
            'kfa_code' => $validated['kfa_code'] ?? null,
            'dosage' => $validated['dosage'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'quantity' => $validated['quantity'] ?? 1,
            'route' => $validated['route'] ?? null,
            'instruction' => $validated['instruction'] ?? null,
        ]);

        return back()->with('success', 'Obat ditambahkan ke resep.');
    }

    public function destroyItem($visitId, $prescriptionId, $itemId)
    {
        $this->authorize('write prescription');
        $visit = $this->visit($visitId);
        $prescription = Prescription::where('visit_id', $visit->id)->where('id', $prescriptionId)->firstOrFail();

        PrescriptionItem::where('prescription_id', $prescription->id)->where('id', $itemId)->firstOrFail()->delete();

        return back()->with('success', 'Obat dihapus dari resep.');
    }
}
