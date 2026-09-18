<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardService extends Service
{
    public function getAdminDataOverview()
    {
        // Method ini menjalankan ~30 query agregat; hasilnya di-cache 60 detik.
        // Staleness maksimal 1 menit dapat diterima untuk angka dashboard.
        return Cache::remember('dashboard:admin-overview', 60, function () {
        try {
            $monthStart = date('Y-m-01 00:00:00');
            $monthEnd = date('Y-m-t 23:59:59');

            $monthlyTransactions = DB::table('transactions')
                ->whereNull('canceled_at')
                ->where('appointment_datetime', '>=', $monthStart)
                ->where('appointment_datetime', '<=', $monthEnd);

            $monthlyRevenue = (float) (clone $monthlyTransactions)->sum('billing');
            $monthlyTxCount = (int) (clone $monthlyTransactions)->count();
            $totalPatients = (int) DB::table('patients')->count();

            // Pasien baru didata bulan berjalan
            $newPatientsThisMonth = (int) DB::table('patients')
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<=', $monthEnd)
                ->count();

            // Rata-rata nilai per transaksi / kunjungan
            $avgTicket = $monthlyTxCount > 0 ? round($monthlyRevenue / $monthlyTxCount) : 0;

            // 1. Tren Mingguan (6 minggu terakhir)
            $weeklyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $wStart = date('Y-m-d 00:00:00', strtotime("-{$i} weeks monday this week"));
                $wEnd = date('Y-m-d 23:59:59', strtotime("-{$i} weeks sunday this week"));
                $wLabel = date('d M', strtotime($wStart)).' - '.date('d M', strtotime($wEnd));

                $wTrx = DB::table('transactions')
                    ->whereNull('canceled_at')
                    ->where('appointment_datetime', '>=', $wStart)
                    ->where('appointment_datetime', '<=', $wEnd)
                    ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                    ->first();

                $wPat = DB::table('patients')
                    ->where('created_at', '>=', $wStart)
                    ->where('created_at', '<=', $wEnd)
                    ->count();

                $weeklyTrend[] = [
                    'label' => $wLabel,
                    'revenue' => (float) ($wTrx->total_revenue ?? 0),
                    'visits' => (int) ($wTrx->total_visits ?? 0),
                    'new_patients' => (int) $wPat,
                ];
            }

            // 2. Tren Bulanan (12 bulan terakhir)
            $monthlyTrend = [];
            for ($i = 11; $i >= 0; $i--) {
                $time = strtotime("-{$i} months");
                $mStart = date('Y-m-01 00:00:00', $time);
                $mEnd = date('Y-m-t 23:59:59', $time);
                $mLabel = \Carbon\Carbon::createFromTimestamp($time)->translatedFormat('M Y');

                $mTrx = DB::table('transactions')
                    ->whereNull('canceled_at')
                    ->where('appointment_datetime', '>=', $mStart)
                    ->where('appointment_datetime', '<=', $mEnd)
                    ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                    ->first();

                $mPat = DB::table('patients')
                    ->where('created_at', '>=', $mStart)
                    ->where('created_at', '<=', $mEnd)
                    ->count();

                $monthlyTrend[] = [
                    'label' => $mLabel,
                    'revenue' => (float) ($mTrx->total_revenue ?? 0),
                    'visits' => (int) ($mTrx->total_visits ?? 0),
                    'new_patients' => (int) $mPat,
                ];
            }

            // 3. Tren Tahunan (4 tahun terakhir)
            $yearlyTrend = [];
            $currYear = (int) date('Y');
            for ($i = 3; $i >= 0; $i--) {
                $year = $currYear - $i;
                $yStart = "{$year}-01-01 00:00:00";
                $yEnd = "{$year}-12-31 23:59:59";

                $yTrx = DB::table('transactions')
                    ->whereNull('canceled_at')
                    ->where('appointment_datetime', '>=', $yStart)
                    ->where('appointment_datetime', '<=', $yEnd)
                    ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                    ->first();

                $yPat = DB::table('patients')
                    ->where('created_at', '>=', $yStart)
                    ->where('created_at', '<=', $yEnd)
                    ->count();

                $yearlyTrend[] = [
                    'label' => (string) $year,
                    'revenue' => (float) ($yTrx->total_revenue ?? 0),
                    'visits' => (int) ($yTrx->total_visits ?? 0),
                    'new_patients' => (int) $yPat,
                ];
            }

            $revenueAnalytics = [
                'weekly' => $weeklyTrend,
                'monthly' => $monthlyTrend,
                'yearly' => $yearlyTrend,
            ];

            // Total periode sebelumnya (pembanding % badge headline chart).
            // 3 query agregat ringan, tanpa loop per bucket.
            $prevRanges = [
                'weekly' => [
                    date('Y-m-d 00:00:00', strtotime('-11 weeks monday this week')),
                    date('Y-m-d 23:59:59', strtotime('-6 weeks sunday this week')),
                ],
                'monthly' => [
                    date('Y-m-01 00:00:00', strtotime('-23 months')),
                    date('Y-m-t 23:59:59', strtotime('-12 months')),
                ],
                'yearly' => [
                    ($currYear - 7).'-01-01 00:00:00',
                    ($currYear - 4).'-12-31 23:59:59',
                ],
            ];

            $prevTotals = [];
            foreach ($prevRanges as $key => [$pStart, $pEnd]) {
                $row = DB::table('transactions')
                    ->whereNull('canceled_at')
                    ->where('appointment_datetime', '>=', $pStart)
                    ->where('appointment_datetime', '<=', $pEnd)
                    ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                    ->first();
                $prevTotals[$key] = [
                    'revenue' => (float) ($row->total_revenue ?? 0),
                    'visits' => (int) ($row->total_visits ?? 0),
                ];
            }
            $revenueAnalytics['prev'] = $prevTotals;

            // Breakdown metode pembayaran bulan ini
            $paymentMethods = DB::table('transactions')
                ->whereNull('canceled_at')
                ->where('appointment_datetime', '>=', $monthStart)
                ->where('appointment_datetime', '<=', $monthEnd)
                ->selectRaw("coalesce(nullif(payment_method, ''), 'LAINNYA') as method, count(id) as total_count, coalesce(sum(billing), 0) as total_amount")
                ->groupBy(DB::raw("coalesce(nullif(payment_method, ''), 'LAINNYA')"))
                ->orderByDesc('total_amount')
                ->get();

            // Pasien dengan data belum lengkap
            $incompletePatientsQuery = DB::table('patients')
                ->leftJoin('patient_addresses', 'patients.id', '=', 'patient_addresses.patient_id')
                ->where(function ($q) {
                    $q->whereNull('patients.nik')
                        ->orWhere('patients.nik', '')
                        ->orWhereNull('patients.phone')
                        ->orWhere('patients.phone', '')
                        ->orWhereNull('patients.birthdate')
                        ->orWhere('patients.birthdate', '')
                        ->orWhereNull('patient_addresses.patient_id')
                        ->orWhereNull('patient_addresses.street')
                        ->orWhere('patient_addresses.street', '');
                });

            $incompleteCount = (clone $incompletePatientsQuery)->count();

            $incompletePatients = $incompletePatientsQuery
                ->select(
                    'patients.id',
                    'patients.name',
                    'patients.code',
                    'patients.nik',
                    'patients.phone',
                    'patients.birthdate',
                    'patient_addresses.street',
                    'patient_addresses.village'
                )
                ->orderByDesc('patients.created_at')
                ->limit(6)
                ->get()
                ->map(function ($p) {
                    $missing = [];
                    if (empty($p->nik)) {
                        $missing[] = 'NIK';
                    }
                    if (empty($p->phone)) {
                        $missing[] = 'No. HP';
                    }
                    if (empty($p->birthdate)) {
                        $missing[] = 'Tgl Lahir';
                    }
                    if (empty($p->street) && empty($p->village)) {
                        $missing[] = 'Alamat';
                    }
                    $p->missing_fields = $missing;

                    return $p;
                });

            // 5 Transaksi terbaru bulan ini
            $recentTransactions = DB::table('transactions')
                ->whereNull('canceled_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            return (object) [
                'transactions' => $monthlyTxCount,
                'revenue' => $monthlyRevenue,
                'patients' => $totalPatients,
                'new_patients' => $newPatientsThisMonth,
                'avg_ticket' => $avgTicket,
                'revenue_analytics' => $revenueAnalytics,
                'monthly_trend' => $monthlyTrend,
                'payment_methods' => $paymentMethods,
                'incomplete_count' => $incompleteCount,
                'incomplete_patients' => $incompletePatients,
                'recent_transactions' => $recentTransactions,
            ];
        } catch (Throwable $th) {
            $this->writeLog('DashboardService::getAdminDataOverview', $th);
            throw $th;
        }
        });
    }

    public function getDoctorDataOverview($doctorID = null)
    {
        // Key per dokter (null = semua). TTL 60 detik, alasan sama seperti di atas.
        return Cache::remember('dashboard:doctor-overview:'.($doctorID ?? 'all'), 60, function () use ($doctorID) {
        try {
            // Antrian minggu berjalan (Senin–Sabtu) yang belum dilayani/batal.
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd = date('Y-m-d', strtotime('saturday this week'));

            $appointments = DB::table('appointments')
                ->whereDate('date', '>=', $weekStart)
                ->whereDate('date', '<=', $weekEnd)
                ->whereNull('recorded_at')
                ->whereNull('canceled_at');

            $medicalRecords = DB::table('medical_records');
            $transactions = DB::table('transactions');

            if (! empty($doctorID)) {
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
        });
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

            return (float) $income->sum('billing');
        } catch (Throwable $th) {
            $this->writeLog('DashboardService::getIncomeFromDate', $th);
            throw $th;
        }
    }
}
