<?php

namespace App\Http\Controllers\Clinical;

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

        $queueQuery = Visit::with(['patient:id,code,name', 'doctor.user:id,name'])
            ->whereDate('visit_date', $today)
            ->whereIn('clinical_status', Visit::QUEUE_STATUSES)
            ->orderBy('created_at');

        if ($isDoctor) {
            $queueQuery->where('doctor_id', Auth::id());
        }

        $queue = $queueQuery->get();
        $current = (clone $queueQuery)->where('clinical_status', Visit::STATUS_IN_TREATMENT)->first();

        return view('pages.clinical.workspace.index', [
            'queue' => $queue,
            'current' => $current,
            'waitingCount' => $queue->where('clinical_status', Visit::STATUS_WAITING)->count(),
            'treatingCount' => $queue->where('clinical_status', Visit::STATUS_IN_TREATMENT)->count(),
            'doneCount' => Visit::whereDate('visit_date', $today)
                ->when($isDoctor, fn ($q) => $q->where('doctor_id', Auth::id()))
                ->whereIn('clinical_status', [Visit::STATUS_DONE, Visit::STATUS_SIGNED])
                ->count(),
            'isDoctor' => $isDoctor,
        ]);
    }
}
