<?php

namespace App\Services\Patient;

use App\Helpers\FileHelper;
use App\Services\Service;
use App\Types\FileMetadata;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MasterService extends Service
{
    public function storeImage(mixed $image): FileMetadata|Exception
    {
        return FileHelper::storeFile($image, 'uploads/images/patient', 'public');
    }

    public function deleteImage(mixed $image): bool|Exception
    {
        return FileHelper::deleteFile('public', $image);
    }

    private function initSelect(object $filter): Builder
    {
        $query = DB::table('patients')
            ->join('patient_addresses', 'patient_addresses.patient_id', '=', 'patients.id', 'left');

        if (isset($filter->keyword)) {
            $query->where(function ($query) use ($filter) {
                $query->whereRaw('lower(patients.name) like ?', ["%{$filter->keyword}%"])
                    ->orWhereRaw('lower(patients.code) like ?', ["%{$filter->keyword}%"])
                    ->orWhereRaw('lower(patients.phone) like ?', ["%{$filter->keyword}%"]);
            });
        }

        return $query;
    }

    public function readAllPatients(?object $filter = null): LengthAwarePaginator|Exception
    {
        try {
            return $this->initSelect($filter)
                ->paginate(20)
                ->withQueryString();
        } catch (Exception $err) {
            $this->writeLog('MasterService::readAllPatients', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function countData(?object $filter = null): int
    {
        try {
            return $this->initSelect($filter)->selectRaw('count(*) as counter')->first()->counter;
        } catch (Exception $err) {
            $this->writeLog('MasterService::countData', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function countTotalData(mixed $filter = null)
    {
        try {
            $patient = DB::table('patients')
                ->selectRaw('count(*) as counter');

            if (isset($filter->keyword)) {
                $patient->where(DB::raw('lower(patients.name)'), 'like', '%'.strtolower($filter->keyword).'%')
                    ->orWhere(DB::raw('lower(patients.code)'), 'like', '%'.strtolower($filter->keyword).'%')
                    ->orWhere(DB::raw('lower(patients.phone)'), 'like', '%'.strtolower($filter->keyword).'%');
            }

            return $patient->first();
        } catch (Throwable $th) {
            $this->writeLog('MasterService::countTotalData', $th);

            return new Collection;
        }
    }

    public function readPatientByFilter(mixed $filter = null)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            $patient = DB::table('patients')
                ->join('patient_addresses', 'patient_addresses.patient_id', '=', 'patients.id', 'left');

            if (isset($filter->keyword)) {
                $patient->where(DB::raw('lower(patients.name)'), 'like', '%'.strtolower($filter->keyword).'%')
                    ->orWhere(DB::raw('lower(patients.code)'), 'like', '%'.strtolower($filter->keyword).'%')
                    ->orWhere(DB::raw('lower(patients.phone)'), 'like', '%'.strtolower($filter->keyword).'%');
            }

            $patient->limit($limit)
                ->offset(($page - 1) * $limit);

            return $patient->get();
        } catch (Throwable $th) {
            $this->writeLog('MasterService::readPatientByFilter', $th);

            return new Collection;
        }
    }

    public function selectAllPatient(int $limit = 10, int $offset = 0): Collection
    {
        return DB::table('patients')
            ->join('patient_addresses', 'patient_addresses.patient_id', '=', 'patients.id', 'left')
            ->orderBy('code', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function selectPatientByID(string $id): ?object
    {
        return DB::table('patients')
            ->join('patient_addresses', 'patient_addresses.patient_id', '=', 'patients.id', 'left')
            ->select([
                'id',
                'name',
                'code',
                'email',
                'payment_email',
                'sosmed',
                'phone',
                'gender',
                'birthdate',
                'birth_place',
                'nik',
                'ihs_id',
                'satusehat_consent',
                'religion',
                'village',
                'street',
                'zip_code',
                'tonarigumi',
                'district',
                'regency',
                'province',
                'picture',
            ])
            ->where('patients.id', '=', $id)
            ->first();
    }

    public function getPatientBySearching(string $search): Collection
    {
        try {
            $query = DB::table('patients')
                ->select([
                    'id',
                    'name',
                    'code',
                    'phone',
                ]);

            if (isset($search)) {
                $query->orWhere('id', 'like', '%'.$search.'%');
                $query->orwhere('code', 'like', '%'.$search.'%');
                $query->orWhereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']);
                $query->orWhere('phone', 'like', '%'.$search.'%');
            }

            return $query->get();
        } catch (Exception $err) {
            $this->writeLog('MasterService::getPatientBySearching', $err);

            return new Collection;
        }
    }

    public function generatePatientCode(): string
    {
        $lastPatient = DB::table('patients')->orderBy('code', 'desc')->first();
        if ($lastPatient) {
            $lastCode = substr($lastPatient->code, 4);
            $lastCode = (int) $lastCode;
            $lastCode++;
            $lastCode = 'PX01'.str_pad($lastCode, 4, '0', STR_PAD_LEFT);
        } else {
            $lastCode = 'PX010001';
        }

        return $lastCode;
    }

    public function insertPatient(object $patient): string|Exception
    {
        try {
            $id = Str::uuid();
            $code = $this->generatePatientCode();

            DB::table('patients')
                ->insert([
                    'id' => $id,
                    'code' => $code,
                    'name' => $patient->name,
                    'email' => $patient->email,
                    'payment_email' => $patient->payment_email,
                    'sosmed' => $patient->input('sosmed'),
                    'phone' => $patient->input('phone'),
                    'birthdate' => $patient->birthdate,
                    'birth_place' => $patient->input('birth_place'),
                    'nik' => $patient->input('nik'),
                    'ihs_id' => $patient->input('ihs_id'),
                    'satusehat_consent' => $patient->boolean('satusehat_consent'),
                    'religion' => $patient->religion ?? 'OTHER',
                    'gender' => $patient->gender ?? 'MALE',
                    'picture' => $patient->input('picture'),
                ]);

            DB::table('patient_addresses')
                ->insert([
                    'patient_id' => $id,
                    'street' => $patient->street,
                    'village' => $patient->village,
                    'tonarigumi' => $patient->tonarigumi,
                    'district' => $patient->district,
                    'regency' => $patient->regency,
                    'province' => $patient->province,
                    'zip_code' => $patient->zip_code,
                ]);

            return $id;
        } catch (Throwable $th) {
            $this->writeLog('MasterService::insertPatient', $th);
            throw $th;
        }
    }

    public function updatePatient(object $patient, string $id): string|Exception
    {
        try {
            DB::table('patients')
                ->where('id', $id)
                ->update([
                    'name' => $patient->name,
                    'email' => $patient->email,
                    'payment_email' => $patient->payment_email,
                    'sosmed' => $patient->input('sosmed'),
                    'phone' => $patient->input('phone'),
                    'birthdate' => $patient->birthdate,
                    'birth_place' => $patient->input('birth_place'),
                    'nik' => $patient->input('nik'),
                    'ihs_id' => $patient->input('ihs_id'),
                    'satusehat_consent' => $patient->boolean('satusehat_consent'),
                    'religion' => $patient->religion ?? 'OTHER',
                    'gender' => $patient->gender ?? 'MALE',
                    'picture' => $patient->input('picture'),
                ]);

            DB::table('patient_addresses')
                ->where('patient_id', $id)
                ->update([
                    'street' => $patient->street,
                    'village' => $patient->village,
                    'tonarigumi' => $patient->tonarigumi,
                    'district' => $patient->district,
                    'regency' => $patient->regency,
                    'province' => $patient->province,
                    'zip_code' => $patient->zip_code,
                ]);

            return $id;
        } catch (Throwable $th) {
            $this->writeLog('MasterService::updatePatient', $th);
            throw $th;
        }
    }

    public function deletePatient(string $id): string|Exception
    {
        try {
            DB::table('patient_addresses')
                ->where('patient_id', '=', $id)
                ->delete();

            DB::table('patients')
                ->where('id', '=', $id)
                ->delete();

            return $id;
        } catch (Throwable $th) {
            $this->writeLog('MasterService::deletePatient', $th);
            throw $th;
        }
    }
}
