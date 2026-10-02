<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Anamnesis;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnamnesisController extends Controller
{
    /**
     * Anamnesis boleh didraf dokter & asisten pendamping (izin `write anamnesis`).
     * Visit SIGNED terkunci.
     */
    public function store(Request $request, $visitId)
    {
        $this->authorize('write anamnesis');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');
        abort_if($visit->anamnesis()->exists(), 422, 'Anamnesis sudah ada, gunakan ubah.');

        $validated = $request->validate([
            'chief_complaint' => 'required|string',
            'present_illness' => 'nullable|string',
            'past_medical_history' => 'nullable|string',
            'dental_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
        ]);

        Anamnesis::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            ...$validated,
        ]);

        // Triase dari workspace: otomatis masuk antrean dokter.
        if ($request->boolean('advance_to_called') && $visit->clinical_status === Visit::STATUS_WAITING) {
            $visit->update(['clinical_status' => Visit::STATUS_CALLED]);

            return back()->with('success', 'Triase tersimpan — pasien masuk antrean dokter.');
        }

        return back()->with('success', 'Anamnesis tersimpan.');
    }

    public function update(Request $request, $visitId)
    {
        $this->authorize('write anamnesis');
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'chief_complaint' => 'required|string',
            'present_illness' => 'nullable|string',
            'past_medical_history' => 'nullable|string',
            'dental_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
        ]);

        $visit->anamnesis()->updateOrCreate(['visit_id' => $visit->id], $validated + ['id' => (string) Str::uuid()]);

        // Triase dari workspace: otomatis masuk antrean dokter.
        if ($request->boolean('advance_to_called') && $visit->fresh()->clinical_status === Visit::STATUS_WAITING) {
            $visit->update(['clinical_status' => Visit::STATUS_CALLED]);

            return back()->with('success', 'Triase tersimpan — pasien masuk antrean dokter.');
        }

        return back()->with('success', 'Anamnesis diperbarui.');
    }
}
