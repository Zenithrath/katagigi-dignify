# Blueprint RME Dental — v1 aktual (client) → v2 (kita)

> Label: `[V1]` = ada di versi client & dibekukan (`release/v1`).
> `[V2]` = update kita. `[DESAIN-KITA]` = rancangan sendiri, bukan klaim fitur
> internal produk referensi (detail internal yang tak dipublikasikan).

## 1. Peta sistem

```text
                    LOGIN (Breeze, verified)
                      ↓
              DASHBOARD per role [V1]
                      ↓
        ┌─────────────┴──────────────┐
        ▼                            ▼
   APPOINTMENT [V1]              PATIENT [V1]
        ↓                            ↓
    CHECK-IN [V2]              PATIENT PROFILE + HISTORY [V1,κολοβό]
        ↓                            ↓
     QUEUE [V2]                      ↓
        ↓                            ↓
      DOCTOR WORKSPACE [V2] ←── CURRENT PATIENT
        ↓
   ANAMNESIS [V1 teks→V2 tabel] → EXAMINATION SOAP [V2]
        ↓
   ODONTOGRAM FDI [V2] ─┬─ DIAGNOSIS ICD-10 [V1 campur→V2 tabel]
                         └─ TREATMENT ICD-9 [V2] ─→ BILLING [V1 nota→V2 invoice]
        ↓                                                   ↓
   PRESCRIPTION [V1 teks→V2 KFA] ─→ INVENTORY [V2]      PAYMENT [V2] → RECEIPT [V2]
        ↓                                                   ↓
   TREATMENT PLAN [V2]                              FOLLOW-UP / KONTROL [V2]
        ↓                            ┌───────────────────┴───────────────────┐
   ATTACHMENTS [V1 foto→V2 signed]   ▼                                       ▼
                              WHATSAPP Official [V2-akhir]          SATUSEHAT [V2-akhir]
```

## 2. Matriks Modul → Fitur → Route → View → Role → Tabel → Workflow (V1 AKTUAL)

Legenda gate: `C=controller authorize`, `B=blade @can/@role`, `-`=tanpa gate (lubang, perbaiki Fase 1).
Role seeder: **M**=manajemen, **A**=admin, **D**=doctor, **N**=nurse.

### 2.1 Dashboard

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| Ringkasan admin (omzet, pasien, NIK tak lengkap, tren) | `GET /dashboard` `dashboard` | `dashboard.blade.php` | `auth,verified` | M/A | transactions, patients, patient_addresses | login → ringkasan → tarik data |
| Workspace dokter (antrian minggu ini, RM bulan ini) | sama | sama | sama | D (filter `doctor_id=auth.id`) | appointments, medical_records, transactions | login → antrian → layani |
| Pendamping (filter `?doctor=`) | sama | sama | sama | N | sama | bantu pantau antrian |

### 2.2 Pasien

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| List + cari | `GET /patients` `patients.index` | `patient/master/index` | B create/delete | M/A/D/N baca | patients (+address join) | cari → buka |
| Tambah | `GET/POST /patients` `create/store` | `patient/master/form` | **C** `create patient` | M/A/N (D: tidak) | patients, patient_addresses | isi form → simpan → kode PX01-otomatis |
| Detail + riwayat RM | `GET /patients/{id}` `show` | `patient/master/detail` | - | semua (baca) | patients + medical_records | buka → lihat timeline + link WA manual |
| Ubah | `GET /patients/{id}/edit` + `PUT` | `form` | - (LUBANG) | seeder M/A/N | sama | ubah → simpan |
| Hapus | `DELETE /patients/{id}` | — | B `delete patient` saja | seeder N+M (janggal: N bisa hapus!) | kaskade addresses | hapus → data hilang permanen (V2: soft) |
| Lookup JSON | `GET api/patients/lookup` | JSON | `auth` | semua | patients | autocomplete appointment/transaksi |

Field form v1 (`PatientRequest`): picture, name*, email, payment_email, sosmed,
phone* (`08…`), religion, gender, birthdate, alamat (7 kolom). **Tanpa**: nik,
ihs_id, birth_place, consent (D-01).

