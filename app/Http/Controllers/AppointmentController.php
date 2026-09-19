<?php

namespace App\Http\Controllers;

use App\Http\Requests\General\AppointmentRequest;
use App\Http\Requests\General\UpdateAppointmentRequest;
use App\Services\General\AppointmentService;
use App\Services\General\ServiceService;
use App\Services\Master\DoctorService;
use App\Services\OptionService;
use App\Services\Patient\MasterService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    private $doctorService;

    private $serviceService;

    private $patientService;

    private $appointmentService;

    private $optionService;

    public function __construct()
    {
        $this->doctorService = new DoctorService;
        $this->serviceService = new ServiceService;
        $this->patientService = new MasterService;
        $this->appointmentService = new AppointmentService;
        $this->optionService = new OptionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('read appointment');
        $appointmentList = $this->appointmentService->readAllAppointments();

        return view('pages.general.appointment.index', [
            'appointmentList' => $appointmentList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create appointment');
        $doctorList = $this->doctorService->selectAllDoctorOption();
        $serviceList = $this->optionService->getServiceList();

        return view('pages.general.appointment.form', [
            'data' => (object) [],
            'doctors' => $doctorList,
            'services' => $serviceList,
            'type' => 'create',
            'action' => route('appointments.store'),
        ]);
    }

    public function lookup(Request $request)
    {
        $this->authorize('read appointment');
        $total = $this->appointmentService->countTotalData($request);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->appointmentService->readAppointmentByFilter($request),
            'pagination' => $pagination,
        ]);
    }

    public function lookupDoctor(Request $request)
    {
        $this->authorize('read appointment');
        $doctorID = auth()->user()->id;
        $total = $this->appointmentService->countTotalDataDoctor($doctorID);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->appointmentService->readAppointmentDoctorByFilter($request, $doctorID),
            'pagination' => $pagination,
        ]);
    }

    public function getPatientByCode(Request $request)
    {
        $this->authorize('read appointment');
        $search = $request->search;
        $patient = $this->patientService->getPatientBySearching($search);

        if ($patient instanceof Exception) {
            Log::error($patient->getMessage());

            return response()->json((object) ['error' => 'Patient not found'], 404);
        }

        return response()->json(
            (object) [
                'status' => 'success',
                'data' => $patient,
            ],
            200
        );
    }

    public function confirm(Request $request)
    {
        // D-04: konfirmasi via POST + gate (dulu GET terbuka).
        $this->authorize('update appointment');
        $confirmation = $this->appointmentService->patchAppointmentStatus('CONFIRMED', $request->appointment);

        if ($confirmation instanceof Exception || ! $confirmation) {
            return redirect()->back()
                ->with('error', __('messages.appointment.error.onconfirm'));
        }

        return redirect()->back()
            ->with('success', __('messages.appointment.success.onconfirm'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AppointmentRequest $request)
    {
        $this->authorize('create appointment');
        $validated = $request->validated();
        $patient = $this->patientService->selectPatientByID($validated['patient_id']);
        $validated['patient_id'] = $patient->id;
        $validated['patient_name'] = $patient->name;
        $validated['patient_code'] = $patient->code;
        $validated['patient_phone'] = $patient->phone;
        $doctor = $this->doctorService->selectDoctorByID($validated['doctor_id']);
        $validated['doctor_name'] = $doctor->name;
        $validated['doctor_nipp'] = $doctor->nipp;
        $validated['doctor_niptk'] = $doctor->niptk;
        $validated['status'] = 'PENDING';
        $services = $this->serviceService->readServicesByIDList($validated['service_id']);
        $validated['services'] = json_encode($services);

        $inserted = $this->appointmentService->createAppointment($validated);

        if ($inserted instanceof Exception) {
            Log::error($inserted->getMessage());

            return redirect()->back()
                ->with('error', __('messages.appointment.error.oncreate'));
        }

        return redirect()->route('appointments.index')
            ->with('success', __('messages.appointment.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('read appointment');
        $appointment = $this->appointmentService->readDetailAppointmentByID($id);
        $appointment->services = json_decode($appointment->services);

        return view('pages.general.appointment.detail', [
            'data' => $appointment,
            'visit' => \App\Models\Visit::where('appointment_id', $id)->first(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('update appointment');
        $doctorList = $this->doctorService->selectAllDoctorOption();
        $serviceList = $this->optionService->getServiceList();
        $appointment = $this->appointmentService->readAppointmentByID($id);

        return view('pages.general.appointment.form', [
            'data' => $appointment,
            'doctors' => $doctorList,
            'services' => $serviceList,
            'type' => 'update',
            'action' => route('appointments.update', ['appointment' => $id]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAppointmentRequest $request, $id)
    {
        $this->authorize('update appointment');
        $validated = $request->validated();
        $doctor = $this->doctorService->selectDoctorByID($validated['doctor_id']);
        $validated['doctor_name'] = $doctor->name;
        $validated['doctor_nipp'] = $doctor->nipp;
        $validated['doctor_niptk'] = $doctor->niptk;
        $services = $this->serviceService->readServicesByIDList($validated['service_id']);
        $validated['services'] = json_encode($services);

        $updated = $this->appointmentService->updateAppointment($validated, $id);
        if ($updated instanceof Exception) {
            Log::error($updated->getMessage());

            return redirect()->back()->with('error', __('messages.appointment.error.onupdate'));
        }

        return redirect()->route('appointments.index')
            ->with('success', __('messages.appointment.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete appointment');
        $status = $this->appointmentService->patchAppointmentStatus('CANCELED', $id);

        if ($status instanceof Exception) {
            Log::error($status->getMessage());

            return redirect()->back()->with('error', __('messages.appointment.error.oncancel'));
        }

        return redirect()->route('appointments.index')
            ->with('success', __('messages.appointment.success.oncancel'));
    }
}
