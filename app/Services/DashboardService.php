<?php

namespace App\Services;

use Carbon\Carbon;
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

                // Tren mingguan/bulanan/tahunan + pembanding periode sebelumnya
                // diekstrak ke helper bersama agar bisa dipakai overview dokter
                // dengan scope query berbeda (mis. doctor_id).
                $revenueAnalytics = $this->buildRevenueAnalytics(
                    DB::table('transactions')->whereNull('canceled_at')
                );

                // Breakdown metode pembayaran bulan ini.
                // PAYLOAD CACHE WAJIB ARRAY MURNI: config/cache.php menyetel
                // serializable_classes = false, sehingga stdClass/Collection yang
                // di-cache menjadi __PHP_Incomplete_Class saat dibaca. Konversi
                // ke array asosiatif di sini; controller yang hydrate ulang.
                $paymentMethods = DB::table('transactions')
                    ->whereNull('canceled_at')
                    ->where('appointment_datetime', '>=', $monthStart)
                    ->where('appointment_datetime', '<=', $monthEnd)
                    ->selectRaw("coalesce(nullif(payment_method, ''), 'LAINNYA') as method, count(id) as total_count, coalesce(sum(billing), 0) as total_amount")
                    ->groupBy(DB::raw("coalesce(nullif(payment_method, ''), 'LAINNYA')"))
                    ->orderByDesc('total_amount')
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();

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

                        // (array) setelah missing_fields disuntikkan — lihat
                        // catatan payload cache di $paymentMethods.
                        return (array) $p;
                    })
                    ->all();

                // 5 Transaksi terbaru bulan ini (array murni — lihat catatan di atas)
                $recentTransactions = DB::table('transactions')
                    ->whereNull('canceled_at')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();

                // Kembalikan ARRAY BERSIH tanpa satu pun object: cache store database
                // memakai unserialize dengan allowed_classes terbatas (config/cache.php
                // serializable_classes = false), sehingga stdClass/Collection yang
                // di-cache menjadi __PHP_Incomplete_Class saat dibaca. Hanya
                // array/scalar yang aman. Controller yang meng-hydrate jadi Collection
                // of stdClass untuk kebutuhan akses ->prop di view.
                return [
                    'transactions' => $monthlyTxCount,
                    'revenue' => $monthlyRevenue,
                    'patients' => $totalPatients,
                    'new_patients' => $newPatientsThisMonth,
                    'avg_ticket' => $avgTicket,
                    'revenue_analytics' => $revenueAnalytics,
                    'monthly_trend' => $revenueAnalytics['monthly'],
                    'today_appointments' => DB::table('appointments')
                        ->whereNull('canceled_at')
                        ->whereDate('date', date('Y-m-d'))
                        ->orderBy('time_start')
                        ->limit(20)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->all(),
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

                // Transaksi bulan berjalan (sudah ter-scope dokter di atas).
                $monthTx = (clone $transactions)
                    ->whereNull('canceled_at')
                    ->where('appointment_datetime', '>', date('Y-m-01'))
                    ->where('appointment_datetime', '<=', date('Y-m-t 23:59:59'));

                // Array asosiatif murni (tanpa object) agar aman lewat serialize
                // cache — lihat catatan payload di getAdminDataOverview().
                return [
                    'medical_records' => $medicalRecords->whereMonth('appointment_date', date('m'))
                        ->whereYear('appointment_date', date('Y'))
                        ->selectRaw('count(id) as counter')->first()->counter,
                    'transactions' => (int) (clone $monthTx)->count(),
                    'revenue' => (float) (clone $monthTx)->sum('billing'),
                    'revenue_analytics' => $this->buildRevenueAnalytics(
                        (clone $transactions)->whereNull('canceled_at')
                    ),
                    'today_appointments' => (clone $appointments)
                        ->whereDate('date', date('Y-m-d'))
                        ->orderBy('time_start')
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->all(),
                    'appointments' => $appointments->get()
                        ->map(fn ($row) => (array) $row)
                        ->all(),
                    'recent_records' => DB::table('medical_records')
                        ->when(! empty($doctorID), fn ($q) => $q->where('doctor_id', $doctorID))
                        ->orderByDesc('created_at')
                        ->limit(5)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->all(),
                    'schedules' => [],
                ];
            } catch (Throwable $th) {
                $this->writeLog('DashboardService::getDoctorDataOverview', $th);
                throw $th;
            }
        });
    }

    /**
     * Bangun tren mingguan/bulanan/tahunan + total periode sebelumnya
     * dari base query transaksi yang SUDAH berisi scope (doctor_id bila ada)
     * dan whereNull('canceled_at'), TANPA filter tanggal.
     *
     * Payload berupa array murni agar aman lewat serialize cache
     * (serializable_classes = false — lihat config/cache.php).
     */
    private function buildRevenueAnalytics($baseQuery): array
    {
        // 1. Tren Mingguan (6 minggu terakhir)
        $weeklyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $wStart = date('Y-m-d 00:00:00', strtotime("-{$i} weeks monday this week"));
            $wEnd = date('Y-m-d 23:59:59', strtotime("-{$i} weeks sunday this week"));
            $wLabel = date('d M', strtotime($wStart)).' - '.date('d M', strtotime($wEnd));

            $wTrx = (clone $baseQuery)
                ->where('appointment_datetime', '>=', $wStart)
                ->where('appointment_datetime', '<=', $wEnd)
                ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                ->first();

            $weeklyTrend[] = [
                'label' => $wLabel,
                'revenue' => (float) ($wTrx->total_revenue ?? 0),
                'visits' => (int) ($wTrx->total_visits ?? 0),
            ];
        }

        // 2. Tren Bulanan (12 bulan terakhir)
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $time = strtotime("-{$i} months");
            $mStart = date('Y-m-01 00:00:00', $time);
            $mEnd = date('Y-m-t 23:59:59', $time);
            $mLabel = Carbon::createFromTimestamp($time)->translatedFormat('M Y');

            $mTrx = (clone $baseQuery)
                ->where('appointment_datetime', '>=', $mStart)
                ->where('appointment_datetime', '<=', $mEnd)
                ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                ->first();

            $monthlyTrend[] = [
                'label' => $mLabel,
                'revenue' => (float) ($mTrx->total_revenue ?? 0),
                'visits' => (int) ($mTrx->total_visits ?? 0),
            ];
        }

        // 3. Tren Tahunan (4 tahun terakhir)
        $yearlyTrend = [];
        $currYear = (int) date('Y');
        for ($i = 3; $i >= 0; $i--) {
            $year = $currYear - $i;
            $yStart = "{$year}-01-01 00:00:00";
            $yEnd = "{$year}-12-31 23:59:59";

            $yTrx = (clone $baseQuery)
                ->where('appointment_datetime', '>=', $yStart)
                ->where('appointment_datetime', '<=', $yEnd)
                ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                ->first();

            $yearlyTrend[] = [
                'label' => (string) $year,
                'revenue' => (float) ($yTrx->total_revenue ?? 0),
                'visits' => (int) ($yTrx->total_visits ?? 0),
            ];
        }

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
            $row = (clone $baseQuery)
                ->where('appointment_datetime', '>=', $pStart)
                ->where('appointment_datetime', '<=', $pEnd)
                ->selectRaw('count(id) as total_visits, coalesce(sum(billing), 0) as total_revenue')
                ->first();
            $prevTotals[$key] = [
                'revenue' => (float) ($row->total_revenue ?? 0),
                'visits' => (int) ($row->total_visits ?? 0),
            ];
        }

        return [
            'weekly' => $weeklyTrend,
            'monthly' => $monthlyTrend,
            'yearly' => $yearlyTrend,
            'prev' => $prevTotals,
        ];
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
