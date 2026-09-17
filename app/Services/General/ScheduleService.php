<?php

namespace App\Services\General;

use App\Services\Service;
use App\Types\Entities\ScheduleEntity;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ScheduleService extends Service
{
    public function countTotalData()
    {
        try {
            $schedule = DB::table('schedules')
                ->selectRaw('count(*) as counter');

            return $schedule->first();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::countTotalData', $th);

            return new Collection;
        }
    }

    public function countTotalDataDoctor(string $doctorID)
    {
        try {
            $schedule = DB::table('schedules')
                ->selectRaw('count(*) as counter')
                ->where('doctor_id', $doctorID);

            return $schedule->first();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::countTotalDataDoctor', $th);

            return new Collection;
        }
    }

    public function readScheduleByFilter(mixed $filter = null)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            $schedule = DB::table('schedules')
                ->join('doctors', 'doctors.user_id', '=', 'schedules.doctor_id', 'left')
                ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
                ->select([
                    'schedules.id AS id',
                    'doctors.user_id AS doctor_id',
                    'name',
                    'day',
                    'availability',
                    'time_start',
                    'time_end',
                ])
                ->orderBy('name', 'asc')
                ->limit($limit)
                ->offset(($page - 1) * $limit);

            return $schedule->get();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::readScheduleByFilter', $th);

            return new Collection;
        }
    }

    public function readScheduleDoctorByFilter(mixed $filter = null, string $doctorID)
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            $schedule = DB::table('schedules')
                ->join('doctors', 'doctors.user_id', '=', 'schedules.doctor_id', 'left')
                ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
                ->select([
                    'schedules.id AS id',
                    'doctors.user_id AS doctor_id',
                    'name',
                    'day',
                    'availability',
                    'time_start',
                    'time_end',
                ])
                ->where('schedules.doctor_id', '=', $doctorID)
                ->limit($limit)
                ->offset(($page - 1) * $limit);

            return $schedule->get();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::readScheduleDoctorByFilter', $th);

            return new Collection;
        }
    }

    public function selectAllSchedule(int $limit = 10, int $offset = 0, $params = []): Collection
    {
        try {
            $query = DB::table('schedules')
                ->join('doctors', 'doctors.user_id', '=', 'schedules.doctor_id', 'left')
                ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
                ->select([
                    'schedules.id AS id',
                    'doctors.user_id AS doctor_id',
                    'name',
                    'day',
                    'availability',
                    'time_start',
                    'time_end',
                ])
                ->orderBy('name', 'asc')
                ->limit($limit)
                ->offset($offset);

            if (isset($params['doctor_id'])) {
                $query->where('schedules.doctor_id', '=', $params['doctor_id']);
            }

            if (isset($params['day'])) {
                $query->where('schedules.day', '=', $params['day']);
            }

            return $query->get();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::selectAllSchedule', $th);
            throw $th;
        }
    }

    public function selectScheduleByID(string $scheduleID): object
    {
        try {
            return DB::table('schedules')
                ->join('doctors', 'doctors.user_id', '=', 'schedules.doctor_id', 'left')
                ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
                ->select([
                    'schedules.id AS id',
                    'doctors.user_id AS doctor_id',
                    'name',
                    'day',
                    'availability',
                    'time_start',
                    'time_end',
                ])
                ->where('schedules.id', '=', $scheduleID)
                ->first();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::selectAllSchedule', $th);
            throw $th;
        }
    }

    public function selectScheduleByDoctorID(string $doctorID, int $limit = 10, int $offset = 0): Collection
    {
        try {
            return DB::table('schedules')
                ->join('doctors', 'doctors.user_id', '=', 'schedules.doctor_id', 'left')
                ->join('users', 'users.id', '=', 'doctors.user_id', 'left')
                ->select([
                    'schedules.id AS id',
                    'name',
                    'day',
                    'availability',
                    'time_start',
                    'time_end',
                ])
                ->where('schedules.doctor_id', '=', $doctorID)
                ->limit($limit)
                ->offset($offset)
                ->get();
        } catch (Exception $e) {
            $this->writeLog('ScheduleService::selectScheduleByDoctorID', $e);
            throw $e;
        }
    }

    public function insertSchedule(ScheduleEntity $schedule): string|Throwable
    {
        try {
            $id = Str::uuid();
            DB::table('schedules')->insert([
                'id' => $id,
                'doctor_id' => $schedule->doctor_id,
                'day' => $schedule->day,
                'time_start' => $schedule->start_time,
                'time_end' => $schedule->end_time,
                'availability' => 'AVAILABLE',
            ]);

            return $id;
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::insertSchedule', $th);
            throw $th;
        }
    }

    public function updateStatusSchedule(string $scheduleID): bool|Exception
    {
        try {
            $schedule = $this->selectScheduleByID($scheduleID);
            $isAvailable = $schedule->availability == 'AVAILABLE' ? 'UNAVAILABLE' : 'AVAILABLE';
            DB::table('schedules')->where('id', '=', $scheduleID)
                ->update(['availability' => $isAvailable]);

            return $isAvailable;
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::updateStatusSchedule', $th);
            throw $th;
        }
    }

    public function deleteSchedule(string $scheduleID): bool|Exception
    {
        try {
            return DB::table('schedules')->where('id', '=', $scheduleID)->delete();
        } catch (Throwable $th) {
            $this->writeLog('ScheduleService::deleteSchedule', $th);
            throw $th;
        }
    }
}
