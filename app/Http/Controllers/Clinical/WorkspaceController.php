<?php

namespace App\Http\Controllers\Clinical;

use App\Helpers\BranchContext;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('read visit');

        $today = date('Y-m-d');
        $isDoctor = Auth::user()->hasRole('doctor');

        $queueQuery = Visit::with([
            'patient',
            'patient.address',
            'doctor.user:id,name',
            'appointment',
            'vitalSign:id,visit_id,pulse_bpm,temperature_c,respiratory_rate,pregnancy_status',
            'anamnesis:id,visit_id,chief_complaint',
        ])
            ->whereDate('visit_date', $today)
            ->whereIn('clinical_status', Visit::QUEUE_STATUSES)
            ->orderBy('created_at');

        // Filter cabang aktif (null = semua) — sama seperti antrean lama.
        if ($branchId = BranchContext::currentId()) {
            $queueQuery->where('branch_id', $branchId);
        }

        if ($isDoctor) {
            $queueQuery->where('doctor_id', Auth::id());
        }

        $queue = $queueQuery->get();
        $current = (clone $queueQuery)->where('clinical_status', Visit::STATUS_IN_TREATMENT)->first();

        // Selesai hari ini (diperiksa + selesai konsultasi) untuk kolom kanan suster.
        $doneToday = Visit::with([
            'patient:id,code,name',
            'doctor.user:id,name',
            'appointment:id,time_start',
            'vitalSign:id,visit_id,pulse_bpm,temperature_c,respiratory_rate,pregnancy_status',
            'anamnesis:id,visit_id,chief_complaint',
        ])
            ->whereDate('visit_date', $today)
            ->whereIn('clinical_status', [Visit::STATUS_DONE, Visit::STATUS_SIGNED])
            ->when(BranchContext::currentId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('pages.clinical.workspace.index', [
            'queue' => $queue,
            'nurseQueue' => $queue->whereIn('clinical_status', Visit::QUEUE_NURSE)->values(),
            'doctorQueue' => $queue->whereIn('clinical_status', Visit::QUEUE_DOCTOR)->values(),
            'doneToday' => $doneToday,
            'current' => $current,
            'waitingCount' => $queue->where('clinical_status', Visit::STATUS_WAITING)->count(),
            'treatingCount' => $queue->where('clinical_status', Visit::STATUS_IN_TREATMENT)->count(),
            'transitions' => VisitController::TRANSITIONS,
            'doneCount' => Visit::whereDate('visit_date', $today)
                ->when($isDoctor, fn ($q) => $q->where('doctor_id', Auth::id()))
                ->when(BranchContext::currentId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
                ->whereIn('clinical_status', [Visit::STATUS_DONE, Visit::STATUS_SIGNED])
                ->count(),
            'isDoctor' => $isDoctor,
        ]);
    }
}
