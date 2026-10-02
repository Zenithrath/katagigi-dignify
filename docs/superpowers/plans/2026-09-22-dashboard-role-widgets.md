# Dashboard Role-Gated Widgets Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans (inline) — subagent dispatch tidak tersedia di environment ini. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Satu halaman dashboard untuk semua role; isi widget ditentukan registry per-role dan scope data mengikuti role (dokter hanya melihat data miliknya).

**Architecture:** `DashboardService` memasok overview array serialize-safe (cache database, `serializable_classes = false`). `DashboardController` memetakan role → daftar widget (registry) → provider method → Collection of `{view, payload}` yang dirender `dashboard.blade.php` sebagai partial loop. Controller dan view diganti dalam SATU task agar dashboard tidak pernah 500 di antara task. Tidak ada route/permission baru.

**Tech Stack:** Laravel 11, Blade partials, Alpine (`revenueChartComponent`), PHPUnit feature tests, i18n `lang/{id,en}`.

**Spec:** `docs/superpowers/specs/2026-09-22-dashboard-role-widgets-design.md`

## Global Constraints

- Payload cache WAJIB array murni — tidak boleh ada `stdClass`/`Collection` masuk cache (regresi `__PHP_Incomplete_Class`; lihat `DashboardTest`).
- Scope dokter di level service: `where('doctor_id', <user id>)` pada `transactions`, `medical_records`, `appointments` (semua tabel memakai user id sebagai doctor_id).
- Role resolution: `manajemen` → `admin`; role lain = nama role itu sendiri; fallback `guest`.
- Semua label baru via `lang/{id,en}/dashboard.php` + `__()`.
- Perawat TIDAK boleh melihat angka finansial (revenue) — diuji.
- Test style: `RefreshDatabase` + `$this->seed()`; login via `actingAs` + `email_verified_at` disetel.

---

### Task 1: Service — helper analytics bersama + enrich overview admin & dokter

**Files:**
- Modify: `app/Services/DashboardService.php`
- Test: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Produces: `getDoctorDataOverview($doctorID)` kini mengembalikan kunci tambahan: `revenue` (float), `revenue_analytics` (array `weekly/monthly/yearly/prev`), `today_appointments` (array of array), `recent_records` (array of array). `getAdminDataOverview()` tambah `today_appointments` (array of array).
- Produces (private): `buildRevenueAnalytics($baseQuery): array` — `$baseQuery` = builder `transactions` yang SUDAH berisi scope (doctor_id) + `whereNull('canceled_at')`, TANPA filter tanggal.

- [ ] **Step 1: Tulis test scope yang gagal**

Tambahkan ke `tests/Feature/DashboardTest.php` (import `DB`, `Str`, `DashboardService`; cek `DemoDataSeeder` untuk dokter kedua — bila tidak ada, uji cukup dengan dokter A):

```php
public function test_doctor_overview_scopes_revenue_to_own_transactions(): void
{
    config(['cache.default' => 'database']);

    $doctorA = $this->verifiedUser('doctor@gmail.com');

    $patientId = DB::table('patients')->value('id') ?? DB::table('patients')->insertGetId([
        'id' => (string) Str::uuid(), 'name' => 'T Patient', 'code' => 'T-001',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::table('transactions')->insert([
        'id' => (string) Str::uuid(), 'sequence' => random_int(1, 999999),
        'patient_id' => $patientId, 'patient_name' => 'T Patient', 'patient_code' => 'T-001',
        'doctor_id' => $doctorA->id, 'doctor_name' => 'T', 'appointment_id' => (string) Str::uuid(),
        'appointment_datetime' => now()->format('Y-m-d H:i:s'),
        'services' => '[]', 'price' => 100000, 'discount' => 0, 'billing' => 150000,
        'payment_method' => 'CASH', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $overviewA = app(DashboardService::class)->getDoctorDataOverview($doctorA->id);
    $this->assertSame(150000.0, $overviewA['revenue']);
}
```

