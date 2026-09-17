<?php

namespace App\Http\Controllers\General;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
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
            $overview = $this->service->getAdminDataOverview();
            $data = (object) [
                'role' => 'admin',
                'revenue' => GeneralHelper::floatToRupiah((float) ($overview->revenue ?? 0)),
                'revenue_raw' => (float) ($overview->revenue ?? 0),
                'transactions' => $overview->transactions,
                'patients' => $overview->patients,
                'new_patients' => $overview->new_patients,
                'avg_ticket' => GeneralHelper::floatToRupiah((float) ($overview->avg_ticket ?? 0)),
                'revenue_analytics' => $overview->revenue_analytics ?? [],
                'monthly_trend' => $overview->monthly_trend,
                'payment_methods' => $overview->payment_methods,
                'incomplete_count' => $overview->incomplete_count,
                'incomplete_patients' => $overview->incomplete_patients,
                'recent_transactions' => $overview->recent_transactions,
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
}
