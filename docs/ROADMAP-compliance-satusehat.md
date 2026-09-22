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
- [ ] Migrasi: `master_informed_consent_templates` (judul, isi HTML/blade, jenis: cabut/PSA/bedah)
- [ ] Migrasi: `informed_consents` (visit_id, template_id, pasien/wali, ttd canvas PNG, ip, user_agent, waktu)
- [ ] Form tanda tangan canvas *touchscreen* di halaman visit (komponen Livewire)
- [ ] Cetak/unduh PDF consent berttd
- [ ] Test: consent wajib sebelum visit berstatus SIGNED (middleware/validator)

### 1.2 Medical Record Addendums (No Hard Delete)
- [ ] Migrasi: `medical_record_addendums` (model, model_id, field, nilai_lama, nilai_baru, alasan, aktor)
- [ ] Matikan hard-delete model medis (odontogram findings, diagnoses, treatments, vitals) → pakai SoftDeletes + event listener yang menulis addendum
- [ ] Tampilkan riwayat koreksi di detail rekam medis
- [ ] Test: edit medis menghasilkan addendum; delete di-block/soft

## Fase 2 — Infrastruktur SatuSehat Multi-Cabang (syarat 2 cabang)

### 2.1 Kredensial Per Cabang
- [ ] Migrasi: `satusehat_credentials` (branch_id, client_id, client_secret terenkripsi, organization_id, location_id, environment dev/prod)
- [ ] Refactor `SatuSehatService`: token & base URL resolve dari cabang visit, bukan `.env`
- [ ] UI admin: kelola kredensial per cabang (mask secret)
- [ ] Migrasi: tambah `satusehat_org_id`/`location_id` di `branches`
- [ ] Test: visit cabang A memakai token cabang A

### 2.2 Queue + Retry
- [ ] Job `SyncVisitToSatusehat` (queued) — ganti pemanggilan sinkron di controller
- [ ] Command `satusehat:retry` + tombol Retry per log FAILED di UI (controller sudah ada, tambah aksi)
- [ ] Rate-limit & backoff (429 → retry with delay)
- [ ] Test: gagal API → log FAILED → retry sukses → log SUCCESS

### 2.3 Onboarding Master SatuSehat
- [ ] Sinkron Practitioner (IHS dokter via API lookup NIK)
- [ ] Sinkron Organization & Location per cabang saat kredensial diset

## Fase 3 — Kelengkapan Klinis

### 3.1 Vital Signs Lengkap
- [ ] Migrasi: nadi, suhu, respirasi, **status kehamilan** (enum: tidak/hamil/trimester) di `examinations`
- [ ] Payload Observation tambahan (LOINC 8867-4 nadi, 8310-5 suhu, 9279-1 resp)
- [ ] Form + tampilan di detail visit
- [ ] Test: payload FHIR valid untuk 4 observasi baru

### 3.2 Pemeriksaan Dental Standar Kemenkes
- [ ] Migrasi: kolom OHI-S (debris, calculus), DMF-T/def-t per gigi atau agregat, oklusi, torus, palatum, diastema di `examinations`
- [ ] Form odontogram panel: input lengkap
- [ ] Test: hasil DMF-T terhitung benar dari findings

### 3.3 Radiologi & Foto Intraoral
- [ ] Migrasi: `radiology_studies` (visit_id, jenis: periapikal/panoramik/intraoral, file_path, taken_at)
- [ ] Migrasi: `diagnostic_reports` (study_id, reading dokter, `satusehat_report_id`)
- [ ] Upload multi-file (PNG/JPG dulu; DICOM opsional)
- [ ] Payload FHIR DiagnosticReport + Media
- [ ] Test: upload → report → sync

## Fase 4 — Master Kamus & Administratif

### 4.1 Master Wilayah Kemendagri
- [ ] Migrasi + seeder: `master_wilayah` (kode, nama, level: provinsi/kab/kec/kel)
- [ ] Ganti alamat pasien: dropdown berantai + simpan kode wilayah
- [ ] Payload Address FHIR pakai kode Kemendagri
- [ ] Test: payload Address valid

### 4.2 Master KFA / LOINC / SNOMED lokal
- [ ] Seeder import kamus KFA (obat & BHP) → `master_kfa`
- [ ] Kolom `kfa_code` di prescription_items di-link ke master (validator)
- [ ] FHIR MedicationRequest (payload + kirim dalam pipeline syncVisit)
- [ ] Test: resep dengan KFA valid → MedicationRequest SUCCESS

### 4.3 Surat & Penjamin
- [ ] `master_letter_templates` + generator surat sakit/berobat/rujukan (PDF)
- [ ] `master_insurances` + pemilihan penjamin saat pendaftaran
- [ ] Opsional: master_suppliers untuk pengadaan

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
