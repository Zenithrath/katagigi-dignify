# Roadmap Compliance & SatuSehat — RME KataGigi

> Hasil audit kode 22 Sep 2026 (branch `develop/v2`).
> Sumber kewajiban: Permenkes No. 24/2022, standar SatuSehat Kemenkes (HL7 FHIR R4), kebutuhan operasional klinik (2 cabang: Banjarmasin & Banjarbaru).

## Ringkasan Status Audit

**✅ Sudah ada & terverifikasi:**
- Multi-cabang (`branches`, `visits.branch_id`), RBAC (permission tables)
- MPI: `patients` dengan NIK, `ihs_id`, `satusehat_consent` (gate consent UU PDP bekerja)
- ICD-10 & ICD-9-CM (tabel `diagnosis_codes` berkolom `system` + seeder)
- Odontogram FDI multi-surface (`odontogram_findings`: fdi + surface + condition)
- Diagnosis & tindakan per kunjungan, treatment plans
- Auto-deduct BHP (`StockService`), E-resep + `kfa_code`
- Billing lengkap (invoices, transactions, payments, receipts)
- Audit logs
- SatuSehat engine: OAuth2, FHIR **Patient, Encounter, Condition, Procedure, Observation** (LOINC 8480-6/8462-4 + SNOMED gigi), MPI IHS round-trip, `satusehat_sync_logs` (PENDING/SUCCESS/FAILED/SKIPPED)

