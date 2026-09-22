<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Services\General\ScheduleService;
use App\Services\Master\DoctorService;
use App\Types\Entities\ScheduleEntity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
    private $service;

    private $doctorService;

    public function __construct(ScheduleService $service, DoctorService $doctorService)
    {
        $this->service = $service;
        $this->doctorService = $doctorService;
    }

    public function index()
    {
        $this->authorize('read schedule');
        $doctorList = $this->doctorService->selectAllDoctorOption();

        return view('pages.general.schedule.index', [
            'doctorList' => $doctorList,
        ]);
    }

    public function lookup(Request $request)
    {
        $this->authorize('read schedule');
        $total = $this->service->countTotalData($request);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => max(1, (int) $request->page),
            'limit' => (int) $limit,
            'last' => max(1, (int) ceil($total->counter / $limit)),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->service->readScheduleByFilter($request),
            'pagination' => $pagination,
        ]);
    }

    public function lookupDoctor(Request $request)
    {
        $this->authorize('read schedule');
        $doctorID = auth()->user()->id;
        $total = $this->service->countTotalDataDoctor($doctorID);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => max(1, (int) $request->page),
            'limit' => (int) $limit,
            'last' => max(1, (int) ceil($total->counter / $limit)),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->service->readScheduleDoctorByFilter($request, $doctorID),
            'pagination' => $pagination,
        ]);
    }

    public function store(ScheduleRequest $request)
    {
        $this->authorize('create schedule');
        $validated = $request->validated();

        $schedule = new ScheduleEntity;
        $schedule->fromRequest($validated, Str::uuid());

        $inserted = $this->service->insertSchedule($schedule);
        if ($inserted instanceof Exception) {
            Log::error($inserted->getMessage());

            return back()
                ->withErrors(['error' => __('messages.schedule.error.oncreate')])->withInput();
        }

        return redirect()->route('schedules.index')
            ->with('success', __('messages.schedule.success.oncreate'));
    }

    public function updateStatus($id)
    {
        $this->authorize('update schedule');
        $updated = $this->service->updateStatusSchedule($id);
        if ($updated instanceof Exception) {
            Log::error($updated->getMessage());

            return back()
                ->withErrors(['error' => __('messages.schedule.error.onupdate')])->withInput();
        }

        return redirect()->route('schedules.index')
            ->with('success', __('messages.schedule.success.onupdate'));
    }

    public function destroy($id)
    {
        $this->authorize('delete schedule');
        $deleted = $this->service->deleteSchedule($id);
        if ($deleted instanceof Exception) {
            Log::error($deleted->getMessage());

            return back()
                ->withErrors(['error' => __('messages.schedule.error.ondelete')])->withInput();
        }

        return redirect()->route('schedules.index')
            ->with('success', __('messages.schedule.success.ondelete'));
    }
}
