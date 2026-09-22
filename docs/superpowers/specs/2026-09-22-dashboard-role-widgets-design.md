# Design: Dashboard Satu Halaman dengan Widget Role-Gated

Tanggal: 2026-09-22
Status: Menunggu review
Opsi dipilih: **A** (dari brainstorm — A: widget role-gated, B: perkaya doctor, C: Livewire modular)

## Latar Belakang

Saat ini ada dua dashboard yang terpisah total:

- **Manajemen/admin** (`resources/views/dashboard.blade.php`, blok admin): KPI pendapatan, tren grafik, analisis kelengkapan data pasien.
- **Dokter/perawat**: view `pages/general/dashboard.blade.php` yang ternyata **sudah mati** (tidak dirender controller mana pun). Faktanya semua role sekarang menerima view `dashboard.blade.php` yang sama dengan data berbeda dari `DashboardService`.

Keluhan user: "kenapa data di manajemen dan dokter beda — di dokter gak ada total pendapatan dll". Prinsip yang dilanggar: **yang seharusnya membedakan role hanyalah scope data & izin, bukan layout, analisis, atau kualitas informasi.**

## Prinsip Desain

1. **Satu halaman dashboard** untuk semua role (`resources/views/dashboard.blade.php`).
2. **Widget = unit independen** dengan: judul, izin role, scope data, dan template partial.
3. **Widget registry** di `DashboardController`: daftar widget → role yang boleh melihat → service method pemasok data. Controller mengumpulkan data hanya untuk widget yang dirender.
4. **Scope data mengikuti role**: dokter selalu melihat data miliknya (`doctor_id = auth()->id()`); manajemen melihat seluruh klinik; perawat melihat antrian operasional cabang.
5. Layout tetap konsisten: **header + aksi cepat → baris KPI → grafik → daftar kerja**. Yang berubah antar role hanyalah widget mana yang tampil.

## Widget Registry (usulan awal)

| Widget | Sumber data | manajemen | doctor | nurse |
|---|---|---|---|---|
| KPI Pendapatan bulan ini | `transactions` (scope: klinik / milik sendiri) | ✅ klinik | ✅ jasa miliknya | ❌ |
| KPI Kunjungan bulan ini | `transactions` | ✅ klinik | ✅ miliknya | ✅ antrian aktif |
| KPI Pasien terdaftar + baru | `patients` | ✅ | ✅ (pasien yang pernah ditangani) | ✅ (read-only) |
| Grafik tren (revenue/visits, mingguan/bulanan/tahunan) | `revenue_analytics` | ✅ | ✅ miliknya | ❌ |
| Breakdown metode pembayaran | `payment_methods` | ✅ | ❌ | ❌ |
| Antrian kunjungan hari ini | `appointments` | ✅ | ✅ miliknya | ✅ (fokus utama) |
| Pasien data belum lengkap | `incomplete_patients` | ✅ | ❌ | ❌ |
| Nota/rekammedis terbaru | `recent_transactions` | ✅ | ✅ miliknya | ❌ |

Aturan: widget tanpa data pada scope-nya **tidak dirender** (bukan tampil kosong), kecuali kosong-nya bermakna operasional (mis. antrian kosong hari ini tetap tampil dengan empty state).

## Perubahan Teknis

### 1. `DashboardService`
- `getAdminDataOverview()` tetap (sudah array serialize-safe, cache 60 dtk).
- `getDoctorDataOverview($doctorID)` diperkaya: tambah `revenue` (sum billing milik dokter), `revenue_analytics` versi scoped, dan kembalikan antrian hari ini.
- Payload cache **tetap array murni** — mengikuti aturan `serializable_classes = false` (regresi `__PHP_Incomplete_Class` sudah dipatch + test `DashboardTest`).

### 2. `DashboardController`
- Ganti if-per-role dengan **registry**:

```php
private function widgetMap(): array
{
    return [
        'kpi.revenue'        => ['roles' => ['admin', 'doctor'], 'provider' => 'revenueKpi'],
        'chart.trend'        => ['roles' => ['admin', 'doctor'], 'provider' => 'trendChart'],
        'queue.today'        => ['roles' => ['admin', 'doctor', 'nurse'], 'provider' => 'todayQueue'],
        'patients.stats'     => ['roles' => ['admin', 'doctor'], 'provider' => 'patientStats'],
        'billing.methods'    => ['roles' => ['admin'], 'provider' => 'paymentMethods'],
        'patients.incomplete'=> ['roles' => ['admin'], 'provider' => 'incompletePatients'],
        'recent.activities'  => ['roles' => ['admin', 'doctor'], 'provider' => 'recentActivities'],
    ];
}
```

- `index()` me-resolve role ('manajemen' → 'admin'), loop registry, panggil provider, kumpulkan `$data->widgets` (Collection berisi nama partial + payload).
- View mem-render `@foreach ($data->widgets as $widget) @include("dashboard.widgets.{$widget->view}", ['widget' => $widget->payload]) @endforeach`.

### 3. View
- `resources/views/dashboard.blade.php` → kerangka + header (judul & aksi cepat menyesuaikan role).
- Partials baru di `resources/views/dashboard/widgets/*.blade.php` (kpi-revenue, kpi-visits, kpi-patients, chart-trend, queue-today, billing-methods, patients-incomplete, recent-activities).
- Chart tetap `revenueChartComponent` (Alpine) — hanya datasetnya yang di-scope.

### 4. i18n
- Semua label widget via lang key baru `lang/{id,en}/dashboard.php` (mengikuti konvensi `__()` yang sudah ada).

### 5. Keamanan
- Scope query dokter **wajib** `where doctor_id` di level service (bukan view) — prinsip sama dengan `WorkspaceTest: doctor workspace scoped to own visits`.
- Tidak ada endpoint baru; tetap satu route `dashboard` dengan middleware `auth` + `verified`.

## Testing

- Perluas `DashboardTest`:
  - `test_doctor_dashboard_scopes_revenue_to_own_transactions` (buat 2 dokter + transaksi masing-masing; assert angka KPI dokter = miliknya saja).
  - `test_nurse_dashboard_hides_financial_widgets` (assert string pendapatan tidak muncul di response).
  - `test_admin_dashboard_shows_clinic_wide_widgets` (regresi widget klasik).
- Browser (sekali di akhir): screenshot 3 role light + dark untuk verifikasi layout.

## Roadmap Implementasi (bertahap)

1. **Fase 1** — Registry + refactor controller/service, 3 partial pertama (KPI ×2, antrian) + test scope. _(inti, ~1 sesi)_
2. **Fase 2** — Sisa partial (chart scoped, billing-methods, incomplete, recent) + i18n ID/EN + dark mode untuk semua partial.
3. **Fase 3** (opsional) — Pindah widget ke komponen Livewire bila butuh auto-refresh (mis. antrian live); registry-nya tinggal diganti class Livewire.

## Yang Sengaja Tidak Dilakukan (YAGNI)

- Tidak membuat halaman dashboard baru per role — satu view cukup.
- Tidak mengganti `pages/general/dashboard.blade.php` (view mati) — usul: hapus di fase pembersihan terpisah.
- Tidak menambah role/permission baru — memakai role yang ada (`manajemen`, `doctor`, `nurse`).
