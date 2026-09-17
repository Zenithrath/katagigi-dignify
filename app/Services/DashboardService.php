<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardService extends Service
{
    public function getAdminDataOverview()
    {
        $doctors = DB::table('doctors')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->select('users.id as id', 'users.name as name')
            ->get();

        foreach ($doctors as $doctor) {
            $doctor->transactions = DB::table('transactions')
                ->where('doctor_id', $doctor->id)
                ->where('appointment_datetime', '>', date('Y-m-01'))
                ->where('appointment_datetime', '<=', date('Y-m-t 23:59:59'))
                ->whereNull('canceled_at')
                ->selectRaw('count(id) as counter')->first()->counter;

            $doctor->medical_records = DB::table('medical_records')
                ->where('doctor_id', $doctor->id)
                ->whereMonth('appointment_date', date('m'))
                ->whereYear('appointment_date', date('Y'))
                ->selectRaw('count(id) as counter')->first()->counter;
        }

        try {
            return (object) [
                'transactions' => DB::table('transactions')
                    ->where('appointment_datetime', '>', date('Y-m-01'))
                    ->where(
                        'appointment_datetime',
                        '<=',
                        date('Y-m-t 23:59:59')
                    )
                    ->selectRaw('count(id) as counter')->first()->counter,
                'revenue' => $this->sumServicesBilling(
                    DB::table('transactions')
                        ->whereNull('canceled_at')
                        ->where('appointment_datetime', '>=', date('Y-m-01'))
                        ->pluck('services')
                ),
                'patients' => DB::table('patients')
                    ->selectRaw('count(id) as counter')->first()->counter,
                'dataChart' => (object) [
                    'labels' => $doctors->pluck('name')->toArray(),
                    'datasets' => [
                        (object) [
                            'label' => 'Transactions',
                            'data' => $doctors->pluck('transactions')->toArray(),
                            'backgroundColor' => '#475569',
                            'borderColor' => '#1e293b',
                            'borderWidth' => 1,
                        ],
                        (object) [
                            'label' => 'Medical Records',
                            'data' => $doctors->pluck('medical_records')->toArray(),
                            'backgroundColor' => '#3b82f6',
                            'borderColor' => '#1d4ed8',
                            'borderWidth' => 1,
                        ],
                    ],
                ],
            ];
        } catch (Throwable $th) {
            $this->writeLog('DashboardService::getAdminDataOverview', $th);
            throw $th;
        }
    }

    public function getDoctorDataOverview($doctorID = null)
    {
        try {
            $appointments = DB::table('appointments')
                ->whereDate('date', '>=', date('Y-m-d', strtotime('sunday this week')))
                ->whereDate('date', '>=', date('Y-m-d', strtotime('saturday this week')))
                ->whereNull(['recorded_at', 'canceled_at']);

            $medicalRecords = DB::table('medical_records');
            $transactions = DB::table('transactions');

            if ($doctorID != '' || $doctorID == null) {
                $appointments->where('doctor_id', $doctorID);
                $medicalRecords->where('doctor_id', $doctorID);
                $transactions->where('doctor_id', $doctorID);
            }

            return
                (object) [
                    'medical_records' => $medicalRecords->whereMonth('appointment_date', date('m'))
                        ->whereYear('appointment_date', date('Y'))
                        ->selectRaw('count(id) as counter')->first()->counter,
                    'transactions' => $transactions
                        ->where('appointment_datetime', '>', date('Y-m-01'))
                        ->where('appointment_datetime', '<=', date('Y-m-t 23:59:59'))
                        ->whereNull('canceled_at')
                        ->selectRaw('count(id) as counter')->first()->counter,
                    'appointments' => $appointments->get(),
                    'schedules' => [],
                ];
        } catch (Throwable $th) {
            $this->writeLog('DashboardService::getDoctorDataOverview', $th);
            throw $th;
        }
    }

    public function getIncomeFromDate(object $filter)
    {
        try {
            // appointment_datetime disimpan sebagai string 'Y-m-d H:i:s'
            // → filter prefix LIKE agar portabel sqlite/mysql/pgsql.
            $prefix = '';
            if (isset($filter->year)) {
                $prefix = sprintf('%04d', $filter->year);
            }
            if (isset($filter->month)) {
                $prefix .= '-'.sprintf('%02d', $filter->month);
            }
            if (isset($filter->day)) {
                $prefix .= '-'.sprintf('%02d', $filter->day);
            }

            $income = DB::table('transactions')->whereNull('canceled_at');
            if ($prefix !== '') {
                $income->where('appointment_datetime', 'like', $prefix.'%');
            }

            return $income->get()->sum('billing');
        } catch (Throwable $th) {
            $this->writeLog('DashboardService::getIncomeFromDate', $th);
            throw $th;
        }
    }

    /**
     * Jumlahkan price*qty-discount dari kolom services (JSON) secara PHP.
     * Pengganti CTE json_array_elements Postgres agar jalan di sqlite/mysql/pgsql.
     */
    private function sumServicesBilling($servicesJsonList): float
    {
        $total = 0;
        foreach ($servicesJsonList as $json) {
            foreach ((array) json_decode($json) as $service) {
                $service = (array) $service;
                $total += ((float) ($service['price'] ?? 0)) * ((float) ($service['quantity'] ?? 0))
                    - ((float) ($service['discount'] ?? 0));
            }
        }

        return $total;
    }
}
