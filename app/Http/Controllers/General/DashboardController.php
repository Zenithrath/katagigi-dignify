<?php

namespace App\Http\Controllers\General;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = (object) [];
        $roles = auth()->user()->getRoleNames();
        // manajemen = admin lama: lihat dashboard admin.
        $role = $roles->contains('manajemen') ? 'admin' : ($roles->first() ?? 'guest');

        if ($role == 'admin') {
            $data = $this->service->getAdminDataOverview();
            $dataAppointment = $this->service->getDoctorDataOverview($request->doctor);
            $labelLastYear = $this->getMonthLabels();
            $labelLastMonth = $this->getDayLabels();
            $labelThisYear = $this->getMonthLabels(Carbon::now()->month);
            $data = (object) [
                'revenue' => GeneralHelper::floatToRupiah($data->revenue == null ? (float) 0 : $data->revenue),
                'transactions' => $data->transactions,
                'patients' => $data->patients,
                'dataChart' => $data->dataChart,
                // "medical_records" => $dataAppointment->medical_records,
                'incomeChart' => json_encode((object) [
                    'lastYear' => (object) [
                        'labels' => $labelLastYear->labels,
                        'datasets' => collect($labelLastYear->dates)->map(function ($item) {
                            $date = Carbon::createFromFormat('Y-m', $item);

                            return $this->service->getIncomeFromDate((object) [
                                'year' => (string) $date->year,
                                'month' => (string) $date->month,
                            ]);
                        })->toArray(),
                    ],
                    'lastMonth' => (object) [
                        'labels' => $labelLastMonth->labels,
                        'datasets' => collect($labelLastMonth->dates)->map(function ($item) {
                            $date = Carbon::createFromFormat('Y-m-d', $item);

                            return $this->service->getIncomeFromDate((object) [
                                'year' => (string) $date->year,
                                'month' => (string) $date->month,
                                'day' => (string) $date->day,
                            ]);
                        })->toArray(),
                    ],
                    'thisYear' => (object) [
                        'labels' => $labelThisYear->labels,
                        'datasets' => collect($labelThisYear->dates)->map(function ($item) {
                            $date = Carbon::createFromFormat('Y-m', $item);

                            return $this->service->getIncomeFromDate((object) [
                                'year' => (string) $date->year,
                                'month' => (string) $date->month,
                            ]);
                        })->toArray(),
                    ],
                ]),
                'appointments' => $dataAppointment->appointments,
            ];
        }

        if ($role == 'doctor') {
            $doctorID = auth()->user()->id;
            $data = $this->service->getDoctorDataOverview($doctorID);
            $data = (object) [
                'transactions' => $data->transactions,
                'medical_records' => $data->medical_records,
                'appointments' => $data->appointments,
                'schedules' => $data->schedules,
            ];
        }

        if ($role == 'nurse') {
            $data = $this->service->getDoctorDataOverview($request->doctor);
            $data = (object) [
                'transactions' => $data->transactions,
                'medical_records' => $data->medical_records,
                'appointments' => $data->appointments,
            ];
        }

        if ($role == 'guest') {
            $data = (object) [
                'transactions' => 0,
                'revenue' => GeneralHelper::floatToRupiah(0),
                'patients' => 0,
                'appointments' => collect(),
            ];
        }

        return view('dashboard', ['data' => $data]);
    }

    private function getMonthLabels(?int $month = null)
    {
        $lang = app()->getLocale();
        $monthLabels = [];
        $monthTotal = $month ? $month : 12;
        $monthDate = [];
        if ($month) {
            for ($i = 1; $i <= $monthTotal; $i++) {
                $monthLabels[] = Carbon::create()->month($i)->locale($lang)->isoFormat('MMMM');
                $monthDate[] = Carbon::now()->month($i)->locale($lang)->isoFormat('YYYY-MM');
            }
        } else {
            for ($i = $monthTotal; $i >= 1; $i--) {
                $monthLabels[] = Carbon::now()->addMonth(1)->locale($lang)->subMonths($i)->isoFormat('MMMM');
                $monthDate[] = Carbon::now()->addMonth(1)->locale($lang)->subMonths($i)->isoFormat('YYYY-MM');
            }
        }

        return (object) [
            'labels' => $monthLabels,
            'dates' => $monthDate,
        ];
    }

    private function getDayLabels()
    {
        $dayLabels = [];
        $today = Carbon::now()->addDays(1);
        $days = [];
        for ($i = 30; $i >= 1; $i--) {
            $dayLabels[] = $today->copy()->subDays($i)->isoFormat('DD MMMM');
            $days[] = $today->copy()->subDays($i)->isoFormat('YYYY-MM-DD');
        }

        return (object) [
            'labels' => $dayLabels,
            'dates' => $days,
        ];
    }
}