### 2.3 Appointment (reservasi)

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| List (tabel admin vs dokter) | `GET /appointments` `index` | `general/appointment/index` | B campur | semua baca | appointments | pantau jadwal |
| Buat (pilih pasien+jadwal dokter) | `GET/POST` `create/store` (`AppointmentRequest` + ruleslot: `AvailableDoctor/DoctorSchedule/TimeRangeUsed/DoctorDay`) | `general/appointment/form` | - | M/A/N | appointments | pesan → validasi bentrok → simpan |
| Detail | `GET /appointments/{id}` `show` | `detail` | B update/delete | semua | appointments | tinjau |
| Ubah | `GET + PUT` `edit/update` (`UpdateAppointmentRequest`) | `form` | - | M/A/N | appointments | reschedule manual |
| Batal (soft) | `DELETE` `destroy` → status CANCELED | — | - (LUBANG: bisa batalkan yg sudah bayar) | M/A/N | appointments.canceled_at | batal |
| Konfirmasi | `GET /appointments/{id}/confirm` | — | - (LUBANG: GET mengubah data) | M/A/N | confirmed_at | konfirmasi (V2: POST + gate) |
| Lookup pasien/dokter JSON | 3 API `get_patient/lookup/lookup_doctor` | JSON | `auth` | semua | patients/appointments | dukung form dinamis |

Status v1 implisit: BOOKED (baru) → CONFIRMED → (PAID saat nota) → RECORDED (RM jadi) / CANCELED.

### 2.4 Jadwal dokter

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| List + tambah + hapus + toggle aktif | `GET/POST/DELETE /schedules`, `POST update_status/{id}` (`ScheduleRequest`) | `general/schedule/index` (satu file, tabel per role) | B `@role admin/nurse` + can | M/A tulis, D/N baca | schedules | atur shift → dipakai validasi appointment |

### 2.5 Layanan & kategori (master tindakan jual)

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| CRUD layanan (harga min–maks, komisi) | `services.*` (`ServiceRequest`) | `general/service/{index,form,detail}` | - (update/delete di-comment!) | M (A/D/N via URL!) | services, categories | kelola tarif → dipakai appointment/RM/nota |
| CRUD kategori | `categories.*` (`CategoryRequest`) | `service/category/{index,form}` | - (semua can di-comment!) | M (terbuka!) | categories | kelola kategori |

### 2.6 Master user (admin/dokter/perawat)

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| List | `*/index` | `master/*/{index}` | **C** read-* | M (+A lihat link) | users + profesi + address | kelola staf |
| Tambah | `*/create/store` (`UserRequest`) | `master/*/form` | **C** create-* | M | users + profesi + address + role | rekrut |
| Ubah/hapus | `*/edit/update/destroy` | `form` | - (LUBANG) | M niatnya | sama | mutasi/nonaktif (V2: `is_active`, tanpa hapus fisik) |

### 2.7 Rekam medis (inti v1)

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| List | `GET /medical-records` | `patient/record/index` | B `create` + `@role admin/doctor` aksi | semua baca | medical_records + patients | pantau RM |
| Buat (dari appointment confirmed; dokter terfilter miliknya) | `GET/POST create/store` (`MedicalRecordRequest`: wajib anamnesis/diagnosis/therapy + **≥1 `diagnosis_codes`** + harga + foto) | `patient/record/form` + `livewire:diagnosis-search` | - | D tulis; A/N bisa via URL (LUBANG) | medical_records + medical_record_diagnoses (snapshot) + appointments.recorded_at | periksa → pilih kode (ketik bahasa awam) → foto → simpan |
| Detail (+kode resmi) | `GET /medical-records/{id}` | `patient/record/detail` | - | semua | + pivot | baca + cetak |
| Ubah | `GET + PUT edit/update` | `form` | - (LUBANG: A bisa edit padahal seeder read-only!) | D niatnya | + re-sync pivot | koreksi (V2: beraudit) |
| Hapus | `DELETE destroy` | — | - | D niatnya | + `recorded_at=null` | hapus (V2: arsip, bukan hapus) |
| History per pasien | `GET api/medical-records/{id}/lookup_history` | JSON | `auth` | semua | medical_records | timeline di detail pasien |

### 2.8 Transaksi / nota (kasir v1)

