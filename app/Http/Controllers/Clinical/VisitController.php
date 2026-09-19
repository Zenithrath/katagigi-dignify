<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Services\Clinical\VisitService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    private const TRANSITIONS = [
        Visit::STATUS_REGISTERED => [Visit::STATUS_WAITING],
        Visit::STATUS_WAITING => [Visit::STATUS_CALLED],
        Visit::STATUS_CALLED => [Visit::STATUS_IN_TREATMENT],
        Visit::STATUS_IN_TREATMENT => [Visit::STATUS_DONE],
        Visit::STATUS_DONE => [],
        Visit::STATUS_SIGNED => [],
    ];

    public function __construct(private VisitService $visits) {}

    /**
     * Antrian hari ini (default status antrean aktif + filter).
     */
    public function index(Request $request)
    {
        $this->authorize('read visit');

        $statuses = $request->filled('status')
            ? [$request->status]
            : Visit::QUEUE_STATUSES;

        $query = Visit::with(['patient:id,code,name', 'doctor.user:id,name', 'branch:id,name'])
            ->whereDate('visit_date', $request->input('date', date('Y-m-d')))
            ->whereIn('clinical_status', $statuses)
            ->orderBy('created_at');

        // Fase 4: filter cabang aktif (null = semua).
        if ($branchId = \App\Helpers\BranchContext::currentId()) {
            $query->where('branch_id', $branchId);
        }

        if (Auth::user()->hasRole('doctor') && ! $request->filled('doctor')) {
            $query->where('doctor_id', Auth::id());
        } elseif ($request->filled('doctor')) {
            $query->where('doctor_id', $request->doctor);
        }

        return view('pages.clinical.queue.index', [
            'visits' => $query->paginate(20)->withQueryString(),
            'statuses' => $statuses,
            'transitions' => self::TRANSITIONS,
        ]);
    }

    public function show($id)
    {
        $this->authorize('read visit');

        $visit = Visit::with([
            'patient', 'doctor.user', 'appointment', 'branch', 'signer:id,name',
            'anamnesis', 'examination', 'odontogramFindings', 'diagnoses', 'treatments',
            'treatmentPlans.items', 'prescriptions.items', 'attachments.uploader', 'invoices',
        ])->findOrFail($id);

        return view('pages.clinical.visit.show', [
            'visit' => $visit,
            'transitions' => self::TRANSITIONS,
        ]);
    }

    /**
     * Check-in: appointment terkonfirmasi → visit WAITING.
     */
    public function checkin($appointmentId)
    {
        $this->authorize('create visit');

        $appointment = DB::table('appointments')->where('id', $appointmentId)->first();
        abort_if(! $appointment, 404);
        abort_if(! $appointment->confirmed_at || $appointment->canceled_at, 422, 'Hanya appointment terkonfirmasi yang bisa check-in.');

        $exists = Visit::where('appointment_id', $appointmentId)->first();
        if ($exists) {
            return redirect()->route('visits.show', $exists->id)
                ->with('info', 'Appointment ini sudah check-in.');
        }

        $visit = $this->visits->createVisit([
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'visit_date' => $appointment->date,
            'clinical_status' => Visit::STATUS_WAITING,
        ]);

        if ($visit instanceof Exception) {
            return back()->withErrors('error', 'Check-in gagal, coba lagi.');
        }

        return redirect()->route('visits.show', $visit->id)
            ->with('success', 'Check-in berhasil. Nomor visit '.$visit->visit_number.'.');
    }

    /**
     * Maju status klinis (maju saja; SIGNED lewat rute sign Task 10).
     */
    public function updateStatus(Request $request, $id)
    {
        $this->authorize('update visit');

        $visit = Visit::findOrFail($id);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        $request->validate(['status' => 'required|string']);
        $allowed = self::TRANSITIONS[$visit->clinical_status] ?? [];
        abort_unless(in_array($request->status, $allowed, true), 422, 'Transisi status tidak valid.');

        $visit->update(['clinical_status' => $request->status]);

        return back()->with('success', 'Status visit: '.$request->status.'.');
    }

    /**
     * Task 10: tanda tangan elektronik visit (SIGNED = kunci).
     * Syarat: status DONE + minimal 1 diagnosis ICD-10 (kelengkapan SATUSEHAT).
     * Menandai appointment tercatat agar konsisten dengan dashboard v1.
     */
    public function sign($id)
    {
        $this->authorize('sign visit');

        $visit = Visit::findOrFail($id);
        abort_if($visit->isSigned(), 422, 'Visit sudah SIGNED.');
        abort_unless($visit->clinical_status === Visit::STATUS_DONE, 422, 'Visit harus DONE sebelum ditandatangani.');
        abort_unless(
            $visit->diagnoses()->where('system', 'ICD10')->exists(),
            422,
            'Minimal 1 diagnosis ICD-10 sebelum sign (syarat SATUSEHAT).'
        );

        $visit->update([
            'clinical_status' => Visit::STATUS_SIGNED,
            'signed_at' => now(),
            'signed_by' => Auth::id(),
        ]);

        if ($visit->appointment_id) {
            DB::table('appointments')->where('id', $visit->appointment_id)->update([
                'recorded_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return back()->with('success', 'Visit '.$visit->visit_number.' ditandatangani dan dikunci.');
    }
}
