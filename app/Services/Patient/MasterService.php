<?php

namespace App\Services\Patient;

use App\Helpers\BranchContext;
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

    /**
     * Kriteria pasien "data lengkap" (siap bridging SATUSEHAT): NIK 16 digit,
     * no. HP, tanggal lahir, alamat, dan consent. Dipakai bersama master
     * pasien + dashboard agar definisi kelengkapan konsisten.
     */
    public static function applyCompleteCriteria(Builder $query): void
    {
        $query
            ->whereNotNull('patients.nik')
            ->where('patients.nik', '!=', '')
            ->whereRaw('length(patients.nik) = 16')
            ->whereNotNull('patients.phone')
            ->where('patients.phone', '!=', '')
            ->whereNotNull('patients.birthdate')
            ->where(function ($q) {
                $q->where(function ($a) {
                    $a->whereNotNull('patient_addresses.street')->where('patient_addresses.street', '!=', '');
                })->orWhere(function ($a) {
                    $a->whereNotNull('patient_addresses.village')->where('patient_addresses.village', '!=', '');
                });
            })
            ->where('patients.satusehat_consent', true);
    }

    /**
     * Daftar field yang belum diisi untuk satu baris pasien (badge UI).
     */
    public static function missingFields(object $patient): array
    {
        $missing = [];
        if (empty($patient->nik) || strlen((string) $patient->nik) !== 16) {
            $missing[] = 'NIK';
        }
        if (empty($patient->phone)) {
            $missing[] = 'No. HP';
        }
        if (empty($patient->birthdate)) {
            $missing[] = 'Tgl Lahir';
        }
        if (empty($patient->street) && empty($patient->village)) {
            $missing[] = 'Alamat';
        }
        if (empty($patient->satusehat_consent)) {
            $missing[] = 'Consent';
        }

        return $missing;
    }

    public function readAllPatients(?object $filter = null): LengthAwarePaginator|Exception
    {
        try {
            $query = $this->initSelect($filter)
                ->orderByDesc('patients.created_at');

            if (isset($filter->completeness)) {
                if ($filter->completeness === 'complete') {
                    $query->where(fn ($q) => static::applyCompleteCriteria($q));
                } elseif ($filter->completeness === 'incomplete') {
                    $query->whereNot(fn ($q) => static::applyCompleteCriteria($q));
                }
            }

            return $query->paginate(20)->withQueryString();
        } catch (Exception $err) {
            $this->writeLog('MasterService::readAllPatients', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    /**
     * Jumlah pasien per kelompok kelengkapan (untuk tab master pasien).
     */
    public function countByCompleteness(?object $filter = null): array
    {
        $base = $this->initSelect($filter ?? (object) []);
        $complete = (clone $base)
            ->where(fn ($q) => static::applyCompleteCriteria($q))
            ->count();
        $incomplete = (clone $base)
            ->whereNot(fn ($q) => static::applyCompleteCriteria($q))
            ->count();

        return ['complete' => $complete, 'incomplete' => $incomplete];
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
            ->leftJoin('master_insurances', 'master_insurances.id', '=', 'patients.insurance_id')
            ->select([
                'patients.id as id',
                'patients.name as name',
                'patients.code as code',
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
                'marital_status',
                'insurance_id',
                'insurance_number',
                'master_insurances.name as insurance_name',
                'village',
                'street',
                'zip_code',
                'tonarigumi',
                'district',
                'regency',
                'province',
                'patient_addresses.region_code as region_code',
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
                    'branch_id' => BranchContext::currentId() ?? BranchContext::defaultId(),
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
                    'marital_status' => $patient->input('marital_status') ?: null,
                    'insurance_id' => $patient->input('insurance_id') ?: null,
                    'insurance_number' => $patient->input('insurance_number') ?: null,
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
                    'region_code' => $patient->input('region_code'),
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
                    'marital_status' => $patient->input('marital_status') ?: null,
                    'insurance_id' => $patient->input('insurance_id') ?: null,
                    'insurance_number' => $patient->input('insurance_number') ?: null,
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
                    'region_code' => $patient->input('region_code'),
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
