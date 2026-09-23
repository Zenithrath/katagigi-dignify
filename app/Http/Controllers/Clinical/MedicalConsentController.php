<?php

namespace App\Http\Controllers\Clinical;

use App\Helpers\Audit;
use App\Http\Controllers\Controller;
use App\Models\MedicalConsentRecord;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MedicalConsentController extends Controller
{
    public function store(Request $request, $visitId)
    {
        $this->authorize('record consent');

        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $validated = $request->validate([
            'consent_type' => ['nullable', 'string', Rule::in(array_keys(MedicalConsentRecord::TYPES))],
            'consent_text' => 'required|string|max:8000',
            'granted' => 'required|boolean',
            'granted_by_name' => 'required|string|max:255',
            'granted_by_relation' => 'nullable|string|max:32',
            'signature' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,webp',
            'signature_data' => 'nullable|string',
            'notes' => 'nullable|string|max:4000',
        ]);

        // Tanda tangan = berkas medis: disk private, hanya lewat signed URL.
        // Bisa datang dari kanvas (data URI base64) atau unggahan gambar.
        $signaturePath = null;
        if (! empty($validated['signature_data'])
            && preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $validated['signature_data'], $m)
            && strlen($validated['signature_data']) <= 600_000
        ) {
            $binary = base64_decode(substr($validated['signature_data'], strpos($validated['signature_data'], ',') + 1), true);
            $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
            if ($binary !== false) {
                $signaturePath = 'consents/'.$visit->id.'/canvas-'.Str::random(8).'.'.$ext;
                Storage::disk('local')->put($signaturePath, $binary);
            }
        } elseif ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('consents/'.$visit->id, 'local');
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

        Audit::log('consent.create', 'medical_consent_records', $record->id, null, [
            'visit_id' => $visit->id,
            'granted' => $record->granted,
        ]);

        return back()->with('success', 'Informed consent tersimpan.');
    }

    public function destroy($visitId, $consent)
    {
        $this->authorize('revoke consent');

        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $record = MedicalConsentRecord::where('visit_id', $visit->id)->findOrFail($consent);

        if ($record->signature_path) {
            Storage::disk('local')->delete($record->signature_path);
        }

        $record->delete();
        Audit::log('consent.delete', 'medical_consent_records', $record->id, null, ['visit_id' => $visitId]);

        return back()->with('success', 'Consent dihapus.');
    }

    /**
     * Unduh gambar tanda tangan lewat signed URL (middleware signed + read visit).
     */
    public function signature($consent)
    {
        $this->authorize('read visit');

        $record = MedicalConsentRecord::findOrFail($consent);
        $path = $record->signature_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            'consent-'.$record->id.'.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Lembar cetak informed consent (Permenkes 24/2022) — arsip fisik berTTD.
     * Gambar tanda tangan disisipkan sebagai data URI agar tetap privat (disk local).
     */
    public function print($consent)
    {
        $this->authorize('read visit');

        $record = MedicalConsentRecord::with(['visit.branch', 'patient', 'doctor.user'])->findOrFail($consent);
        abort_unless($record->signature_path && Storage::disk('local')->exists($record->signature_path), 404,
            'Tanda tangan belum ada — consent tanpa tanda tangan tidak bisa dicetak.');

        $mime = match (pathinfo($record->signature_path, PATHINFO_EXTENSION)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return view('pages.clinical.visit.partials.consent-print', [
            'consent' => $record,
            'signatureSrc' => 'data:'.$mime.';base64,'.base64_encode(Storage::disk('local')->get($record->signature_path)),
        ]);
    }
}
