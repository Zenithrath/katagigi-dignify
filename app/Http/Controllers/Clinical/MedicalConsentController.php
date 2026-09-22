<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Helpers\Audit;
use App\Models\MedicalConsentRecord;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MedicalConsentController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        abort_unless(
            Auth::user()->hasRole(['doctor', 'nurse', 'manajemen', 'admin']),
            403,
            'Consent diisi staf klinik yang menangani pasien.'
        );

        $visit = Visit::findOrFail($visitId);

        $validated = $request->validate([
            'consent_type' => 'nullable|string|max:32',
            'consent_text' => 'required|string|max:8000',
            'granted' => 'required|boolean',
            'granted_by_name' => 'required|string|max:255',
            'granted_by_relation' => 'nullable|string|max:32',
            'signature' => 'nullable|image|max:4096',
            'notes' => 'nullable|string|max:4000',
        ]);

        $signaturePath = null;
        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('consents/'.$visit->id, 'public');
        }

        $record = MedicalConsentRecord::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'doctor_id' => $visit->doctor_id,
            'consent_type' => $validated['consent_type'] ?? 'treatment',
            'consent_text' => $validated['consent_text'],
            'granted' => $validated['granted'],
            'granted_by_name' => $validated['granted_by_name'],
            'granted_by_relation' => $validated['granted_by_relation'] ?? null,
            'granted_at' => now(),
            'signature_path' => $signaturePath,
            'notes' => $validated['notes'] ?? null,
        ]);

        Audit::record('consent.create', 'medical_consent_records', $record->id, [
            'visit_id' => $visit->id,
            'granted' => $record->granted,
        ]);

        return back()->with('success', 'Informed consent tersimpan.');
    }

    public function destroy($visitId, $consent)
    {
        $this->authorize('update visit');
        abort_unless(Auth::user()->hasRole(['doctor', 'manajemen']), 403);

        $record = MedicalConsentRecord::where('visit_id', $visitId)->findOrFail($consent);

        if ($record->signature_path) {
            Storage::disk('public')->delete($record->signature_path);
        }

        $record->delete();
        Audit::record('consent.delete', 'medical_consent_records', $record->id, ['visit_id' => $visitId]);

        return back()->with('success', 'Consent dihapus.');
    }
}
