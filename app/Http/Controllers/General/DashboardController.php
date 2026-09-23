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
        $roles = auth()->user()->getRoleNames();
        // manajemen = admin lama: lihat dashboard admin.
        $role = $roles->contains('manajemen') ? 'admin' : ($roles->first() ?? 'guest');

        // Overview array bersih dari cache (serialize-safe) — lihat DashboardService.
        // Nurse: overview operasional klinik (bukan overview dokter lain).
        $overview = match ($role) {
            'admin' => $this->service->getAdminDataOverview(),
            'doctor' => $this->service->getDoctorDataOverview(auth()->user()->id),
            'nurse' => $this->service->getAdminDataOverview(),
            default => null,
        };

        $data = (object) [
            'role' => $role,
            'roleLabel' => __('dashboard.role.'.$role),
            'widgets' => $this->buildWidgets($role, $overview),
        ];

        return view('dashboard', ['data' => $data]);
    }

    /**
     * Kumpulkan payload hanya untuk widget yang boleh & layak dirender
     * untuk role terkait. Payload tanpa data tidak dirender (kecuali
     * kosong-nya bermakna operasional, mis. antrian hari ini).
     *
     * @return \Illuminate\Support\Collection<int, object{view: string, payload: object}>
     */
    private function buildWidgets(string $role, ?array $overview): \Illuminate\Support\Collection
    {
        if ($overview === null) {
            return collect(); // guest: hanya header
        }

        $widgets = collect();
        foreach ($this->widgetMap() as $view => [$allowed, $provider]) {
            if (! in_array($role, $allowed, true)) {
                continue;
            }

            $payload = $this->{$provider}($overview, $role);
            if ($payload === null) {
                continue;
            }

            $widgets->push((object) ['view' => $view, 'payload' => $payload]);
        }

        return $widgets;
    }

    /**
     * Registry widget: nama partial => [role yang boleh lihat, provider payload].
     * Menambah widget/role baru cukup di sini + partial-nya.
     */
    private function widgetMap(): array
    {
        return [
            // Pendapatan (kpi + grafik) HANYA manajemen/admin — dokter &
            // perawat melihat kinerja operasional tanpa angka finansial.
            'kpi-row' => [['admin', 'doctor', 'nurse'], 'kpiRow'],
            'chart-trend' => [['admin'], 'chartTrend'],
            'queue-today' => [['admin', 'doctor', 'nurse'], 'queueToday'],
            'billing-methods' => [['admin'], 'billingMethods'],
            'patients-incomplete' => [['admin'], 'patientsIncomplete'],
            'recent-activities' => [['admin', 'doctor'], 'recentActivities'],
        ];
    }

    private function kpiRow(array $o, string $role): object
    {
        return (object) [
            // Keuangan hanya untuk manajemen (dashboard admin).
            'show_revenue' => $role === 'admin',
            'revenue' => GeneralHelper::floatToRupiah((float) ($o['revenue'] ?? 0)),
            'transactions' => (int) ($o['transactions'] ?? 0),
            'patients' => isset($o['patients']) ? (int) $o['patients'] : null,
            'new_patients' => isset($o['new_patients']) ? (int) $o['new_patients'] : null,
            'medical_records' => isset($o['medical_records']) ? (int) $o['medical_records'] : null,
            // true = angka milik dokter sendiri, bukan seluruh klinik.
            'scoped' => $role !== 'admin',
        ];
    }

    private function chartTrend(array $o, string $role): ?object
    {
        if (empty($o['revenue_analytics'])) {
            return null;
        }

        return (object) ['analytics' => $o['revenue_analytics']];
    }

    private function queueToday(array $o, string $role): object
    {
        // Selalu dirender: antrian kosong adalah info operasional yang berarti.
        return (object) [
            'appointments' => $this->toObjects($o['today_appointments'] ?? []),
        ];
    }

    private function billingMethods(array $o, string $role): ?object
    {
        // Selalu dirender (manajemen): tabel kosong = info transaksi bulan ini
        // belum ada, bukan widget hilang. Partial menangani kosong sendiri.
        return (object) [
            'methods' => $this->toObjects($o['payment_methods'] ?? []),
        ];
    }

    private function patientsIncomplete(array $o, string $role): ?object
    {
        // Selalu dirender (manajemen): "semua lengkap" adalah info yang berarti,
        // jangan hilangkan widget-nya — partial menampilkan empty state.
        return (object) [
            'count' => (int) ($o['incomplete_count'] ?? 0),
            'patients' => $this->toObjects($o['incomplete_patients'] ?? []),
        ];
    }

    private function recentActivities(array $o, string $role): ?object
    {
        if ($role === 'doctor') {
            return (object) ['type' => 'records', 'rows' => $this->toObjects($o['recent_records'] ?? [])];
        }

        return (object) ['type' => 'transactions', 'rows' => $this->toObjects($o['recent_transactions'] ?? [])];
    }

    /**
     * Hydrate array asosiatif hasil cache menjadi Collection of stdClass
     * agar akses properti (->name, ->code, dst.) di view tetap bekerja.
     * stdClass aman dipakai runtime; yang dilarang hanyalah masuk cache.
     */
    private function toObjects(array $rows): \Illuminate\Support\Collection
    {
        return collect($rows)->map(fn ($row) => is_object($row) ? $row : (object) $row);
    }
}
