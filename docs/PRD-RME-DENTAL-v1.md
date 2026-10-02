# PRD — RME Dental Klinik Gigi (v1 aktual client → v2 update kita)

> **v1 = versi bawaan client, dibekukan.** Kode `dc2b1f6`, tag `v1.0.0`, branch `release/v1`.
> Rollback kapan saja ke `release/v1` / `v1.0.0` — aman.
> **v2 = update kita**, dikerjakan di `develop/v2` + `feature/*`, rilis ke `main` (production).
> Pelengkap: `BLUEPRINT-RME-DENTAL-v1.md` (matriks + skema), `ROADMAP-v1-to-v2.md` (fase),
> `BRANCHING.md` (alur branch + rollback).
> Acuan produk: fitur publik DentalDiary (RME, odontogram, ICD-10/ICD-9, appointment,
> billing, inventory, SATUSEHAT, WhatsApp). Detail internal DentalDiary yang tidak
> dipublikasikan TIDAK diklaim — bagian itu adalah desain kita dan ditandai `[DESAIN-KITA]`.

## 1. Ringkasan eksekutif

Sistem adalah **RME + Clinic Management** khusus klinik gigi: pasien → appointment →
antrian → pemeriksaan (termasuk odontogram) → diagnosis ICD-10 → tindakan ICD-9 →
resep → rencana perawatan → billing → pembayaran → laporan, plus inventory,
multi-cabang (nanti), WhatsApp automation (nanti), dan integrasi SATUSEHAT (nanti).

Keputusan yang sudah dikunci:

1. **Tanpa WhatsApp dulu** — reminder diganti calendar view + pengingat in-app.
2. **Tanpa rewrite** — v2 tetap Laravel 13 + Livewire + Blade + Spatie (monolit modular).
   Usulan Next.js/Prisma/PostgreSQL dari draft awal DITOLAK (biaya rewrite > manfaat).
3. **Tanpa role baru** di v2 awal — 4 role aktual
   (`manajemen, admin, doctor, nurse`) dipetakan ke jobdesk profesional.
   `Owner ≈ manajemen`, `Front Office ≈ admin`, kasir dirangkap `admin/nurse`
   sesuai praktik klinik saat ini. `Cashier`/`Super Admin` terpisah hanya bila
   benar-benar dibutuhkan nanti.
4. **Tidak ada klaim "Terintegrasi SATUSEHAT / WA otomatis"** sebelum bridging
   production lolos — klaim prematur = risiko hukum/marketing.

## 2. Definisi v1 (versi client — FROZEN)

### 2.1 Stack & fondasi

Laravel 13, PHP 8.3, Livewire 3 + Volt, Blade + Tailwind, Spatie Permission 8,
SQLite (dev) / MySQL/PgSQL (portabel — service menghindari fungsi spesifik DB),
auth Breeze (email+password, verified, throttle login 5x), UUID sebagai PK
di hampir semua tabel domain.

### 2.2 Modul v1 yang ADA dan jalan

