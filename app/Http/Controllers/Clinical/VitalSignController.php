<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VitalSignController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('write vital sign');

        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'pulse_bpm' => 'nullable|integer|min:20|max:300',
            'temperature_c' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:80',
            'pregnancy_status' => 'nullable|in:PREGNANT,NOT_PREGNANT,UNSURE',
        ]);

        VitalSign::updateOrCreate(
            ['visit_id' => $visit->id],
            $validated + ['id' => (string) Str::uuid()]
        );

        return back()->with('success', 'Tanda vital tersimpan.');
    }
}
