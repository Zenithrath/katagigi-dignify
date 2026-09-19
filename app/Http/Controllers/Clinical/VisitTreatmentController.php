<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\DiagnosisCode;
use App\Models\Visit;
use App\Models\VisitTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VisitTreatmentController extends Controller
{
    private function ensureClinician(): void
    {
        abort_unless(
            Auth::user()->hasRole(['doctor', 'manajemen']),
            403,
            'Tindakan hanya boleh ditulis dokter.'
        );
    }

    private function visit($visitId): Visit
    {
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        return $visit;
    }

    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);

        $validated = $request->validate([
            'procedure_code_ids' => 'required|array|min:1',
            'procedure_code_ids.*' => ['required', 'string', 'distinct', Rule::exists('diagnosis_codes', 'id')->where('system', 'ICD9')->where('is_active', true)],
            'tooth_fdi' => ['nullable', 'string', Rule::in(\App\Models\OdontogramFinding::allTeeth())],
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $tooth = $validated['tooth_fdi'] ?? '';
        $codes = DiagnosisCode::whereIn('id', array_unique($validated['procedure_code_ids']))->get();
        foreach ($codes as $code) {
            VisitTreatment::firstOrCreate(
                ['visit_id' => $visit->id, 'procedure_code_id' => $code->id, 'tooth_fdi' => $tooth],
                [
                    'id' => (string) Str::uuid(),
                    'system' => $code->system,
                    'code' => $code->code,
                    'procedure' => $code->display_id,
                    'quantity' => $validated['quantity'] ?? 1,
                    'unit_price' => $validated['unit_price'] ?? 0,
                ]
            );
        }

        return back()->with('success', 'Tindakan tersimpan.');
    }

    public function destroy($visitId, $id)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);

        VisitTreatment::where('visit_id', $visit->id)->where('id', $id)->firstOrFail()->delete();

        return back()->with('success', 'Tindakan dihapus.');
    }
}
