# Blueprint RME Dental v1 (Baseline) → v2

> Dokumen ini adalah adaptasi blueprint usulan ke **realita kode saat ini**.
> Label: `[V1-ACTUAL]` = sudah ada & dibekukan. `[V2]` = akan dibangun.
> Referensi fitur DentalDiary dipakai sebagai acuan produk, bukan klaim detail internalnya.

## 1. Konsep data utama

```text
[V1-ACTUAL, diperluas di V2]
Organization (V2: single dulu, siapkan branch_id)
    ↓
Patient  [V1-ACTUAL: patients + patient_addresses]
    ↓
Appointment  [V1-ACTUAL: appointments + schedules]
    ↓
Visit / Encounter  [V2: tabel visits baru; V1 memakai medical_records per appointment]
    ↓
Clinical Record
    ├── Anamnesis        [V1: kolom teks → V2: tabel anamnesis]
    ├── Examination SOAP [V2]
    ├── Odontogram FDI   [V2: tabel odontogram_findings]
    ├── Diagnosis ICD-10 [V1: pivot medical_record_diagnoses → V2: tabel diagnoses per visit+gigi]
    ├── Treatment ICD-9  [V2: tabel treatments, mengalir ke billing]
    ├── Prescription     [V1: kolom teks → V2: prescriptions + items, obat KFA]
    ├── Attachment       [V1: image_before/after → V2: attachments + signed URL]
    └── Treatment Plan   [V2: treatment_plans + items]
            ↓
        Billing  [V1: transactions → V2: invoices + items]
            ↓
        Payment  [V1: tercampur + installments → V2: payments + receipts]
```

Prinsip: **Patient menyimpan identitas. Visit menyimpan kejadian medis.**
Status medis dan status billing SELALU kolom terpisah.

## 2. Patient management

```text
[V1-ACTUAL] patients(id, code, name, email, phone, birthdate*, birth_place,
  nik(16,unique), ihs_id(unique), religion, gender, picture, sosmed, satusehat_consent)
  + patient_addresses(patient_id, street, village, district, regency, province, zip_code)
[V2] perbaiki: birthdate string→date, validasi NIK/IHS di Request+Service
  (saat ini insert/update TIDAK menyimpan nik/ihs/consent — tech debt #1),
  tambah patient_allergies + patient_medical_histories, tab profil:
  Overview | Visit History | Odontogram | Treatment Plan | Prescriptions |
  Attachments | Billing | Timeline
```

## 3. Appointment + Queue

```text
[V1-ACTUAL] appointments(+patient/doctor snapshot, date, time_start/end,
  confirmed/paid/recorded/canceled_at, services JSON)
[V2] tambah: branch_id, type, status enum
  BOOKED→CONFIRMED→CHECKED_IN→IN_PROGRESS→COMPLETED→CANCELLED/NO_SHOW/RESCHEDULED
  + tabel queues(queue_number, status WAITING/CALLED/IN_TREATMENT/COMPLETED/SKIPPED)
  + calendar view (saat ini list saja) + check-in flow
```

## 4. Visit / Encounter (inti RME, BARU di V2)

```text
visits(id, patient_id, appointment_id, doctor_id, branch_id,
  visit_date, visit_type, clinical_status, billing_status, signed_at, signed_by)
clinical_status: SCHEDULED→CHECKED_IN→IN_PROGRESS→EXAMINATION→TREATMENT→COMPLETED
billing_status:  UNBILLED→DRAFT→ISSUED→PARTIALLY_PAID→PAID→VOID
```

## 5. Clinical record (per visit)

* **Anamnesis** `[V2]`: chief_complaint, present_illness, medical/dental_history,
  allergy, current_medication, recorded_by/at.
* **Examination** `[V2]`: subjective/objective/assessment/plan + section
  (general, extra/intra oral, penunjang, clinical notes).
* **Odontogram** `[V2, modul pembeda]`:
  `odontogram_findings(visit_id, fdi_number 11-48/51-85, surface, condition SNOMED,
  restoration_material, notes)` + UI SVG interaktif + history per gigi per visit.
  SATUSEHAT: `Observation (OC000061 + bodySite FDI + component surface/finding)`.
* **Diagnosis** `[V1→V2]`: saat ini satu field campur ICD-10/9/SNOMED (salah konsep).
  V2: `diagnoses(visit_id, tooth_fdi, icd10_code, name, type PRIMARY/SECONDARY)` —
  **ICD-10 wajib ≥1** (SATUSEHAT `Condition`). ICD-9 DILARANG di tabel ini.
* **Treatment** `[V2]`: `treatments(visit_id, tooth_fdi, icd9cm_code, procedure,
  qty, price)` → otomatis jadi `invoice_items`. SATUSEHAT `Procedure`.