| Fitur | Route | View | Gate | Role | Tabel | Workflow |
|---|---|---|---|---|---|---|
| List nota | `GET /transactions` | `report/transaction/index` | B `@role nurse/admin` tombol | semua baca | transactions | pantau nota |
| Buat nota (dari appointment; DP/cicilan/voucher/asisten) | `GET/POST create/store` (`TransactionRequest`) | `report/transaction/form` | - | A/N kasir (terbuka!) | transactions + transaction_services/nurses + installments(+steps) | hitung → bayar/DP/cicil → nota `sequence` |
| Detail + reschedule kontrol + usul batal | `GET /transactions/{id}` + `PUT api/transactions/{id}/reschedule` | `report/transaction/detail` | B role; reschedule TANPA gate | semua | transactions.next_schedule | follow-up kontrol |
| Batal langsung | `POST /transactions/{id}/cancel` | — | **C** `approve cancellation` | M | canceled_at + reason | batal (terkunci bila ada usulan) |
| Usul→setuju/tolak | `propose-cancel`, `cancellations/{id}/approve|reject` | badge di detail | **C** request/approve | A usul, M putus | transaction_cancellation_requests + is_locked | ajukan → kunci → putus → buka |

### 2.9 Cicilan / omzet / gaji / export / profil

| Fitur | Route | Gate | Tabel | Catatan |
|---|---|---|---|---|
| Cicilan list + detail (read-only) | `installments.index/show` | - (cuma sidebar `@role M/A/N`) | installments + steps |-boot- buat via nota, bukan menu |
| Omzet + filter dokter/periode | `incomes.index` + `api/incomes/lookup` | scoping doctor di controller | transactions | export XLSX dari sini |
| Gaji dokter | `GET /salaries` (**tanpa nama route, tanpa sidebar**) | - | transactions + doctors | share 30–35%, rontgen, shift, target 55 |
| Export XLSX | `GET /export-transactions` | - | transactions | `MonthlyReports` |
| Profil + password + bahasa | `profile.*`, `switch-language` | `auth` | users + profesi | branching per role di controller |
| Diagnosis autocomplete | `GET api/diagnosis-codes?q&system&limit` | `auth` | diagnosis_codes | dipakai form RM |
| WA manual | `GET api/followup/whatsapp/{phone}/{message}` | `auth` | — | redirect `wa.me`, BUKAN automation |

## 3. Skema database v1 AKTUAL (33 tabel, ringkas per kolom)

> Konvensi: PK `uuid` kecuali disebut lain. `timestamps` = created/updated.
> Detail penuh hasil audit tersimpan di riwayat kerja; di bawah ini potret operasional.

### 3.1 Auth & sistem

* **users**(id PK, name*, email* unique, email_verified_at?, password*, is_active=T,
  remember, timestamps) · **password_reset_tokens**(email PK, token, created_at?) ·
  **sessions**(id PK, user_id? tanpa FK, ip?, agent?, payload*, last_activity) ·
  **cache/cache_locks/jobs/job_batches/failed_jobs** (bawaan Laravel)
* **permissions**(id, name*, guard, unique[name,guard]) · **roles**(id, name*, guard,
  unique) · **model_has_roles / model_has_permissions / role_has_permissions**
  (komposit; ⚠️ morph `model_id` bigint vs `users.id` uuid — D-06)

### 3.2 Pasien (SATUSEHAT-ready, berlubang di form)

* **patients**(id PK, name*, code (tanpa unique DB!), email?, payment_email?, phone?,
  **birthdate string?** (V2→date), birth_place?, **nik(16)? unique, ihs_id? unique,
  satusehat_consent=F**, religion enum? default OTHER,
  gender enum[MALE,FEMALE]=MALE, picture?, sosmed longtext?, timestamps)
* **patient_addresses**(patient_id PK+FK→patients cascade, 7 kolom alamat?)

### 3.3 Staf & layanan

* **admins**(user_id PK+FK→users cascade, nipp* (tanpa unique!), niptk?, foto?/cover?)
* **doctors**(+target=55, bank_*?, **ihs_id? unique** (tak ada form!), foto?)
* **nurses**(user_id PK+FK, nipp*, niptk?, foto?) · **user_addresses**(user_id PK+FK, 7 alamat?)
* **categories**(id PK, code* unique, name*, timestamps)
* **services**(id PK, category_id FK→categories restrict, code* unique, name*,
  type=REG, is_active=T, description?, lower_price* float, upper_price* float,
  **doctor_commision string?** (typo warisan, V2→decimal))