**⚠️ Setengah jadi:**
- Vital signs: hanya tekanan darah — nadi/suhu/resp/**status kehamilan** belum ada
- Sinkronisasi SatuSehat: sinkron & tanpa retry (jobs table ada tapi tidak dipakai)
- Kredensial SatuSehat: `.env` global — 2 cabang butuh kredensial **per cabang**
- Alamat pasien: string bebas, belum ada kode wilayah Kemendagri

**❌ Belum ada (WAJIB):**
- Informed consent + tanda tangan digital (Permenkes 24/2022)
- Medical record addendums / no hard delete (Permenkes 24/2022)
- Pemeriksaan dental lengkap: OHI-S, DMF-T/def-t, oklusi, torus, palatum, diastema
- Radiologi/foto intraoral + diagnostic report
- Master lookup lokal: KFA, LOINC, SNOMED, wilayah Kemendagri
- FHIR MedicationRequest (resep belum dikirim ke SatuSehat)
- Template surat (sakit/berobat/rujukan) + master penjamin/asuransi
- Sinkronisasi Practitioner / Organization / Location (onboarding)

---

## Fase 1 — Compliance Legal (prioritas tertinggi, risiko regulasi)

### 1.1 Informed Consent + Tanda Tangan Digital
- [x] Migrasi: `medical_consent_records` (visit, pasien, jenis consent, teks, keputusan, pemberi, ttd file/canvas PNG, waktu)
- [x] Form tanda tangan canvas *touchscreen* di halaman visit (Alpine pointer events) + opsi unggah gambar
- [x] Cetak lembar informed consent berttd (print view, gambar TTD inline base64)
- [x] Gate: consent disetujui wajib ada sebelum visit SIGNED (VisitController::sign)
- [ ] (Opsional) Master template consent per jenis tindakan

### 1.2 Medical Record Addendums (No Hard Delete)
- [x] Migrasi: `medical_record_addendums` (model, model_id, field, nilai_lama, nilai_baru, alasan, aktor)
- [x] Matikan hard-delete model medis (odontogram findings, diagnoses, treatments, vitals) → pakai SoftDeletes + event listener yang menulis addendum
- [x] Tampilkan riwayat koreksi di detail rekam medis
- [x] Test: edit medis menghasilkan addendum; delete di-block/soft

## Fase 2 — Infrastruktur SatuSehat Multi-Cabang (syarat 2 cabang)

### 2.1 Kredensial Per Cabang
- [x] Migrasi: `satusehat_credentials` (branch_id unik, client_id, client_secret terenkripsi `encrypted` cast, organization_id, location_id, environment sandbox/production)
- [x] Refactor `SatuSehatService`: `resolveConfig(branch_id)` — token & base URL resolve dari cabang visit, fallback `.env` global; cache token per client_id
- [x] UI admin: kelola kredensial per cabang (secret tak pernah dikirim balik, tombol verifikasi OAuth)
- [x] Migrasi: tambah `satusehat_org_id`/`satusehat_location_id` di `branches`
- [ ] Test: visit cabang A memakai token cabang A

### 2.2 Queue + Retry
- [x] Job `SyncVisitToSatuSehat` (queued, WithoutOverlapping, backoff 10–300s) — dikirim bila `?queue=1` pada sinkron; tombol Retry per visit sudah ada
- [x] Rate-limit & backoff (429 → log tetap PENDING + sleep Retry-After, maks 15s)
- [ ] Command `satusehat:retry` untuk seluruh FAILED (Retry per visit sudah ada)
- [ ] Test: gagal API → log FAILED → retry sukses → log SUCCESS

### 2.3 Onboarding Master SatuSehat
- [ ] Sinkron Practitioner (IHS dokter via API lookup NIK)
- [ ] Sinkron Organization & Location per cabang saat kredensial diset

## Fase 3 — Kelengkapan Klinis

### 3.1 Vital Signs Lengkap
- [x] Migrasi: nadi, suhu, respirasi, status kehamilan (tabel `vital_signs`)
- [x] Payload Observation (LOINC 8867-4 nadi, 8310-5 suhu, 9279-1 resp, 82810-3 kehamilan)
- [x] Form + tampilan di detail visit (partial `vital-signs`)
- [x] Test: payload FHIR valid untuk observasi baru (SatuSehatTest)

### 3.2 Pemeriksaan Dental Standar Kemenkes
- [x] Migrasi: OHI-S + DMF-T di `oral_health_indices`; oklusi, torus, palatum, diastema, relasi molar/kaninus, temuan lain di `examinations`
- [x] Form pemeriksaan dental lengkap di tab Pemeriksaan + OHI-S di panel odontogram
- [x] DMF-T auto-hitung dari odontogram findings (D=karies/akar/fraktur, M=missing, F=filled/crown/implan/protesa) saat tidak diisi manual
- [x] Payload FHIR: odontogram per gigi, OHI-S, DMF-T, dan kondisi mulut lainnya (SatuSehatDental)
- [ ] Test: hasil DMF-T auto-hitung dari findings

### 3.3 Radiologi & Foto Intraoral
- [x] Migrasi: `radiology_orders` (order, modalitas, hasil baca, berkas hasil privat, `satusehat_diagnostic_report_id`)
- [x] Upload berkas hasil (PNG/JPG/webp; DICOM tak dikirim ke SSP)
- [x] Payload FHIR DiagnosticReport + Media (SatuSehatDental::diagnosticReport/mediaPayload) dalam pipeline syncVisit
- [ ] Upload multi-file per order
- [ ] Test: upload → report → sync

## Fase 4 — Master Kamus & Administratif

### 4.1 Master Wilayah Kemendagri
- [x] Migrasi + seeder: `region_codes` (kode, nama, level provinsi/kota/kec/kel; seeder 38 provinsi + kota Kalsel; impor CSV kec/kel siap)
- [x] Alamat pasien: datalist wilayah (soft chained) + simpan `region_code` di `patient_addresses`
- [x] Payload Address FHIR dengan extension administrativeCode kode Kemendagri
- [ ] Test: payload Address valid

### 4.2 Master KFA / LOINC / SNOMED lokal
- [x] Migrasi + seeder `master_kfa` (obat & BHP layanan gigi; impor CSV nasional siap)
- [x] `kfa_code` di prescription_items divalidasi ke master (Rule::exists) + API lookup `api/kfa/lookup`
- [x] FHIR MedicationRequest (payload + kirim dalam pipeline syncVisit)
- [ ] Test: resep dengan KFA valid → MedicationRequest SUCCESS

### 4.3 Surat & Penjamin
- [x] Generator surat sakit/berobat/rujukan (print view, tanpa template DB) — tab Surat di halaman visit
- [ ] `master_insurances` + pemilihan penjamin saat pendaftaran
- [ ] Opsional: master_suppliers untuk pengadaan, template surat dari DB

## Checklist Definition of Done (per fase)
- [ ] Migrasi + rollback aman
- [ ] Test fitur (positif + negatif) lolos
- [ ] i18n ID/EN lengkap untuk UI baru
- [ ] Dark mode mengikuti `dark.css`
- [ ] Audit log untuk aksi create/update medis
- [ ] `npm run build` + full suite hijau sebelum commit

## Catatan keputusan arsitektur yang sudah fix
- Payload cache harus **array murni** (`serializable_classes = false` di `config/cache.php`)
- Dashboard: satu view + widget registry role-gated (`DashboardController::widgetMap()`)
- Consent pasien = gerbang wajib sync SatuSehat (sudah berlaku untuk Patient)
