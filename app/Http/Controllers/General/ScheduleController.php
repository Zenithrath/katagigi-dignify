<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Models\Schedule;
use App\Models\User;
use App\Services\General\ScheduleService;
use App\Services\Master\DoctorService;
use App\Types\Entities\ScheduleEntity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ScheduleController extends Controller
{
    private $service;

    private $doctorService;

    public function __construct(ScheduleService $service, DoctorService $doctorService)
    {
        $this->service = $service;
        $this->doctorService = $doctorService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // $this->authorize('read schedule');
        // $user = User::findOrFail(Auth::user()->id);
        // $roles = $user->getRoleNames();
        // if ($roles[0] == 'doctor') {
        //     $scheduleList = $this->service->selectAllSchedule(10, 0, ['doctor_id' => $user->id]);
        //     return view('pages.general.schedule.index', [
        //         "scheduleList" => $scheduleList,
        //     ]);
        // }
        $doctorList = $this->doctorService->selectAllDoctorOption();

        return view('pages.general.schedule.index', [
            // "scheduleList" => $scheduleList,
            'doctorList' => $doctorList,
        ]);
    }

    public function lookup(Request $request)
    {
        $total = $this->service->countTotalData($request);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->service->readScheduleByFilter($request),
            'pagination' => $pagination,
        ]);
    }

    public function lookupDoctor(Request $request)
    {
        $doctorID = auth()->user()->id;
        $total = $this->service->countTotalDataDoctor($doctorID);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->service->readScheduleDoctorByFilter($request, $doctorID),
            'pagination' => $pagination,
        ]);
    }

    public function filter(Request $request)
    {
        return response()->json($this->service->selectAllSchedule(10, 0, $request->all()));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // $this->authorize('create schedule');
        return view('pages.general.schedule.form', [
            'type' => 'create',
            'action' => route('schedules.store'),
            'data' => new Schedule,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(ScheduleRequest $request)
    {
        // $this->authorize('create schedule');
        $validated = $request->validated();

        $schedule = new ScheduleEntity;
        $schedule->fromRequest($validated, Str::uuid());

        $inserted = $this->service->insertSchedule($schedule);
        if ($inserted instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($inserted->getMessage());

            return back()
                ->withErrors('error', __('messages.schedule.error.oncreate'))->withInput();
        }

        return redirect()->route('schedules.index')
            ->with('success', __('messages.schedule.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateStatus($id)
    {
        $updated = $this->service->updateStatusSchedule($id);
        if ($updated instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($updated->getMessage());

            return back()
                ->withErrors('error', __('messages.schedule.error.onupdate'))->withInput();
        }

        return redirect()->route('schedules.index')
            ->with('success', __('messages.schedule.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $data = Schedule::find($id);

        // $this->authorize('delete schedule');
        $deleted = $this->service->deleteSchedule($id);
        if ($deleted instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($deleted->getMessage());

            return back()
                ->withErrors('error', __('messages.schedule.error.ondelete'))->withInput();
        }

        return redirect()->route('schedules.index')
            ->with('success', __('messages.schedule.success.ondelete'));
    }

    public function getScheduleByDoctorAndDay(Request $request)
    {
        $data = $request->all();

        return response()->json($data, 200);
    }
}