* **schedules**(id PK, doctor_id FK→doctors.user_id cascade, day enum MON–SUN*,
  availability=AVAILABLE, time_start*/end* time)

### 3.4 Klinis

* **appointments**(id PK, patient_id FK cascade + snapshot code/name/phone,
  doctor_id FK cascade + snapshot name/nipp/niptk, date* date, services* longtext(JSON),
  time_start*/end*, confirmed/paid/recorded/canceled_at?)
* **medical_records**(id PK, patient_* snapshot (address*), doctor_* snapshot,
  appointment_id? + appointment_date* + time, services* JSON, **anamnesis*/diagnosis*/
  therapy* longtext**, prescription?/checkup_result?, next_schedule string?,
  price*/discount*/billing* float, promat* enum, blood_pressure?, cooperativity* enum,
  image_before?/after? JSON path; ⚠️ TANPA FK — D-05)
* **diagnosis_codes**(id PK, system(16)* [ICD10|ICD9|SNOMED], code(32)*,
  display_id*, display_en?, keywords? JSON, category?, source?, version?,
  is_active=T, unique[system,code], index[system,is_active]; seed: 6 baris v1)
* **medical_record_diagnoses**(id PK, medical_record_id FK cascade,
  diagnosis_code_id FK restrict, system/code/display snapshot, unique[record,code])

### 3.5 Keuangan

* **transactions**(id PK, sequence* unique (nota YYNNNNN via service),
  down_payment_transaction_id? self-FK, has_down_payment=F, is_endorsed=F,
  has_installment? (3-state), current_payment=0, patient_id FK cascade + snapshot,
  doctor_id FK cascade + snapshot, nurse_id? + snapshot (tanpa FK),
  appointment_id FK cascade, appointment_datetime* **string**,
  next_schedule? date, services* longtext, price*/discount*/billing* float,
  payment_method?, canceled_at? + cancel_reason?, voucher_code?,
  referenced_installment_id? FK→installments, is_locked=F (kunci usul batal))
* **transaction_services**(id PK, service_id FK cascade + name, transaction_id FK cascade,
  **price string!**) · **transaction_nurses**(id PK, nurse FK cascade + name, trx FK)
* **installments**(transaction_id PK+FK cascade, patient_id tanpa FK!, amount*,
  status* bebas) · **installment_steps**(id PK, installment_id FK cascade,
  transaction_id? FK, type*, step*, due_date*, paid_at?, amount*, status*)
* **transaction_cancellation_requests**(id PK, transaction_id FK cascade,
  ⚠️ proposed_by/decided_by bigint vs users uuid (D-06), reason*,
  status=PROPOSED [PROPOSED|APPROVED|REJECTED], decided_at?/note?)
* **variables**(id, name* unique, value*)

## 4. Desain v2 (tabel BARU, dibangun bertahap — bukan sekaligus)

```text
[Fase 2] branches(id, org, name, address, phone) — single dulu, semua tabel klinis
  +branch_id nullable; visits(id, patient/appointment/doctor/branch, visit_date,
  clinical_status, billing_status, signed_at/by); anamnesis(visit FK 1-1:
  chief_complaint, present_illness, med/dental_history, allergy, medication);
  examinations(visit FK: subjective/objective/assessment/plan + section);
  odontogram_findings(visit FK, fdi[11-48,51-85], surface[mesial/distal/oklusal/
  bukal/lingual/palatal], condition SNOMED, material, notes; index[visit,fdi]);
  diagnoses(visit FK, tooth_fdi?, icd10*, name*, PRIMARY/SECONDARY);
  treatments(visit FK, tooth_fdi?, icd9cm*, procedure*, qty, price);
  treatment_plans(+items: tooth, treatment, est_price, priority,
  PLANNED/SCHEDULED/IN_PROGRESS/COMPLETED/CANCELLED);
  prescriptions(+items: medicine_kfa*, dosage, frequency, duration, qty, route,
  instruction); attachments(id, patient/visit, type[INTRAORAL/EXTRAORAL/XRAY/
  DOCUMENT/OTHER], path private, description, uploaded_by/at)
[Fase 3] invoices(+items dari treatments: TREATMENT/MEDICINE/OTHER, qty, unit_price;
  DRAFT/ISSUED/PARTIALLY_PAID/PAID/VOID); payments(invoice FK, amount, method
  CASH/TRANSFER/QRIS/DEBIT/CREDIT, date, received_by); receipts(payment FK, number);
  doctor_fee_rules/fees; inventory_items(+batches: batch/expiry/qty/buy_price) +
  stock_movements(IN/OUT/ADJUST/EXPIRED/DAMAGED); expenses(branch, category, amount)
[Fase 4] satusehat_sync_logs(patient/visit, resource_type, external_id,
  PENDING/SUCCESS/FAILED/RETRY, request/response JSON); whatsapp_templates/messages
  (QUEUED/SENT/DELIVERED/READ/FAILED); audit_logs(user, action, entity, entity_id,
  old/new JSON, reason, ip) [WAJIB]; notifications
```

