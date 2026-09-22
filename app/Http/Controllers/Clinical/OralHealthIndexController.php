<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\OralHealthIndex;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OralHealthIndexController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        abort_unless(
            Auth::user()->hasRole(['doctor', 'nurse', 'manajemen']),
            403,
            'Indeks kesehatan mulut hanya boleh diisi nakes/manajemen.'
        );

        $visit = Visit::findOrFail($visitId);

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

        $dmt = null;
        if (isset($validated['d_count'], $validated['m_count'], $validated['f_count'])) {
            $dmt = round($validated['d_count'] + $validated['m_count'] + $validated['f_count'], 1);
        }

        OralHealthIndex::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'id' => (string) Str::uuid(),
                'patient_id' => $visit->patient_id,
                ...$validated,
                'ohis_total' => $ohisTotal,
                'dmt_index' => $dmt,
            ]
        );

        return back()->with('success', 'OHI-S & DMF-T tersimpan.');
    }
}
