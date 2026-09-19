<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TreatmentPlanController extends Controller
{
    private function ensureClinician(): void
    {
        abort_unless(
            Auth::user()->hasRole(['doctor', 'manajemen']),
            403,
            'Rencana perawatan hanya boleh ditulis dokter.'
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
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        TreatmentPlan::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'status' => TreatmentPlan::STATUSES[0],
            ...$validated,
        ]);

        return back()->with('success', 'Rencana perawatan dibuat.');
    }

    public function updateStatus(Request $request, $visitId, $id)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);

        $validated = $request->validate([
            'status' => ['required', Rule::in(TreatmentPlan::STATUSES)],
        ]);

        TreatmentPlan::where('visit_id', $visit->id)->where('id', $id)->firstOrFail()
            ->update(['status' => $validated['status']]);

        return back()->with('success', 'Status rencana: '.$validated['status'].'.');
    }

    public function destroy($visitId, $id)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);

        TreatmentPlan::where('visit_id', $visit->id)->where('id', $id)->firstOrFail()->delete();

        return back()->with('success', 'Rencana dihapus.');
    }

    public function storeItem(Request $request, $visitId, $planId)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);
        $plan = TreatmentPlan::where('visit_id', $visit->id)->where('id', $planId)->firstOrFail();

        $validated = $request->validate([
            'tooth_fdi' => ['nullable', 'string', Rule::in(\App\Models\OdontogramFinding::allTeeth())],
            'description' => 'required|string|max:255',
            'estimated_price' => 'nullable|numeric|min:0',
            'priority' => 'nullable|integer|min:1|max:3',
        ]);

        TreatmentPlanItem::create([
            'id' => (string) Str::uuid(),
            'treatment_plan_id' => $plan->id,
            'tooth_fdi' => $validated['tooth_fdi'] ?? '',
            'description' => $validated['description'],
            'estimated_price' => $validated['estimated_price'] ?? 0,
            'priority' => $validated['priority'] ?? 2,
            'status' => TreatmentPlan::STATUSES[0],
        ]);

        return back()->with('success', 'Item rencana ditambahkan.');
    }

    public function destroyItem($visitId, $planId, $itemId)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = $this->visit($visitId);
        $plan = TreatmentPlan::where('visit_id', $visit->id)->where('id', $planId)->firstOrFail();

        TreatmentPlanItem::where('treatment_plan_id', $plan->id)->where('id', $itemId)->firstOrFail()->delete();

        return back()->with('success', 'Item rencana dihapus.');
    }
}