Relasi target: `PATIENT─┬─APPOINTMENT→QUEUE └─VISIT─┬─ANAMNESIS/EXAM/ODONTOGRAM
├─DIAGNOSIS→TREATMENT→PLAN ├─PRESCRIPTION→INVENTORY └─ATTACHMENT` lalu
`VISIT→INVOICE→PAYMENT→RECEIPT`.

## 5. Alur bisnis kunci (v2 final, v1 mengikuti yang ada)

```text
Registrasi (FO, NIK wajib) → Appointment (validasi slot dokter) → Konfirmasi
→ H-1 reminder in-app → Datang → Check-in → Queue (WAITING→CALLED→IN_TREATMENT)
→ Anamnesis (N boleh draft) → Examination SOAP (D) → Odontogram per gigi (D)
→ Diagnosis ICD-10 ≥1 (D) → Tindakan ICD-9 (D) → Resep KFA (D, kurangi stok)
→ Treatment Plan (D) → SIGNED (D, e-sign) → Invoice otomatis (kasir) →
  Payment penuh/cicilan (kasir) → Receipt → Kontrol next_schedule →
  sinkron SATUSEHAT (async, retry) → audit tiap langkah
```

## 6. Daftar tech debt → fase (kode D-xx, sama dengan PRD §2.4)

| Kode | Masalah | Fase |
|---|---|---|
| D-01 | nik/ihs/consent/birth_place tak tersimpan; birthdate string | 1 |
| D-02 | doctors.ihs_id tanpa form/validasi | 1 |
| D-03 | ICD-9/10 campur satu field; tanpa wajib ICD-10 | 1 |
| D-04 | nol authorize di 7 controller + edit/destroy master + reschedule/confirm/export/installments/salaries | 1 |
| D-05 | medical_records tanpa FK; relasi Patient terbalik; fillable kadaluarsa | 1 |
| D-06 | proposed/decided_by bigint≠uuid; CategoryRequest unique; UpdateTransactionRequest mati; bug seed `1, 600000`; 4 seeder orphan; price string; salaries tanpa route; controller tanpa route + JSON hilang | 1 |

## 7. Navigasi & halaman kunci

Tetap compact: `Dashboard | Patients | Appointments | Clinical (satu alur visit:
anamnesis→exam→odontogram→diagnosis→treatment→resep→plan→attachments) | Billing |
Inventory | Reports ‖ Administration (Users/Doctors/Branches/Settings) |
Integrations (WA/SATUSEHAT)`. Jangan pecah clinical jadi 6 menu.
Halaman kunci: **Patient Detail** (header RM + 8 tab + timeline),
**Doctor Workspace** (antrian hari ini → current patient → Complete Visit),
**Cashier** (nota hari ini → bayar → print), **Owner Dashboard** (KPI + grafik).

## 8. Keamanan & non-fungsional (ringkas, penuh di PRD §5)

RBAC server-side + audit who/what/when/old/new/why; RME SIGNED hanya koreksi
beraudit; tanpa hard-delete RM; file private + signed URL; backup beda lokasi;
retensi ≥25 thn; portabel SQLite/MySQL/PgSQL; halaman pasien/visit < 2 dtk.
