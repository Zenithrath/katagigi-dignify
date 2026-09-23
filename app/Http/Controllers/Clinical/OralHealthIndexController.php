<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\OralHealthIndex;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OralHealthIndexController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('write oral health index');

        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'ohis_debris' => 'nullable|numeric|min:0|max:5',
            'ohis_calculus' => 'nullable|numeric|min:0|max:5',
            'd_count' => 'nullable|integer|min:0|max:32',
            'm_count' => 'nullable|integer|min:0|max:32',
            'f_count' => 'nullable|integer|min:0|max:32',
            'notes' => 'nullable|string|max:4000',
        ]);

        $debris = $validated['ohis_debris'] ?? null;
        $calculus = $validated['ohis_calculus'] ?? null;
        $ohisTotal = null;
        if ($debris !== null || $calculus !== null) {
            $ohisTotal = round((float) ($debris ?? 0) + (float) ($calculus ?? 0), 1);
        }

        // Fase 3.2/4.2: DMF-T dihitung otomatis dari odontogram bila doctor/nurse
        // tidak mengisi manual — D=karies/root/fracture, M=missing, F=filled/crown/implant.
        $autoD = null;
        $autoM = null;
        $autoF = null;
        if (! isset($validated['d_count'], $validated['m_count'], $validated['f_count'])) {
            $teeth = $visit->odontogramFindings()
                ->select('fdi', 'condition')
                ->get()
                ->groupBy('fdi');
            $autoD = $teeth->filter(fn ($f) => $f->pluck('condition')->intersect(['caries', 'root', 'fracture'])->isNotEmpty())->count();
            $autoM = $teeth->filter(fn ($f) => $f->pluck('condition')->contains('missing'))->count();
            $autoF = $teeth->filter(fn ($f) => $f->pluck('condition')->intersect(['filled', 'crown', 'implant', 'denture'])->isNotEmpty())->count();
        }

        $dCount = $validated['d_count'] ?? $autoD;
        $mCount = $validated['m_count'] ?? $autoM;
        $fCount = $validated['f_count'] ?? $autoF;

        $dmt = null;
        if ($dCount !== null && $mCount !== null && $fCount !== null) {
            $dmt = round($dCount + $mCount + $fCount, 1);
        }

        OralHealthIndex::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'id' => (string) Str::uuid(),
                'patient_id' => $visit->patient_id,
                ...$validated,
                'd_count' => $dCount ?? 0,
                'm_count' => $mCount ?? 0,
                'f_count' => $fCount ?? 0,
                'ohis_total' => $ohisTotal,
                'dmt_index' => $dmt,
            ]
        );

        return back()->with('success', 'OHI-S & DMF-T tersimpan.');
    }
}
