<?php

namespace App\Http\Controllers\Patient;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Services\Patient\MasterService;
use App\Services\Patient\MedicalRecordService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class MasterController extends Controller
{
    private $service;

    private $recordService;

    public function __construct(MasterService $service, MedicalRecordService $recordService)
    {
        $this->service = $service;
        $this->recordService = $recordService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return view('pages.patient.master.index', [
            'patientList' => $this->service->readAllPatients($request),
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
            'data' => $this->service->readPatientByFilter($request),
            'pagination' => $pagination,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create patient');

        return view('pages.patient.master.form', [
            'type' => 'create',
            'action' => route('patients.store'),
            'data' => new Patient,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(PatientRequest $request)
    {
        $this->authorize('create patient');
        $storeBag = [];

        if ($request->hasFile('picture')) {
            $storeBag['picture'] = $this->service->storeImage($request->picture)->path;
        }

        if ($request->has('sosmed')) {
            $key = ['facebook', 'instagram', 'tiktok', 'twitter'];
            $storeBag['sosmed'] = json_encode(array_combine($key, $request->sosmed));
        }

        if ($request->has('phone')) {
            $storeBag['phone'] = '62'.$request->phone;
        }

        $request->replace([
            ...$request->all(),
            ...$storeBag,
        ]);

        $inserted = $this->service->insertPatient($request);

        if ($inserted instanceof Throwable) {
            return back()->withErrors('error', __('messages.patient.error.oncreate'))
                ->withInput();
        }

        return redirect()->route('patients.index')
            ->with('success', __('messages.patient.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $data = $this->service->selectPatientByID($id);
        $data->sosmed = json_decode($data->sosmed);
        $data->records = $this->recordService->readMedicalRecordByPatiendID($id);
        $toRupiah = function ($value) {
            return GeneralHelper::floatToRupiah($value);
        };

        return view('pages.patient.master.detail', [
            'data' => $data,
            'toRupiah' => $toRupiah,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = $this->service->selectPatientByID($id);
        $data->sosmed = json_decode($data->sosmed);

        return view('pages.patient.master.form', [
            'type' => 'update',
            'action' => route('patients.update', $id),
            'data' => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(PatientRequest $request, $id)
    {
        $patient = $this->service->selectPatientByID($id);
        $updatedPatientPicture = $patient->picture;
        $updateBag = [];

        if ($request->hasFile('picture')) {
            if ($updatedPatientPicture) {
                $this->service->deleteImage($updatedPatientPicture);
            }
            $picture = $this->service->storeImage($request->picture);
            $updateBag['picture'] = $picture->path;
        }

        if ($request->has('sosmed')) {
            $key = ['facebook', 'instagram', 'tiktok', 'twitter'];
            $updateBag['sosmed'] = json_encode(array_combine($key, $request->sosmed));
        }

        if ($request->has('phone')) {
            $updateBag['phone'] = '62'.$request->phone;
        }

        $request->replace([
            ...$request->all(),
            ...$updateBag,
        ]);

        $updated = $this->service->updatePatient($request, $id);

        if ($updated instanceof Exception) {
            return back()
                ->withErrors('error', __('messages.patient.error.onupdate'))->withInput();
        }

        return redirect()->route('patients.index')
            ->with('success', __('messages.patient.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $deletedPatientPicture = Patient::findOrFail($id)->first()->picture;
        if ($deletedPatientPicture) {
            $this->service->deleteImage($deletedPatientPicture);
        }

        $deleted = $this->service->deletePatient($id);
        if ($deleted instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($deleted->getMessage());

            return back()
                ->withErrors('error', __('messages.patient.error.ondelete'))->withInput();
        }

        return redirect()->route('patients.index')
            ->with('success', __('messages.patient.success.ondelete'));
    }
}
