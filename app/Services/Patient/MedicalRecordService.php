<?php

namespace App\Services\Patient;

use App\Helpers\FileHelper;
use App\Services\Service;
use App\Types\FileMetadata;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MedicalRecordService extends Service
{
    public function storeImageBefore(mixed $image): FileMetadata|Exception
    {
        return FileHelper::storeFile($image, 'uploads/images/medical_records/before', 'public');
    }

    public function storeImageAfter(mixed $image): FileMetadata|Exception
    {
        return FileHelper::storeFile($image, 'uploads/images/medical_records/after', 'public');
    }

    public function deleteImageBefore(mixed $image): bool|Exception
    {
        return FileHelper::deleteFile('public', $image);
    }

    public function deleteImageAfter(mixed $image): bool|Exception
    {
        return FileHelper::deleteFile('public', $image);
    }

    private function initSelect(object $filter): Builder
    {
        $query = DB::table('medical_records')
            ->join('patients', 'medical_records.patient_id', '=', 'patients.id')
            ->orderBy('medical_records.created_at', 'desc');

        if (isset($filter->since)) {
            $query->where('created_at', '>=', $filter->since);
        }

        if (isset($filter->until)) {
            $query->where('created_at', '<=', $filter->until);
        }

        if (Auth::user()->hasRole('doctor') && empty($filter->keyword)) {
            $query->where('doctor_id', Auth::user()->id);
        }

        if (isset($filter->keyword)) {
            $keyword = $filter->keyword;
            $query->where(function ($query) use ($keyword) {
                $query->whereRaw('LOWER(patient_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                    ->orWhereRaw('LOWER(patients.name) LIKE ?', ['%'.strtolower($keyword).'%'])
                    ->orWhereRaw('LOWER(doctor_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                    ->orWhereRaw('LOWER(patient_code) LIKE ?', ['%'.strtolower($keyword).'%'])
                    ->orWhereRaw('LOWER(patient_phone) LIKE ?', ['%'.strtolower($keyword).'%'])
                    ->orWhereRaw('LOWER(patient_address) LIKE ?', ['%'.strtolower($keyword).'%'])
                    ->orWhereRaw('LOWER(medical_records.services) LIKE ?', ['%'.strtolower($keyword).'%']);
            });
        }

        $query->select([
            'medical_records.id',
            'medical_records.patient_id',
            'medical_records.patient_code',
            'patients.name as patient_name',
            'medical_records.patient_address',
            'medical_records.patient_phone',
            'medical_records.doctor_id',
            'medical_records.doctor_name',
            'medical_records.doctor_nipp',
            'medical_records.doctor_niptk',
            'medical_records.appointment_id',
            'medical_records.appointment_date',
            'medical_records.time_start',
            'medical_records.time_end',
            'medical_records.services',
            'medical_records.checkup_result',
            'medical_records.anamnesis',
            'medical_records.diagnosis',
            'medical_records.therapy',
            'medical_records.prescription',
            'medical_records.next_schedule',
        ]);

        return $query;
    }

    public function readAllMedicalRecords(?object $filter = null): LengthAwarePaginator|Exception
    {
        try {
            return $this->initSelect($filter)
                ->paginate(20)
                ->withQueryString();
        } catch (Exception $err) {
            $this->writeLog('MedicalRecordService::readAllMedicalRecords', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function countTotalData(mixed $filter = null)
    {
        $keyword = $filter->keyword ?? '';

        try {
            $record = DB::table('medical_records')
                ->join('patients', 'medical_records.patient_id', '=', 'patients.id')
                ->selectRaw('count(*) as counter');

            if (isset($filter->since)) {
                $record->where('created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $record->where('created_at', '<=', $filter->until);
            }

            if (Auth::user()->hasRole('doctor')) {
                $record->where('doctor_id', Auth::user()->id);
            }

            if (isset($filter->keyword)) {
                $record->where(function ($query) use ($keyword) {
                    $query->whereRaw('LOWER(patient_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patients.name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(doctor_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patient_code) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patient_phone) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patient_address) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(medical_records.services) LIKE ?', ['%'.strtolower($keyword).'%']);
                });
            }

            return $record->first();
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByKeyword', $th);

            return (object) [
                'counter' => 0,
            ];
        }
    }

    public function countTotalDataHistory(mixed $filter = null, string $patient_id)
    {
        $keyword = $filter->keyword ?? '';

        try {
            $record = DB::table('medical_records')
                ->join('patients', 'medical_records.patient_id', '=', 'patients.id')
                ->where('medical_records.patient_id', $patient_id)
                ->selectRaw('count(*) as counter');

            if (isset($filter->since)) {
                $record->where('created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $record->where('created_at', '<=', $filter->until);
            }

            if (Auth::user()->hasRole('doctor')) {
                $record->where('doctor_id', Auth::user()->id);
            }

            if (isset($filter->keyword)) {
                $record->where(function ($query) use ($keyword) {
                    $query->whereRaw('LOWER(doctor_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(medical_records.services) LIKE ?', ['%'.strtolower($keyword).'%']);
                });
            }

            return $record->first();
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByKeyword', $th);

            return (object) [
                'counter' => 0,
            ];
        }
    }

    public function readMedicalRecordByFilter(mixed $filter = null)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;
        $keyword = $filter->keyword ?? '';

        try {
            $record = DB::table('medical_records')
                ->join('patients', 'medical_records.patient_id', '=', 'patients.id')
                ->limit($limit)
                ->orderBy('medical_records.created_at', 'desc')
                ->offset(($page - 1) * $limit);

            if (isset($filter->since)) {
                $record->where('medical_records.created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $record->where('medical_records.created_at', '<=', $filter->until);
            }

            if (Auth::user()->hasRole('doctor') && empty($filter->keyword)) {
                $record->where('doctor_id', Auth::user()->id);
            }

            if (isset($filter->keyword)) {
                $record->where(function ($query) use ($keyword) {
                    $query->whereRaw('LOWER(patient_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patients.name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(doctor_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patient_code) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patient_phone) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(patient_address) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(medical_records.services) LIKE ?', ['%'.strtolower($keyword).'%']);
                });
            }

            // dd($record->toSql());
            return $record->get([
                'medical_records.id',
                'medical_records.patient_id',
                'medical_records.patient_code',
                'patients.name as patient_name',
                'medical_records.patient_address',
                'medical_records.patient_phone',
                'medical_records.doctor_id',
                'medical_records.doctor_name',
                'medical_records.doctor_nipp',
                'medical_records.doctor_niptk',
                'medical_records.appointment_id',
                'medical_records.appointment_date',
                'medical_records.time_start',
                'medical_records.time_end',
                'medical_records.services',
                'medical_records.checkup_result',
                'medical_records.anamnesis',
                'medical_records.diagnosis',
                'medical_records.therapy',
                'medical_records.prescription',
                'medical_records.next_schedule',
            ]);
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByKeyword', $th);

            return new Collection;
        }
    }

    public function readMedicalRecordByPatient(mixed $filter = null, string $patient_id)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;
        $keyword = $filter->keyword ?? '';
        $sort = isset($filter->sort) ? $filter->sort : 'desc';

        try {
            $record = DB::table('medical_records')
                ->join('patients', 'medical_records.patient_id', '=', 'patients.id')
                ->where('medical_records.patient_id', $patient_id)
                ->limit($limit)
                ->orderBy('medical_records.created_at', $sort)
                ->offset(($page - 1) * $limit);

            if (isset($filter->since)) {
                $record->where('medical_records.created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $record->where('medical_records.created_at', '<=', $filter->until);
            }

            if (Auth::user()->hasRole('doctor') && empty($filter->keyword)) {
                $record->where('doctor_id', Auth::user()->id);
            }

            if (isset($filter->keyword)) {
                $record->where(function ($query) use ($keyword) {
                    $query->whereRaw('LOWER(doctor_name) LIKE ?', ['%'.strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(medical_records.services) LIKE ?', ['%'.strtolower($keyword).'%']);
                });
            }

            // dd($record->toSql());
            return $record->get([
                'medical_records.id',
                'medical_records.patient_id',
                'medical_records.patient_code',
                'patients.name as patient_name',
                'medical_records.patient_address',
                'medical_records.patient_phone',
                'medical_records.doctor_id',
                'medical_records.doctor_name',
                'medical_records.doctor_nipp',
                'medical_records.doctor_niptk',
                'medical_records.appointment_id',
                'medical_records.appointment_date',
                'medical_records.time_start',
                'medical_records.time_end',
                'medical_records.services',
                'medical_records.checkup_result',
                'medical_records.anamnesis',
                'medical_records.diagnosis',
                'medical_records.therapy',
                'medical_records.prescription',
                'medical_records.next_schedule',
                'medical_records.created_at',
                'medical_records.image_before',
                'medical_records.image_after',
            ]);
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByKeyword', $th);

            return new Collection;
        }
    }

    public function readAllUnservedAppointments(): Collection
    {
        try {
            return DB::table('appointments')->get();
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::readAllUnservedAppointments', $th);

            return new Collection;
        }
    }

    public function readAllAvailableServices(): Collection
    {
        try {
            return DB::table('services')
                ->join('categories', 'services.category_id', '=', 'categories.id')
                ->select([
                    'services.id',
                    'services.name',
                    'categories.name as category',
                    'services.code',
                    'services.lower_price',
                    'services.upper_price',
                ])
                ->get();
        } catch (Throwable $th) {
            $this->writeLog('ServiceService::readAllAvailableServices', $th);

            return new Collection;
        }
    }

    public function readMedicalRecordByID(string $id): ?object
    {
        try {
            $record =
                DB::table('medical_records')->where('id', $id)->first();
            $record->services = json_decode($record->services);
            $record->image_before = json_decode($record->image_before);
            $record->image_after = json_decode($record->image_after);

            return $record;
        } catch (Exception $err) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByID', $err);

            return null;
        }
    }

    public function readMedicalRecordByPatiendID(string $id): Collection
    {
        try {
            $records = DB::table('medical_records')->where('patient_id', $id)->get();
            $data = [];
            foreach ($records as $record) {
                $record->services = json_decode($record->services);
                $record->image_before = json_decode($record->image_before);
                $record->image_after = json_decode($record->image_after);
                array_push($data, $record);
            }

            return collect($data)->sortBy('appointment_date');
        } catch (Exception $err) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByPatiendID', $err);

            return new Collection;
        }
    }

    public function readMedicalRecordByPatiendIdOrdered(string $id): Collection
    {
        try {
            $records = DB::table('medical_records')
                ->select([
                    'id',
                    'created_at',
                ])
                ->where('patient_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return $records;
        } catch (Exception $err) {
            $this->writeLog('MedicalRecordService::readMedicalRecordByPatiendID', $err);

            return new Collection;
        }
    }

    public function insertMedicalRecord(mixed $valid): bool|Exception
    {
        try {
            $saved = DB::table('medical_records')->insert([
                'id' => Str::uuid(),
                'patient_id' => $valid['patient_id'],
                'patient_code' => $valid['patient_code'],
                'patient_name' => $valid['patient_name'],
                'patient_address' => $valid['patient_address'],
                'patient_phone' => $valid['patient_phone'],
                'doctor_id' => $valid['doctor_id'],
                'doctor_name' => $valid['doctor_name'],
                'doctor_nipp' => $valid['doctor_nipp'],
                'doctor_niptk' => $valid['doctor_niptk'],
                'appointment_id' => $valid['appointment_id'],
                'appointment_date' => $valid['appointment_date'],
                'time_start' => $valid['time_start'],
                'time_end' => $valid['time_end'],
                'services' => json_encode($valid['services']),
                'checkup_result' => $valid['checkup_result'],
                'anamnesis' => $valid['anamnesis'],
                'diagnosis' => $valid['diagnosis'],
                'therapy' => $valid['therapy'],
                'prescription' => $valid['prescription'],
                'next_schedule' => $valid['next_schedule'],
                'discount' => $valid['discount'],
                'billing' => $valid['billing'],
                'price' => $valid['price'],
                'promat' => $valid['promat'],
                'blood_pressure' => $valid['blood_pressure'],
                'cooperativity' => $valid['cooperativity'],
                'image_before' => json_encode($valid['image_before']),
                'image_after' => json_encode($valid['image_after']),
            ]);

            return DB::table('appointments')->where('id', $valid['appointment_id'])->update([
                'recorded_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $err) {
            $this->writeLog('MedicalRecordService::insertMedicalRecord', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readImageMetaByID($id): object
    {
        try {
            return DB::table('medical_records')->where('id', $id)
                ->select(['image_before', 'image_after'])
                ->first();
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::readImageMetaByID', $th);
            throw $th;
        }
    }

    public function update(string $id, mixed $input): bool|Throwable
    {
        try {
            return DB::table('medical_records')
                ->where('id', $id)->update([
                    'services' => json_encode($input->services),
                    'checkup_result' => $input->checkup_result,
                    'anamnesis' => $input->anamnesis,
                    'diagnosis' => $input->diagnosis,
                    'therapy' => $input->therapy,
                    'prescription' => $input->prescription,
                    'next_schedule' => $input->next_schedule,
                    'discount' => $input->discount,
                    'billing' => $input->billing,
                    'price' => $input->price,
                    'promat' => $input->promat,
                    'blood_pressure' => $input->blood_pressure,
                    'cooperativity' => $input->cooperativity,
                    'image_before' => json_encode($input->input('image_before')),
                    'image_after' => json_encode($input->input('image_after')),
                ]);
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::update', $th);
            throw $th;
        }
    }

    public function deleteMedicalRecord(string $id): bool|Exception
    {
        try {
            $appointmentID = DB::table('medical_records')->where('id', $id)
                ->first('appointment_id')->appointment_id;
            DB::table('appointments')->where('id', $appointmentID)->update([
                'recorded_at' => null,
            ]);
            DB::table('medical_records')->where('id', $id)->delete();

            return true;
        } catch (Exception $err) {
            $this->writeLog('MedicalRecordService::deleteMedicalRecord', $err);

            return new Exception($err->getMessage(), 500);
        }
    }
}
