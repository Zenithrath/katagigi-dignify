<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\DiagnosisCode;
use App\Models\Visit;
use App\Models\VisitDiagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VisitDiagnosisController extends Controller
{
    private function ensureClinician(): void
    {
        abort_unless(
            Auth::user()->hasRole(['doctor', 'manajemen']),
            403,
            'Diagnosis hanya boleh ditulis dokter.'
        );
    }

    private function visit($visitId): Visit
    {
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        return $visit;
    }

    /**
     * Tambah ≥1 kode sekaligus (dari komponen diagnosis-search).
     * D-03 berlaku di sini: diagnosis wajib ICD-10 aktif (syarat SATUSEHAT).
     */
    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);

        $validated = $request->validate([
            'diagnosis_code_ids' => 'required|array|min:1',
            'diagnosis_code_ids.*' => ['required', 'string', 'distinct', Rule::exists('diagnosis_codes', 'id')->where('system', 'ICD10')->where('is_active', true)],
            'tooth_fdi' => ['nullable', 'string', Rule::in(\App\Models\OdontogramFinding::allTeeth())],
            'is_primary' => 'nullable|boolean',
        ]);

        $tooth = $validated['tooth_fdi'] ?? '';
        $codes = DiagnosisCode::whereIn('id', array_unique($validated['diagnosis_code_ids']))->get();
        foreach ($codes as $code) {
            VisitDiagnosis::firstOrCreate(
                ['visit_id' => $visit->id, 'diagnosis_code_id' => $code->id, 'tooth_fdi' => $tooth],
                [
                    'id' => (string) Str::uuid(),
                    'system' => $code->system,
                    'code' => $code->code,
                    'display' => $code->display_id,
                    'is_primary' => $request->boolean('is_primary', true),
                ]
            );
        }

        return back()->with('success', 'Diagnosis tersimpan.');
    }

    public function destroy($visitId, $id)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);

        VisitDiagnosis::where('visit_id', $visit->id)->where('id', $id)->firstOrFail()->delete();

        return back()->with('success', 'Diagnosis dihapus.');
    }
}
