<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Examination;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExaminationController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('write examination');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');
        abort_if($visit->examination()->exists(), 422, 'Pemeriksaan sudah ada, gunakan ubah.');

        $validated = $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'blood_pressure' => 'nullable|string|max:16',
            'occlusion' => ['nullable', Rule::in(array_keys(Examination::OCCLUSIONS))],
            'torus' => ['nullable', Rule::in(array_keys(Examination::TORUS))],
            'palatum' => ['nullable', Rule::in(array_keys(Examination::PALATUM))],
            'diastema' => ['nullable', Rule::in(array_keys(Examination::DIASTEMA))],
            'molar_relation' => ['nullable', Rule::in(array_keys(Examination::ANGLE_CLASSES))],
            'canine_relation' => ['nullable', Rule::in(array_keys(Examination::ANGLE_CLASSES))],
            'other_oral_findings' => 'nullable|string|max:4000',
        ]);

        Examination::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            ...$validated,
        ]);

        return back()->with('success', 'Pemeriksaan SOAP tersimpan.');
    }

    public function update(Request $request, $visitId)
    {
        $this->authorize('write examination');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'blood_pressure' => 'nullable|string|max:16',
            'occlusion' => ['nullable', Rule::in(array_keys(Examination::OCCLUSIONS))],
            'torus' => ['nullable', Rule::in(array_keys(Examination::TORUS))],
            'palatum' => ['nullable', Rule::in(array_keys(Examination::PALATUM))],
            'diastema' => ['nullable', Rule::in(array_keys(Examination::DIASTEMA))],
            'molar_relation' => ['nullable', Rule::in(array_keys(Examination::ANGLE_CLASSES))],
            'canine_relation' => ['nullable', Rule::in(array_keys(Examination::ANGLE_CLASSES))],
            'other_oral_findings' => 'nullable|string|max:4000',
        ]);

        $visit->examination()->updateOrCreate(['visit_id' => $visit->id], $validated + ['id' => (string) Str::uuid()]);

        return back()->with('success', 'Pemeriksaan SOAP diperbarui.');
    }
}