* **Prescription** `[V2]`: `prescriptions + prescription_items(medicine_kfa,
  dosage, frequency, duration, qty, route, instruction)` → kurangi stok.
* **Treatment Plan** `[V2]`: `treatment_plans + items(tooth, treatment,
  est_price, priority, status PLANNED/SCHEDULED/IN_PROGRESS/COMPLETED/CANCELLED)`.
* **Attachments** `[V1→V2]`: `INTRAORAL/EXTRAORAL/XRAY/DOCUMENT/OTHER`,
  relasi ke visit, private storage + signed URL (saat ini public path).

## 6. Billing → Payment → Receipt

```text
[V1-ACTUAL] transactions + installments + income reports + export + cancel-approval
[V2] split tanpa hapus data lama:
  invoices(id, patient_id, visit_id, number, subtotal, discount, tax, total,
    status DRAFT/ISSUED/PARTIALLY_PAID/PAID/VOID)
  invoice_items(invoice_id, item_type TREATMENT/MEDICINE/OTHER, ref_id, qty, unit_price)
  payments(id, invoice_id, amount, method CASH/TRANSFER/QRIS/DEBIT/CREDIT, date, received_by)
  receipts(id, payment_id, number, ...)
  doctor_fee_rules + doctor_fees (persentase per tindakan → laporan jasa medis)
  expenses(id, branch_id, category, amount, ...) — operasional
```

## 7. Pendukung

* **Doctor schedule** `[V1-ACTUAL]`: schedules(day enum, time_start/end, availability) → V2 tambah branch_id.
* **Inventory** `[V2]`: `inventory_items + inventory_batches(batch, expiry, qty, buy_price) + stock_movements(IN/OUT/ADJUST/EXPIRED/DAMAGED)` + low-stock alert.
* **WhatsApp** `[V2-AKHIR, ditunda]`: trigger (created/H-1/reschedule/cancelled/follow-up/bill) + `whatsapp_templates + whatsapp_messages(status QUEUED/SENT/DELIVERED/READ/FAILED)`. Wajib Official Business API.
* **SATUSEHAT** `[V2, integration layer]`: `RME → mapping → payload → API`,
  `satusehat_sync_logs(sync_id, patient_id, visit_id, resource_type, external_id,
  status PENDING/SUCCESS/FAILED/RETRY, request, response)`. Urutan: Auth → Organization
  → Location → Practitioner → Patient(MPI) → Encounter → Condition/Procedure/Observation/dst.
  Ikut spesifikasi resmi saat implementasi.
* **Audit log** `[V2, wajib]`: `audit_logs(user_id, action, entity, entity_id,
  old_value, new_value, reason, ip, created_at)`. RME SIGNED hanya koreksi beraudit.

## 8. Navigasi (compact, per role berbeda isi)

```text
Dashboard | Patients | Appointments | Clinical (Patient Visit: anamnesis→exam→
odontogram→diagnosis→treatment→prescription→plan→attachments) | Billing |
Inventory | Reports || Administration (Users/Doctors/Branches/Settings) |
Integrations (WhatsApp/SATUSEHAT)
```

Halaman kunci: **Patient Detail** (header RM + tabs + timeline),
**Doctor Workspace** (antrian hari ini → current patient → Complete Visit),
**Cashier** (invoice hari ini → payment → print), **Owner Dashboard** (KPI + charts).

## 9. Relasi inti (target V2)

```text
PATIENT ─┬─ APPOINTMENT ─→ QUEUE
         └─ VISIT ─┬─ ANAMNESIS / EXAMINATION / ODONTOGRAM
                   ├─ DIAGNOSIS ─→ TREATMENT ─→ TREATMENT PLAN
                   ├─ PRESCRIPTION ─→ INVENTORY
                   └─ ATTACHMENT
VISIT ─→ INVOICE ─→ PAYMENT ─→ RECEIPT
```

## 10. Daftar tabel (target akhir, dibangun bertahap — BUKAN sekaligus)

```text
[Ada di V1] patients, patient_addresses, users(+roles/permissions), doctors,
  nurses, admins, categories, services, schedules, appointments, medical_records,
  medical_record_diagnoses, diagnosis_codes, transactions, installments, variables
[V2 baru] branches, visits, anamnesis, examinations, odontogram_findings,
  diagnoses, treatments, treatment_plans(+items), prescriptions(+items),
  attachments, invoices(+items), payments, receipts, doctor_fee_rules/fees,
  inventory_items(+batches), stock_movements, expenses, whatsapp_templates/messages,
  satusehat_sync_logs, audit_logs, notifications
```