| # | Modul | Route (lihat Blueprint §2) | Data | Status v1 |
|---|---|---|---|---|
| 1 | Dashboard per role | `dashboard` | ringkasan admin (omzet, pasien, NIK tak lengkap), dokter (antrian+RM), nurse | Jalan |
| 2 | Pasien | `patients.*` + `api/patients/lookup` | `patients` + `patient_addresses`; kolom SATUSEHAT-ready (`nik, ihs_id, birth_place, satusehat_consent`) ADA di DB tapi TIDAK disimpan form (tech debt D-01) | Jalan, berlubang |
| 3 | Appointment | `appointments.*` + confirm + 3 API lookup | `appointments` + snapshot pasien/dokter, `services` JSON, status via `confirmed/paid/recorded/canceled_at` | Jalan (list, belum calendar) |
| 4 | Jadwal dokter | `schedules.*` (tanpa create/show/edit/update) + update_status | `schedules` (hari enum, jam, availability) | Jalan |
| 5 | Layanan & kategori | `services.*`, `categories.*` | `services` (harga bawah-atas, komisi dokter string) + `categories` | Jalan |
| 6 | Master user | `admins.*`, `doctors.*`, `nurses.*` (tanpa show) | `users` + `admins/doctors/nurses` + `user_addresses`; `doctors.ihs_id` ADA tapi tak ada form (D-02) | Jalan |
| 7 | Rekam medis | `medical-records.*` + lookup + history | `medical_records` (teks anamnesis/diagnosis/therapy + foto before/after) + `diagnosis_codes` (ICD10/ICD9/SNOMED, 6 seed) + pivot `medical_record_diagnoses` + wajib ≥1 kode (`MedicalRecordRequest`) | Jalan; ICD-9/10 campur satu field (D-03) |
| 8 | Transaksi/nota | `transactions.*` + usul-kunci-approve batal + reschedule API | `transactions` (sequence YYNNNNN, DP, cicilan flag, lock) + `transaction_services/nurses` | Jalan |
| 9 | Cicilan | `installments.index/show` | `installments` + `installment_steps` | Jalan (read-only) |
| 10 | Omzet | `incomes.*` + lookup API | agregat `transactions` (filter dokter bila role doctor) | Jalan |
| 11 | Gaji dokter | `GET /salaries` (tanpa nama route!) | hitung share 30–35%, rontgen, shift, target 55 | Jalan, tak ada di sidebar |
| 12 | Export | `export-transactions` (XLSX) | `MonthlyReports` | Jalan |
| 13 | Profil + bahasa | `profile.*`, `switch-language` | profil per role + ganti password | Jalan |
| 14 | Follow-up WA | `api.followup.whatsapp` redirect `wa.me` manual | — | BUKAN Official API |

### 2.3 Yang TIDAK ADA di v1 (masuk v2 bertahap)

Odontogram, queue/antrian, treatment plan, resep terstruktur, SOAP terstruktur,
tabel `visits`, split `invoices/payments/receipts`, inventory, expenses,
multi-branch (`branch_id`), WhatsApp Official API, SATUSEHAT bridging,
`audit_logs` formal, e-sign dokter, calendar view.

### 2.4 Tech debt v1 yang dicatat (D-xx, detail di Blueprint §6)

* **D-01**: `PatientRequest` tidak validasi/menyimpan `nik/ihs_id/birth_place/consent`;
  `MasterService::insert/updatePatient` tidak menyimpan kolom itu.
* **D-02**: `doctors.ihs_id` tanpa form/validasi (`UserRequest`, `UpdateDoctorRequest` diam).
* **D-03**: diagnosis ICD-10 (penyakit) + ICD-9 (tindakan) campur satu field
  `diagnosis_codes min:1` — SATUSEHAT menolak (Condition wajib ICD-10).
* **D-04**: enforcement akses kosmetik — route `auth` saja; `Appointment/Schedule/
  Service/Category/MedicalRecord/Income/InstallmentController` nol `authorize`;
  edit/update/destroy master tanpa gate; Blade `@can` bisa dilewati via URL.
* **D-05**: `medical_records` tanpa FK (orphan risk); relasi `Patient` key terbalik;
  `MedicalRecord` model `fillable` kadaluarsa.
* **D-06**: `transaction_cancellation_requests.proposed_by/decided_by` bigint vs
  `users.id` uuid (migrasi gagal di PG/MySQL ketat); `CategoryRequest` unique tanpa
  ignore; `UpdateTransactionRequest::authorize=false` (mati total);
  bug `upper_price => 1, 600000` di `ServiceProstodonsiaSeeder`; 4 seeder
  prostodonsia orphan; `transaction_services.price` string; `salaries` tanpa nama route;
  `ICD10Controller`/`AddressController` tanpa route (file `dental_icd_x.json` tak ada).

## 3. Scope v2 (update kita)

### 3.1 Fase 1 — Fondasi aman (wajib pertama)

Tutup D-01 s/d D-04 tanpa tabel besar baru: validasi + simpan NIK/IHS/consent,
`birthdate string→date`, pisah input diagnosis (ICD-10 wajib ≥1) vs tindakan (ICD-9),
`middleware can:` + `authorize()` di semua controller, perbaiki relasi/model,
migration fix D-06 yang blocking. Hasil: **v1.1**.

### 3.2 Fase 2 — RME gigi layak (uji internal)

`visits`, `odontogram_findings` (FDI 11–48/51–85 + surface + SNOMED),
`diagnoses` per visit+gigi, `treatments` ICD-9-CM → invoice otomatis,
resep terstruktur (obat KFA), treatment plan, attachments signed URL,
Doctor Workspace + calendar + queue. Hasil: **v2.0-beta**.

