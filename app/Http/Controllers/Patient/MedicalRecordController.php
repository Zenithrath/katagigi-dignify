<?php

namespace App\Http\Controllers\Patient;

use App\Helpers\Audit;
use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalRecordRequest;
use App\Models\DiagnosisCode;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAddendum;
use App\Services\General\AppointmentService;
use App\Services\General\ServiceService;
use App\Services\Master\DoctorService;
use App\Services\OptionService;
use App\Services\Patient\MasterService;
use App\Services\Patient\MedicalRecordService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MedicalRecordController extends Controller
{
    private $service;

    private $appointmentService;

    private $serviceService;

    private $doctorService;

    private $patientService;

    private $optionService;

    public function __construct()
    {
        $this->service = new MedicalRecordService;
        $this->appointmentService = new AppointmentService;
        $this->serviceService = new ServiceService;
        $this->doctorService = new DoctorService;
        $this->patientService = new MasterService;
        $this->optionService = new OptionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('read medical record');

        return view('pages.patient.record.index', [
            'medicalRecordList' => $this->service->readAllMedicalRecords($request),
        ]);
    }

    public function lookup(Request $request)
    {
        $this->authorize('read medical record');
        $total = $this->service->countTotalData($request);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->service->readMedicalRecordByFilter($request),
            'pagination' => $pagination,
        ], 200);
    }

    public function lookupMedicalHistory(Request $request, string $patient_id)
    {
        $this->authorize('read patient');
        $request['sort'] = 'asc';
        $total = $this->service->countTotalDataHistory($request, $patient_id);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $this->service->readMedicalRecordByPatient($request, $patient_id)->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->locale('id')->setTimezone('Asia/Jakarta')->isoFormat('dddd, DD MMMM YYYY HH:mm ZZ');
                $item->image_before = json_decode($item->image_before);
                $item->image_after = json_decode($item->image_after);

                return $item;
            }),
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
        $this->authorize('create medical record');
        $services = $this->optionService->getServiceList();
        if (Auth::user()->hasRole('doctor')) {
            $doctorID = Auth::user()->id;
        }
        $appointments = $this->appointmentService->readAllAppointments((object) [
            'onlyConfirmed' => true,
            'doctorID' => $doctorID ?? null,
        ]);

        return view('pages.patient.record.form', [
            'type' => 'create',
            'record' => new MedicalRecord,
            'services' => $services,
            'appointments' => $appointments,
            'action' => route('medical-records.store'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(MedicalRecordRequest $request)
    {
        $this->authorize('create medical record');
        $validated = $request->validated();
        $appointment = $this->appointmentService->readAppointmentByID($validated['appointment_id']);
        $patient_id = $appointment->patient_id;
        $patient = $this->patientService->selectPatientByID($patient_id);
        abort_if(! $patient, 404, 'Pasien tidak ditemukan.');
        $validated['patient_id'] = $patient_id;
        $validated['patient_name'] = $patient->name;
        $validated['patient_code'] = $patient->code;
        $validated['patient_phone'] = $patient->phone;
        $validated['patient_address'] = sprintf(
            "%s %s\n%s, %s %s\n%s - %s",
            $patient->street,
            $patient->tonarigumi,
            $patient->village,
            $patient->district,
            $patient->zip_code,
            $patient->regency,
            $patient->province
        );

        $validated['doctor_id'] = $appointment->doctor_id;
        $validated['doctor_name'] = $appointment->doctor_name;
        $validated['doctor_nipp'] = $appointment->doctor_nipp;
        $validated['doctor_niptk'] = $appointment->doctor_niptk;

        $validated['appointment_date'] = $appointment->date;
        $validated['time_start'] = $appointment->time_start;
        $validated['time_end'] = $appointment->time_end;

        $services = $this->serviceService->readServicesByIDList($request['service_id']);

        if ($services instanceof Exception) {
            Log::error($services->getMessage());

            return redirect()->back()
                ->with('error', __('messages.medical-record.error.oncreate'))
                ->withInput();
        }

        $pricedServices = array_map(function ($id, $price, $quantity, $discount) use ($services) {
            $index = array_search($id, array_column($services->toArray(), 'id'));

            return [
                'id' => $id,
                'price' => (float) $price,
                'quantity' => (float) $quantity,
                'subtotal' => (float) $price * $quantity,
                'discount' => (float) $discount ?? 0,
                'code' => $services[$index]->code,
                'name' => $services[$index]->name,
                'category' => $services[$index]->category_name,
            ];
        }, $validated['service_id'], $validated['service_price'], $validated['service_quantity'], $validated['service_discount']);

        $validated['services'] = $pricedServices;

        $imageBeforeRequest = $request->file('image_before') ?? null;
        $imageAfterRequest = $request->file('image_after') ?? null;

        $imageBeforePath = [];
        $imageAfterPath = [];
        if (is_array($imageBeforeRequest)) {
            foreach ($imageBeforeRequest as $image) {
                $imageBefore = $this->service->storeImageBefore($image); //  set to json
                array_push($imageBeforePath, $imageBefore->path);
            }
        }

        if (is_array($imageAfterRequest)) {
            foreach ($imageAfterRequest as $image) {
                $imageAfter = $this->service->storeImageAfter($image); //  set to json
                array_push($imageAfterPath, $imageAfter->path);
            }
        }

        $validated['image_before'] = $imageBeforePath;
        $validated['image_after'] = $imageAfterPath;

        $inserted = $this->service->insertMedicalRecord($validated);
        if ($inserted instanceof Exception) {
            Log::error($inserted->getMessage());

            return redirect()->back()
                ->with('error', __('messages.medical-record.error.oncreate'))
                ->withInput();
        }

        Audit::log('create-medical-record', 'medical_record', $inserted, null, ['patient_id' => $patient_id, 'appointment_id' => $validated['appointment_id']]);

        // D-03: gabung diagnosis ICD-10 + tindakan ICD-9 ke pivot (satu sumber
        // riwayat). Kode sistem lain yang sudah tersimpan dipertahankan sync.
        $this->syncDiagnosisCodes($inserted, array_merge(
            $validated['diagnosis_codes_icd10'] ?? [],
            $validated['procedure_codes_icd9'] ?? []
        ));

        return redirect()->route('medical-records.index')
            ->with('success', __('messages.medical-record.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('read medical record');
        $toRupiah = function ($value) {
            return GeneralHelper::floatToRupiah($value);
        };

        $allCodes = DB::table('medical_record_diagnoses')
            ->where('medical_record_id', $id)
            ->orderBy('system')
            ->orderBy('code')
            ->get();

        return view('pages.patient.record.detail', [
            'data' => $this->service->readMedicalRecordByID($id),
            'toRupiah' => $toRupiah,
            'diagnosisCodesIcd10' => $allCodes->where('system', 'ICD10')->values(),
            'procedureCodesIcd9' => $allCodes->where('system', 'ICD9')->values(),
            'otherCodes' => $allCodes->whereNotIn('system', ['ICD10', 'ICD9'])->values(),
            'addendums' => MedicalRecordAddendum::where('model_type', 'MedicalRecord')
                ->where('model_id', $id)
                ->orderBy('created_at', 'desc')
                ->get(),
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
        $this->authorize('update medical record');
        $services = $this->optionService->getServiceList();

        $record = $this->service->readMedicalRecordByID($id);
        $appointments = [
            $this->appointmentService->readAppointmentByID($record->appointment_id),
        ];

        $codedRows = DB::table('medical_record_diagnoses')
            ->join('diagnosis_codes', 'diagnosis_codes.id', '=', 'medical_record_diagnoses.diagnosis_code_id')
            ->where('medical_record_diagnoses.medical_record_id', $id)
            ->select([
                'diagnosis_codes.id',
                'diagnosis_codes.system',
                'diagnosis_codes.code',
                'diagnosis_codes.display_id',
            ])
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'system' => $row->system,
                'code' => $row->code,
                'display_id' => $row->display_id,
            ]);

        return view('pages.patient.record.form', [
            'type' => 'update',
            'record' => $record,
            'services' => $services,
            'appointments' => $appointments,
            'diagnosisCodesIcd10' => $codedRows->where('system', 'ICD10')->values()->all(),
            'procedureCodesIcd9' => $codedRows->where('system', 'ICD9')->values()->all(),
            'action' => route('medical-records.update', ['medical_record' => $id]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(MedicalRecordRequest $request, $id)
    {
        $this->authorize('update medical record');
        $beforeImageInput = [];
        foreach ($request->image_before_meta as $index => $image) {
            if (str_contains($image, 'blob:')) {
                $data = $this->service->storeImageBefore($request->image_before[$index]);
                if ($data instanceof Exception) {
                    return;
                }
                $image = $data->path;
            }

            array_push($beforeImageInput, str_replace('/storage/', '', $image));
        }

        $afterImageInput = [];
        foreach ($request->image_after_meta as $index => $image) {
            if (str_contains($image, 'blob:')) {
                $data = $this->service->storeImageAfter($request->image_after[$index]);
                if ($data instanceof Exception) {
                    return;
                }
                $image = $data->path;
            }

            array_push($afterImageInput, str_replace('/storage/', '', $image));
        }

        $images = $this->service->readImageMetaByID($id);

        $beforeImages = json_decode($images->image_before);
        $afterImages = json_decode($images->image_after);

        foreach ($beforeImages as $index => $image) {
            if (in_array($image, $beforeImageInput)) {
                continue;
            }
            $image = $this->service->deleteImageBefore($image);
        }

        foreach ($afterImages as $index => $image) {
            if (in_array($image, $afterImageInput)) {
                continue;
            }
            $image = $this->service->deleteImageAfter($image);
        }

        $services = $this->serviceService->readServicesByIDList($request['service_id']);

        if ($services instanceof Exception) {
            Log::error($services->getMessage());

            return redirect()->back()
                ->with('error', __('messages.medical-record.error.onupdate'))
                ->withInput();
        }

        $pricedServices = array_map(function ($id, $price, $quantity, $discount) use ($services) {
            $index = array_search($id, array_column($services->toArray(), 'id'));

            return (object) [
                'id' => $id,
                'price' => (float) $price,
                'quantity' => $quantity,
                'discount' => (float) $discount ?? 0,
                'subtotal' => (float) $price * (int) $quantity,
                'code' => $services[$index]->code,
                'name' => $services[$index]->name,
                'category' => $services[$index]->category_name,
            ];
        }, $request->service_id, $request->service_price, $request->service_quantity, $request->service_discount);

        $request->replace([
            ...$request->all(),
            'services' => $pricedServices,
            'image_before' => $beforeImageInput,
            'image_after' => $afterImageInput,
        ]);

        $updated = $this->service->update($id, $request);

        if ($updated instanceof Throwable) {
            Log::error($updated->getMessage());

            return redirect()->back()
                ->with('error', __('messages.medical-record.error.onupdate'))
                ->withInput();
        }

        // D-03: sinkronkan ulang diagnosis ICD-10 + tindakan ICD-9.
        $this->syncDiagnosisCodes($id, array_merge(
            $request->input('diagnosis_codes_icd10', []),
            $request->input('procedure_codes_icd9', [])
        ));

        // Permenkes 24/2022: koreksi RME SIGNED wajib teraudit (old/new).
        Audit::log('update-medical-record', 'medical_record', $id, $this->service->readMedicalRecordByID($id)?->diagnosis ? ['diagnosis' => $this->service->readMedicalRecordByID($id)->diagnosis] : null, ['diagnosis' => $request->input('diagnosis')], $request->input('audit_reason'));

        return redirect()->route('medical-records.show', ['medical_record' => $id])
            ->with('success', __('messages.medical-record.success.onupdate'));
    }

    /**
     * D-03: simpan/timpa kode diagnosis resmi rekam medis beserta snapshot
     * (agar riwayat tetap benar walau master berubah). Dipanggil setelah
     * insert/update; daftar kode sudah tervalidasi per sistem di Request.
     * Baris sistem lain (mis. SNOMED lama) dipertahankan, tidak dihapus.
     */
    private function syncDiagnosisCodes(string $recordId, array $codeIds): void
    {
        DB::table('medical_record_diagnoses')
            ->where('medical_record_id', $recordId)
            ->whereIn('system', ['ICD10', 'ICD9'])
            ->delete();

        $codes = DiagnosisCode::whereIn('id', array_unique($codeIds))->get();
        $now = now();
        $rows = $codes->map(fn ($code) => [
            'id' => (string) Str::uuid(),
            'medical_record_id' => $recordId,
            'diagnosis_code_id' => $code->id,
            'system' => $code->system,
            'code' => $code->code,
            'display' => $code->display_id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows !== []) {
            DB::table('medical_record_diagnoses')->insert($rows);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete medical record');
        $medicalRecord = $this->service->readMedicalRecordByID($id);
        $imageBefore = $medicalRecord->image_before;
        $imageAfter = $medicalRecord->image_after;

        foreach ($imageBefore as $image) {
            $this->service->deleteImageBefore($image);
        }

        foreach ($imageAfter as $image) {
            $this->service->deleteImageAfter($image);
        }

        // Permenkes 24/2022: rekam medis tidak boleh hard delete.
        // Gunakan soft delete + addendum.
        $record = MedicalRecord::find($id);
        if (! $record) {
            return redirect()->route('medical-records.index')
                ->with('error', 'Rekam medis tidak ditemukan.');
        }

        $record->delete();
        Audit::log('soft-delete-medical-record', 'medical_record', $id, null, ['deleted' => true], request()->input('audit_reason'));

        return redirect()->route('medical-records.index')
            ->with('success', 'Rekam medis diarsipkan (soft delete). Data tetap tersimpan untuk audit.');
    }
}
