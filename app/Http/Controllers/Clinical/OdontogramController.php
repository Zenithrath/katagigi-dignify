<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\OdontogramFinding;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OdontogramController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('write odontogram');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'fdi' => ['required', 'string', Rule::in(OdontogramFinding::allTeeth())],
            'surface' => ['nullable', 'string', Rule::in(array_keys(OdontogramFinding::SURFACES))],
            'condition' => ['required', 'string', Rule::in(array_keys(OdontogramFinding::CONDITIONS))],
            'material' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $finding = OdontogramFinding::firstOrNew([
            'visit_id' => $visit->id,
            'fdi' => $validated['fdi'],
            'surface' => $validated['surface'] ?? 'whole',
        ]);
        if (! $finding->exists) {
            $finding->id = (string) Str::uuid();
        }
        $finding->fill([
            'condition' => $validated['condition'],
            'material' => $validated['material'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        $finding->save();

        return back()->with('success', 'Temuan gigi '.$validated['fdi'].' tersimpan.');
    }

    public function destroy($visitId, $id)
    {
        $this->authorize('write odontogram');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $finding = OdontogramFinding::where('visit_id', $visit->id)->where('id', $id)->firstOrFail();
        $finding->delete();

        return back()->with('success', 'Temuan diarsipkan (soft delete).');
    }
}