- [ ] **Step 2: Run test → FAIL** (`php artisan test --filter=test_doctor_overview_scopes_revenue_to_own_transactions` — kunci `revenue` belum ada).
- [ ] **Step 3: Implementasi di `DashboardService`:**
  1. Ekstrak loop tren (mingguan/bulanan/tahunan + `prev`) dari `getAdminDataOverview()` menjadi `private function buildRevenueAnalytics($baseQuery): array` — struktur kembalian sama seperti `$revenueAnalytics` lama, TANPA `new_patients` (tidak dipakai view aktif). Query per bucket meng-clone `$baseQuery` lalu menambah filter tanggal.
  2. Admin: ganti blok lama dengan `$revenueAnalytics = $this->buildRevenueAnalytics(DB::table('transactions')->whereNull('canceled_at'));`; tambah `'today_appointments' => DB::table('appointments')->whereNull('canceled_at')->whereDate('date', date('Y-m-d'))->orderBy('time_start')->limit(20)->get()->map(fn ($r) => (array) $r)->all()`.
  3. Doctor: definisikan `$monthTx = (clone $transactions)->whereNull('canceled_at')->where('appointment_datetime', '>', date('Y-m-01'))->where('appointment_datetime', '<=', date('Y-m-t 23:59:59'));` lalu: `'revenue' => (float) (clone $monthTx)->sum('billing')`, `'transactions' => (int) (clone $monthTx)->count()` (menggantikan query transaksi lama), `'revenue_analytics' => $this->buildRevenueAnalytics((clone $transactions)->whereNull('canceled_at'))`, `'today_appointments' => (clone $appointments)->whereDate('date', date('Y-m-d'))->orderBy('time_start')->get()->map(fn ($r) => (array) $r)->all()`, `'recent_records' => DB::table('medical_records')->when(!empty($doctorID), fn ($q) => $q->where('doctor_id', $doctorID))->orderByDesc('created_at')->limit(5)->get()->map(fn ($r) => (array) $r)->all()`.
- [ ] **Step 4: Run `php artisan test --filter=DashboardTest` → semua PASS.**
- [ ] **Step 5: Commit** — `feat(dashboard): scope doctor overview revenue & add shared analytics helper`

### Task 2: Controller registry + view partials (SATU task, agar tidak pernah 500)

**Files:**
- Modify: `app/Http/Controllers/General/DashboardController.php`
- Modify: `resources/views/dashboard.blade.php`
- Create: `resources/views/dashboard/widgets/{kpi-row,chart-trend,queue-today,billing-methods,patients-incomplete,recent-activities}.blade.php`
- Test: `tests/Feature/DashboardTest.php`

**Interfaces:**
- Consumes: kunci overview dari Task 1; helper `toObjects()` yang sudah ada di controller.
- Produces: `$data->widgets` = Collection of `{ view: string, payload: object }`; `$data->role`, `$data->roleLabel`. Partial menerima `$widget` (payload) + `$data`.

- [ ] **Step 1: Tulis test failing untuk tampilan widget**

Tambahkan ke `DashboardTest`:

```php
public function test_admin_dashboard_renders_widget_partials(): void
{
    config(['cache.default' => 'database']);
    Cache::forget('dashboard:admin-overview');

    $response = $this->actingAs($this->verifiedUser('manajemen@gmail.com'))
        ->get(route('dashboard'));
    $response->assertOk();
    $this->assertStringContainsString('data-widget="kpi-row"', $response->getContent());
    $this->assertStringContainsString('data-widget="patients-incomplete"', $response->getContent());
}
```

- [ ] **Step 2: Run test → FAIL** (view lama belum punya `data-widget`).
- [ ] **Step 3: Ganti `index()` + tambah registry di `DashboardController`:**

