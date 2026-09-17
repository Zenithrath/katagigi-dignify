# KataGigi Dignify

Rebuild modern dari `katagigi-banjarmasin` (Laravel 9) dengan tech stack terbaru.
Repo bersih — app lama tetap jalan terpisah di production.

## Stack

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13 + PHP 8.4 |
| UI | Livewire 3 + Volt + Tailwind 4 + Vite 8 (gaya Donezo, light, hijau emerald) |
| Auth/RBAC | Breeze (register publik **ditutup**) + `spatie/laravel-permission` |
| DB dev | sqlite (tanpa driver pgsql di Laragon) |
| DB prod | **Postgres** — semua migrasi ditulis kompatibel pgsql (lihat `.env` untuk setting prod) |
| Kualitas | Pint, PHPUnit |

## Peran (baru)

| Role | Akses |
|---|---|
| `manajemen` | Semua akses (migrasi dari admin lama) + approve/reject pembatalan nota + kelola master diagnosis |
| `admin` | Reservasi + penjadwalan dokter, tarik data transaksi, **usul** pembatalan nota (terkunci sampai diputus) |
| `doctor` | Baca + tulis rekam medis (kode diagnosis wajib) |
| `nurse` | Front-office: pasien + appointment penuh, rekam medis baca |

Demo login (password `password`): `manajemen@gmail.com`, `admin@gmail.com`,
`doctor@gmail.com`, `nurse@gmail.com`.

## Fitur fondasi (sprint 1 — sudah)

- [x] Auth + 4 role + 48 permission + seeder demo
- [x] Master `diagnosis_codes` (ICD-10 / ICD-9 / SNOMED + sinonim awam) + seeder contoh
- [x] `GET /api/diagnosis-codes?q=&system=&limit=` (kode resmi wajib, freetext jadi catatan)
- [x] Komponen `<livewire:diagnosis-search>` autocomplete untuk form rekam medis
- [x] `transactions` stub + `transaction_cancellation_requests` (alur usul-kunci-approve)
- [x] Layout Donezo full-app (sidebar kartu, topbar search pill, dashboard klinik)
- [x] `SATUSEHAT_*` di `.env` (nonaktif; kolom `nik/ihs_id/consent` sudah siap di `patients`)

## Roadmap

1. **Klinik inti**: pasien (NIK+consent) → jadwal → appointment → rekam medis (kode wajib) → transaksi/cicilan
2. **Approval nota**: UI usulan (admin) + persetujuan (manajemen) + penguncian nota
3. **Laporan**: transaksi, omzet, gaji dokter, export Excel
4. **SATUSEHAT**: modul `App\Integrations\Satusehat` (OAuth2, FHIR Patient/Encounter/Condition/Procedure) async via queue

## Jalankan lokal

```powershell
composer install; npm install
php artisan migrate --seed
npm run dev   # atau: npm run build
php artisan serve
```
