<?php

namespace App\Services\General;

use App\Services\Service;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class AppointmentService extends Service
{
    public function countTotalData(mixed $filter = null)
    {
        try {
            $appointment = DB::table('appointments')
                ->selectRaw('count(*) as counter');

            return $appointment->first();
        } catch (\Throwable $th) {
            $this->writeLog('AppointmentService::countTotalData', $th);

            return new Collection;
        }
    }

    public function countTotalDataDoctor(string $doctorID)
    {
        try {
            $appointment = DB::table('appointments')
                ->selectRaw('count(*) as counter')
                ->where('doctor_id', $doctorID);

            return $appointment->first();
        } catch (\Throwable $th) {
            $this->writeLog('AppointmentService::countTotalDataDoctor', $th);

            return new Collection;
        }
    }

    public function readAppointmentByFilter(mixed $filter = null)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            $appointment = DB::table('appointments')
                ->join('doctors', 'doctor_id', '=', 'doctors.user_id', 'left')
                ->select([
                    'appointments.id',
                    'patient_id',
                    'patient_name',
                    'patient_code',
                    'doctor_id',
                    'doctor_nipp',
                    'doctor_niptk',
                    'doctor_name',
                    'services',
                    'date',
                    'time_start',
                    'time_end',
                    'confirmed_at',
                    'paid_at',
                    'recorded_at',
                    'canceled_at',
                    'appointments.created_at',
                ])
                ->whereNull('canceled_at')
                ->orderBy('appointments.created_at', 'desc')
                ->limit($limit)
                ->offset(($page - 1) * $limit);

            return $appointment->get();
        } catch (\Throwable $th) {
            $this->writeLog('AppointmentService::readAppointmentByFilter', $th);

            return new Collection;
        }
    }

    public function readAppointmentDoctorByFilter(mixed $filter = null, string $doctorID)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            $appointment = DB::table('appointments')
                ->join('doctors', 'doctor_id', '=', 'doctors.user_id', 'left')
                ->select([
                    'appointments.id',
                    'patient_id',
                    'patient_name',
                    'patient_code',
                    'doctor_id',
                    'doctor_nipp',
                    'doctor_niptk',
                    'doctor_name',
                    'services',
                    'date',
                    'time_start',
                    'time_end',
                    'confirmed_at',
                    'paid_at',
                    'recorded_at',
                    'canceled_at',
                    'appointments.created_at',
                ])
                ->where('doctor_id', $doctorID)
                ->whereNull('canceled_at')
                ->orderBy('appointments.created_at', 'desc')
                ->limit($limit)
                ->offset(($page - 1) * $limit);

            return $appointment->get();
        } catch (\Throwable $th) {
            $this->writeLog('AppointmentService::readAppointmentDoctorByFilter', $th);

            return new Collection;
        }
    }

    public function readAllAppointments(?object $filter = null): Collection|Exception
    {
        try {
            $appointments = DB::table('appointments')
                ->join('doctors', 'doctor_id', '=', 'doctors.user_id', 'left')
                ->select([
                    'appointments.id',
                    'patient_id',
                    'patient_name',
                    'patient_code',
                    'doctor_id',
                    'doctor_nipp',
                    'doctor_niptk',
                    'doctor_name',
                    'services',
                    'date',
                    'time_start',
                    'time_end',
                    'confirmed_at',
                    'paid_at',
                    'recorded_at',
                    'canceled_at',
                    'appointments.created_at',
                ]);

            if (isset($filter->onlyConfirmed)) {
                $appointments->whereNull('recorded_at')
                    ->whereNull('canceled_at')
                    ->whereNotNull('confirmed_at');
            }

            if (isset($filter->doctorID) && $filter->doctorID) {
                $appointments->where('doctor_id', $filter->doctorID);
            }

            return $appointments->whereNull('canceled_at')
                ->get()
                ->map(function ($appointment) {
                    $appointment->services = json_decode($appointment->services);

                    return $appointment;
                });
        } catch (Exception $err) {
            $this->writeLog('AppointmentService::readAllAppointments', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readAppointmentByID(string $id): object
    {
        try {
            return DB::table('appointments')->where('appointments.id', $id)
                ->join('doctors', 'doctor_id', '=', 'doctors.user_id', 'left')
                ->first();
        } catch (Exception $err) {
            $this->writeLog('AppointmentService::readAppointmentByID', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readDetailAppointmentByID(string $id): object
    {
        try {
            return DB::table('appointments')->where('appointments.id', $id)
                ->join('patient_addresses', 'appointments.patient_id', '=', 'patient_addresses.patient_id', 'left')
                ->join('doctors', 'appointments.doctor_id', '=', 'doctors.user_id', 'left')
                ->join('users', 'appointments.doctor_id', '=', 'users.id', 'left')
                ->select([
                    'appointments.id as id',
                    'appointments.patient_id',
                    'patient_code',
                    'patient_name',
                    'patient_addresses.street as patient_street',
                    'patient_addresses.tonarigumi as patient_tonarigumi',
                    'patient_addresses.village as patient_village',
                    'patient_addresses.district as patient_district',
                    'patient_addresses.regency as patient_regency',
                    'patient_addresses.province as patient_province',
                    'patient_addresses.zip_code as patient_zip_code',
                    'appointments.doctor_id as doctor_id',
                    'users.name as doctor_name',
                    'doctors.nipp as doctor_nipp',
                    'doctors.niptk as doctor_niptk',
                    'services',
                    'appointments.date as date',
                    'appointments.time_start as time_start',
                    'appointments.time_end as time_end',
                    'appointments.confirmed_at',
                    'appointments.paid_at',
                    'appointments.recorded_at',
                    'appointments.canceled_at',
                ])
                ->first();
        } catch (Exception $err) {
            $this->writeLog('AppointmentService::readDetailAppointmentByID', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function patchAppointmentStatus($status, $id)
    {
        try {
            $query = DB::table('appointments')->where('id', '=', $id);

            if ($status == 'CONFIRMED') {
                return $query->update(['confirmed_at' => date('Y-m-d H:i:s')]);
            }

            if ($status == 'RECORDED') {
                return $query->update(['recorded_at' => date('Y-m-d H:i:s')]);
            }

            if ($status == 'PAID') {
                return $query->update(['paid_at' => date('Y-m-d H:i:s')]);
            }

            return $query->update(['canceled_at' => date('Y-m-d H:i:s')]);
        } catch (Exception $err) {
            $this->writeLog('AppointmentService::patchAppointmentStatus', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function createAppointment(mixed $valid): bool|Exception
    {
        try {
            return DB::table('appointments')->insert([
                'id' => Uuid::uuid4(),
                'branch_id' => \App\Helpers\BranchContext::currentId() ?? \App\Helpers\BranchContext::defaultId(),
                'patient_id' => $valid['patient_id'],
                'patient_code' => $valid['patient_code'],
                'patient_name' => $valid['patient_name'],
                'patient_phone' => $valid['patient_phone'],
                'doctor_id' => $valid['doctor_id'],
                'doctor_nipp' => $valid['doctor_nipp'],
                'doctor_niptk' => $valid['doctor_niptk'],
                'doctor_name' => $valid['doctor_name'],
                'date' => $valid['date'],
                'time_start' => $valid['start_time'],
                'time_end' => $valid['end_time'],
                'services' => $valid['services'],
            ]);
        } catch (Exception $err) {
            $this->writeLog('AppointmentService::patchAppointmentStatus', $err);

            return false;
        }
    }

    public function updateAppointment(mixed $valid, string $id): bool|Exception
    {
        try {
            return DB::table('appointments')->where('id', $id)->update([
                'doctor_id' => $valid['doctor_id'],
                'doctor_nipp' => $valid['doctor_nipp'],
                'doctor_niptk' => $valid['doctor_niptk'],
                'doctor_name' => $valid['doctor_name'],
                'services' => $valid['services'],
                'date' => $valid['date'],
                'time_start' => $valid['start_time'],
                'time_end' => $valid['end_time'],
            ]);
        } catch (Exception $err) {
            $this->writeLog('AppointmentService::updateAppointment', $err);

            return new Exception($err->getMessage(), 500);
        }
    }
}