```php
public function index(Request $request)
{
    $roles = auth()->user()->getRoleNames();
    $role = $roles->contains('manajemen') ? 'admin' : ($roles->first() ?? 'guest');

    $overview = match ($role) {
        'admin'  => $this->service->getAdminDataOverview(),
        'doctor' => $this->service->getDoctorDataOverview(auth()->user()->id),
        'nurse'  => $this->service->getDoctorDataOverview($request->doctor),
        default  => null,
    };

    $data = (object) [
        'role' => $role,
        'roleLabel' => __('dashboard.role.'.$role),
        'widgets' => $this->buildWidgets($role, $overview),
    ];

    return view('dashboard', ['data' => $data]);
}

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
            continue; // tanpa data → tidak dirender
        }
        $widgets->push((object) ['view' => $view, 'payload' => $payload]);
    }

    return $widgets;
}

private function widgetMap(): array
{
    return [
        'kpi-row'             => [['admin', 'doctor', 'nurse'], 'kpiRow'],
        'chart-trend'         => [['admin', 'doctor'], 'chartTrend'],
        'queue-today'         => [['admin', 'doctor', 'nurse'], 'queueToday'],
        'billing-methods'     => [['admin'], 'billingMethods'],
        'patients-incomplete' => [['admin'], 'patientsIncomplete'],
        'recent-activities'   => [['admin', 'doctor'], 'recentActivities'],
    ];
}
```

Provider (`private function x(array $o, string $role): ?object`):
- `kpiRow` → `(object) ['revenue' => GeneralHelper::floatToRupiah((float)($o['revenue'] ?? 0)), 'transactions' => $o['transactions'] ?? 0, 'patients' => $o['patients'] ?? null, 'new_patients' => $o['new_patients'] ?? null, 'medical_records' => $o['medical_records'] ?? null, 'scoped' => $role !== 'admin']`.
- `chartTrend` → null bila `empty($o['revenue_analytics'])`; else `(object) ['analytics' => $o['revenue_analytics']]`.
- `queueToday` → selalu return: `(object) ['appointments' => $this->toObjects($o['today_appointments'] ?? [])]` (empty state bermakna operasional).
- `billingMethods` → null bila kosong; else `(object) ['methods' => $this->toObjects($o['payment_methods'])]`.
- `patientsIncomplete` → null bila `($o['incomplete_count'] ?? 0) === 0`; else `(object) ['count' => $o['incomplete_count'], 'patients' => $this->toObjects($o['incomplete_patients'])]`.
- `recentActivities` → admin: `recent_transactions` bila ada; doctor: `recent_records` bila ada; payload `(object) ['type' => 'transactions'|'records', 'rows' => $this->toObjects(...)]`; null bila kosong.

- [ ] **Step 4: Refactor `resources/views/dashboard.blade.php`** — header card: badge role `$data->roleLabel`, judul dinamis (admin: "Dashboard Manajemen Klinik"; doctor: "Dashboard Dokter"; nurse: "Dashboard Perawat"; guest: "Dashboard"), quick actions via `@role`/`@can` yang sudah ada: Pasien Baru + Transaksi (admin saja), link Workspace (doctor/nurse). Kemudian loop:

```blade
@foreach ($data->widgets as $widget)
    @includeIf('dashboard.widgets.'.$widget->view, ['widget' => $widget->payload, 'data' => $data])
@endforeach
```

Setiap partial dibungkus `<section data-widget="..." class="space-y-6">` — atribut inilah yang dites.

