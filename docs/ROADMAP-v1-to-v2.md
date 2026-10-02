# Roadmap v1 → v2

## 0. Posisi saat ini

* `v1.0.0` = kode pada `dc2b1f6` + 4 dokumen ini. Stabil, bisa demo & operasional.
* Semua pekerjaan v2 di `develop/v2` dan branch fitur. `main` = production.

## 1. Gap yang harus ditutup (prioritas)

> **Known failure v1 (18 Sep 2026) — SUDAH DIPERBAIKI:**
> `ClinicSmokeTest::test_manajemen_can_open_all_main_pages` sempat gagal
> (`medical-records.index` 500, bug bawaan v1). Diperbaiki di commit
> `2ae1fb5`/`bec6f54`, terverifikasi hijau 19 Sep 2026
> (`ClinicSmokeTest` 4/4 + `ClinicFlowTest` 1/1 pass). Baris ini dipertahankan
> sebagai riwayat; tidak lagi masuk antrean Fase 1.

| # | Gap | Dampak jika dibiarkan |
|---|---|---|
| 1 | `MasterService insert/update` tidak menyimpan `nik/ihs_id/consent` | Data SATUSEHAT-ready kosong → bridging gagal |
| 2 | Diagnosis campur ICD-10/9/SNOMED satu field, tanpa wajib ICD-10 | Ditolak SATUSEHAT `Condition` |
| 3 | Permission hanya di Blade, controller/route terbuka | Gagal survei keamanan, data bisa diubah lintas role |
| 4 | Tanpa `visits`, `odontogram`, resep terstruktur | Use Case Gigi tidak bisa dipenuhi |
| 5 | Billing `transactions` campur aduk | Cicilan & jasa medis sulit diaudit |
| 6 | Tanpa `audit_logs`, file public | Gagal Permenkes 24/2022 |

## 2. Fase implementasi

* **Fase 1 — Fondasi aman (1–2 sprint)**: perbaiki #1–#3. Validasi NIK/IHS,
  split diagnosis vs tindakan di form+Request, `middleware can:` + `authorize()`,
  `birthdate→date`. Tanpa tabel baru besar. Hasil: v1.1.
* **Fase 2 — RME Gigi (2–3 sprint)**: `visits`, `odontogram_findings`,
  `diagnoses`, `treatments`, resep terstruktur, treatment plan, attachments signed URL,
  Doctor Workspace + calendar + queue. Hasil: v2.0-beta (layak uji internal).
* **Fase 3 — Keuangan & operasional**: split invoice/payment/receipt (migrasi dari
  transactions, data lama dipertahankan), doctor fee, inventory ringan, expense,
  laporan. Hasil: v2.0.
* **Fase 4 — Integrasi**: `SatuSehatService` + sync log + dashboard monitoring
  (sandbox dulu), lalu WhatsApp Official API, lalu `branch_id` + multi-branch.
* **Fase 5 — Advanced (opsional)**: AI booking, voice recognition, analitik lanjutan.

## 3. Aturan main v2

1. Satu branch fitur = satu perubahan (`feature/odontogram`, `fix/save-nik`, …).
2. Setiap migrasi harus reversible (`down()`) + seeder diperbarui.
3. Setiap perubahan permission wajib ada test (positif + negatif via URL langsung).
4. Dilarang klaim "Terintegrasi SATUSEHAT / WA otomatis" sebelum Fase 4 lolos.
5. `main` hanya menerima merge dari `develop/v2` yang sudah hijau (`php artisan test`).

## 4. Rollback (jika v2 bermasalah)

```powershell
# Lihat titik aman
git tag --list; git branch -a
# Kembali darurat ke v1 (tanpa hapus kerjaan v2 — v2 tetap ada di develop/v2)
git checkout release/v1
# atau reset production ke tag:
git checkout main; git reset --hard v1.0.0
```

Detail lengkap di `BRANCHING.md`.