### 3.3 Fase 3 — Keuangan & operasional

Split `invoices(+items)/payments/receipts` (migrasi dari `transactions`, data lama
dipertahankan), doctor fee, inventory ringan + alert, expenses, laporan.
Hasil: **v2.0**.

### 3.4 Fase 4 — Integrasi (terakhir)

`SatuSehatService` (Auth → Organization → Location → Practitioner → Patient/MPI →
Encounter → Condition/Procedure/Observation/…) + sync log + dashboard monitoring,
uji **sandbox** dulu; lalu WhatsApp Official API; lalu `branch_id` multi-cabang.
Ikut spesifikasi resmi SATUSEHAT saat coding. Hasil: **v2.1+**.

### 3.5 Fase 5 — Advanced (opsional)

AI booking, voice recognition, analitik lanjutan. BUKAN prioritas.

## 4. Role & jobdesk final (dipetakan ke 4 role aktual)

| Modul | manajemen (Owner) | admin (Front Office) | doctor | nurse (Asisten) |
|---|---|---|---|---|
| Dashboard | penuh + keuangan | operasional/antrian | workspace klinis (miliknya) | antrian/bantuan |
| Pasien | CRUD + hapus (soft) | CREATE/VIEW/EDIT, tanpa hapus | VIEW | VIEW + draft anamnesis/vital |
| Appointment/Queue | CRUD | CRUD + check-in | VIEW | VIEW + kelola antrian |
| RME (anamnesis→plan) | VIEW | VIEW terbatas | **CRUD miliknya + sign/final** | VIEW + upload lampiran |
| Billing/Payment/Cicilan | CRUD + approve batal | VIEW + usul batal | VIEW terbatas (kasusnya) | VIEW (bertindak kasir bila ditugaskan) |
| Inventory/Expense | CRUD | CRUD inventory | VIEW | VIEW |
| Laporan/User/Setting | CRUD | operasional/terbatas | terbatas/none | none |
| SATUSEHAT/WA | MANAGE | operasi terbatas | - | - |

Aturan keras: RME status SIGNED hanya dikoreksi beraudit (who/what/when/old/new/why);
tanpa hard-delete rekam medis; file medis private + signed URL; status medis dan
status billing kolom terpisah.

## 5. Kebutuhan non-fungsional

* **Regulasi**: Permenkes 24/2022 (RME wajib ≤31 Des 2023, interoperabilitas
  SATUSEHAT, registrasi sistem ke Kemenkes, backup, retensi ≥25 thn, audit mutu),
  UU PDP (consent, minimisasi, keamanan, hak pasien).
* **Keamanan**: RBAC server-side, audit log, password hash, session, HTTPS,
  rate limiting, signed URL, backup di lokasi berbeda (aturan Fase 1–2).
* **Kinerja**: halaman pasien & visit < 2 dtk; list paginasi 20.
* **Portabilitas**: kode tetap jalan di SQLite/MySQL/PgSQL (lanjutkan pola
  `TransactionService::nextSequence`, hindari fungsi spesifik DB).
* **Skalabilitas**: monolit modular cukup; siapkan `branch_id` sejak Fase 2
  walau single-branch dulu. Tanpa microservices/K8s/GraphQL.

## 6. Kriteria penerimaan

1. **v1**: `release/v1` terverifikasi sama dengan versi client kecuali 4 file `docs/`
   baseline; satu-satunya test merah (`medical-records.index` 500) tercatat sebagai
   known issue di `ROADMAP-v1-to-v2.md` dan masuk antrean Fase 1.
2. **Fase 1**: NIK/IHS/consent tersimpan; ada ≥1 ICD-10 per RM; test negatif
   permission lolos (nurse≠hapus pasien, admin≠edit RME, doctor≠omzet global).
3. **Fase 2**: alur `registrasi→appointment→check-in→queue→visit→odontogram→
   diagnosis→tindakan→resep→billing→payment→kontrol` end-to-end per role.
4. **Fase 4**: payload sandbox Gigi lolos validasi SATUSEHAT (Patient/Encounter/Condition).

## 7. Risiko tercatat

* Klaim integrasi prematur; link `wa.me` bukan automation; `storage/framework/views`
  untracked membanjiri `git status` (perbaiki `.gitignore` di Fase 1);
  `medical_records` tanpa FK rawan orphan sampai Fase 2.