- [ ] **Step 5: Pindahkan blok view lama ke partial** (markup yang sudah ada dipindah, bukan ditulis ulang):
  - `kpi-row.blade.php`: grid 4 kartu KPI lama. Kartu revenue memakai `card-shiny-emerald` — sub-label `{{ $widget->scoped ? __('dashboard.kpi.revenue_own') : __('dashboard.kpi.revenue_clinic') }}`; kartu "Pasien Terdaftar" & "Pasien Baru" dibungkus `@if ($widget->patients !== null)`; dokter melihat "Rekam Medis Bulan Ini" dari `$widget->medical_records` menggantikan kartu pasien bila pasien null; perawat: kartu kunjungan saja + kartu rekam medis.
  - `chart-trend.blade.php`: blok chart penuh lama; `x-data="revenueChartComponent(@js($widget->analytics))"`; label "Analisis Pendapatan Klinik" → `{{ __('dashboard.chart.title') }}`.
  - `queue-today.blade.php` (BARU): card `content-card` berisi tabel jam (`time_start`), pasien (`patient_name`), layanan (potong `services` JSON via `@json` → tampil nama pertama; gunakan helper: `collect(json_decode($a->services ?? '[]', true))->first()['name'] ?? '—'`), status (`recorded_at` → Selesai; `confirmed_at` → Dikonfirmasi; else Menunggu). Empty state: "Tidak ada antrian hari ini."
  - `billing-methods.blade.php` (BARU): card ringkas baris per method: nama, jumlah transaksi, `GeneralHelper::floatToRupiah($m->total_amount)`, badge persentase dari total.
  - `patients-incomplete.blade.php`: tabel incomplete lama, `$data->incomplete_*` → `$widget->*`.
  - `recent-activities.blade.php` (BARU): tabel 5 baris terakhir; header/kolom tergantung `$widget->type` (`transactions`: kode, pasien, total `billing`; `records`: tanggal, pasien, diagnosis ringkas).

- [ ] **Step 6: Buat file lang sementara minimal** `lang/{id,en}/dashboard.php` dengan key yang dipakai partial (role.*, kpi.*, chart.title, queue.*, billing.title, incomplete.*, recent.*) — teks ID; EN disempurnakan Task 3.
- [ ] **Step 7: Run `php artisan test --filter=DashboardTest` → PASS** (termasuk test nurse lama yang `assertOk`), lalu `php artisan view:clear`.
- [ ] **Step 8: Commit** — `feat(dashboard): render role-gated widget partials on single dashboard`

### Task 3: i18n lengkap ID/EN + polish

**Files:** Modify `lang/id/dashboard.php`, `lang/en/dashboard.php`, partials.

- [ ] **Step 1: Lengkapi kedua file lang** — semua key: `role.admin/doctor/nurse/guest`, `header.title_admin/title_doctor/title_nurse/title_guest`, `kpi.revenue_own/revenue_clinic/visits/records/patients_total/patients_new/patients_new_badge`, `chart.title/period_weekly/period_monthly/period_yearly/mode_revenue/mode_visits/vs_prev`, `queue.title/empty/th.time/th.patient/th.service/th.status/status_done/status_confirmed/status_waiting`, `billing.title/method_other/th.method/th.count/th.amount`, `incomplete.title/hint/count_badge/empty_title/empty_hint/action/see_all`, `recent.title_transactions/title_records/th.code/th.patient/th.total/th.date/th.diagnosis/empty`.
- [ ] **Step 2: Ganti sisa string hardcoded di header + partials** dengan `__()` (tanggal via `Carbon::translatedFormat` mengikuti locale).
- [ ] **Step 3: Run** `php artisan test --filter="NewFeaturesTest"` → PASS (test bahasa & switcher).
- [ ] **Step 4: Commit** — `feat(dashboard): translate role-gated dashboard (ID/EN)`

### Task 4: Verifikasi akhir + gating perawat

- [ ] **Step 1: Tambah test gating** ke `DashboardTest`:

```php
public function test_nurse_dashboard_hides_financial_widgets(): void
{
    config(['cache.default' => 'database']);
    Cache::forget('dashboard:doctor-overview:all');

    $response = $this->actingAs($this->verifiedUser('nurse@gmail.com'))->get(route('dashboard'));
    $response->assertOk();
    $this->assertStringNotContainsString('Pendapatan', $response->getContent());
}
```

- [ ] **Step 2:** `php artisan test` → seluruh suite PASS.
- [ ] **Step 3:** `npm run build` → sukses (partial memakai class komponen yang sudah didukung dark.css); `php artisan cache:clear`.
- [ ] **Step 4:** Commit + laporkan ke user: minta verifikasi visual 3 role (light & dark) di browser.
