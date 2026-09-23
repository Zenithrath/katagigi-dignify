<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Fase 4.3: surat keterangan istirahat (sakit), surat keterangan berobat,
 * dan surat rujukan — dicetak dari data visit (tanpa tabel baru, arsip tetap
 * melalui audit log). PDF diperoleh lewat dialog cetak browser (print CSS).
 */
class LetterController extends Controller
{
    public const TYPES = [
        'sick' => 'Surat Keterangan Sakit (Istirahat)',
        'medical' => 'Surat Keterangan Berobat',
        'referral' => 'Surat Rujukan',
    ];

    public function print(Request $request, $visitId)
    {
        $this->authorize('read visit');

        $visit = Visit::with([
            'patient.address', 'doctor.user', 'branch', 'diagnoses', 'examination',
        ])->findOrFail($visitId);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'rest_days' => 'nullable|integer|min:0|max:30',
            'rest_note' => 'nullable|string|max:500',
            'referral_to' => 'nullable|string|max:255',
            'referral_notes' => 'nullable|string|max:2000',
        ]);

        $type = $validated['type'];
        $restDays = (int) ($validated['rest_days'] ?? 0);
        $restFrom = $restDays > 0 ? $visit->visit_date->copy() : null;
        $restUntil = $restDays > 0 ? $visit->visit_date->copy()->addDays($restDays - 1) : null;

        return view('pages.clinical.visit.partials.letter-print', [
            'visit' => $visit,
            'type' => $type,
            'title' => self::TYPES[$type],
            'restDays' => $restDays,
            'restFrom' => $restFrom,
            'restUntil' => $restUntil,
            'restNote' => $validated['rest_note'] ?? null,
            'referralTo' => $validated['referral_to'] ?? null,
            'referralNotes' => $validated['referral_notes'] ?? null,
        ]);
    }
}
