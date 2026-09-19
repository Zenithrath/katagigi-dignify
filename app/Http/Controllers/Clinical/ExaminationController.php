<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Examination;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExaminationController extends Controller
{
    /**
     * Pemeriksaan SOAP = kewenangan dokter (manajemen boleh mengoreksi).
     * Per PRD §4: RME klinis milik dokter + sign/final.
     */
    private function ensureClinician(): void
    {
        abort_unless(
            Auth::user()->hasRole(['doctor', 'manajemen']),
            403,
            'Pemeriksaan SOAP hanya boleh ditulis dokter.'
        );
    }

    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');
        abort_if($visit->examination()->exists(), 422, 'Pemeriksaan sudah ada, gunakan ubah.');

        $validated = $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'blood_pressure' => 'nullable|string|max:16',
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
        $this->authorize('update visit');
        $this->ensureClinician();
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'blood_pressure' => 'nullable|string|max:16',
        ]);

        $visit->examination()->updateOrCreate(['visit_id' => $visit->id], $validated + ['id' => (string) Str::uuid()]);

        return back()->with('success', 'Pemeriksaan SOAP diperbarui.');
    }
}
