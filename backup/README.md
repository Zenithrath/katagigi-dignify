# Backup fitur non-inti (diparkir 2026-09-24)

Fokus saat ini: **alur pelayanan pasien saja**
(appointment → check-in → antrean perawat → antrean dokter →
pemeriksaan + RME → pembayaran → selesai).

## Isi folder ini

- `views/` — file Blade yang dipindah dari `resources/views/` via `git mv`
  (riwayat git tetap terjaga, terlihat sebagai rename di `git status`).
- `tests/` — file test fitur parkir via `git mv`.

## Cara mengembalikan satu fitur

Contoh kembalikan inventory:

```powershell
git mv backup/views/pages/inventory resources/views/pages/inventory
```

Lalu uncomment blok `PARKED 2026-09-24` yang sesuai di:
- `routes/web.php` (inventory, expenses, finance-report, doctor-fees,
  salaries, revenue-report, assistant-payroll, holidays, attendances,
  audit-logs, installments, incomes, satusehat, whatsapp)
- `routes/integration.php` (satusehat onboarding / org-profile / credentials)
- `resources/views/components/sidebar-nav.blade.php` (tambah link menu)
- `app/Http/Controllers/General/DashboardController.php::widgetMap`
  (daftarkan ulang `chart-trend`, `billing-methods` bila perlu)

Lalu kembalikan test dari `backup/tests/` dan jalankan
`php artisan test` sampai hijau.

## Pengecualian (sengaja TIDAK diparkir)

- `pages/patient/record/*` (rekam medis legacy): route dipakai
  `PermissionEnforcementTest`, `ClinicFlowTest`, `SaveNikTest` —
  hanya disembunyikan dari sidebar.
- Route + controller + service fitur parkir tetap ada di `app/` dan
  `routes/*.php` (dikomentari) — yang dipindah hanya view & test.
- API `api/regions/lookup` dan `api/kfa/lookup` tetap aktif
  (dipakai form pasien & resep).
