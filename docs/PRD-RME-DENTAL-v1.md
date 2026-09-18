# PRD — RME Dental Klinik Gigi (v1 Baseline → v2)

> Status: **v1 FROZEN** (baseline kode saat ini, commit `dc2b1f6` + dokumen ini).
> V2 dikembangkan di `develop/v2`. Rollback kapan saja ke tag `v1.0.0` / branch `release/v1`.
> Lihat juga: `BLUEPRINT-RME-DENTAL-v1.md`, `ROADMAP-v1-to-v2.md`, `BRANCHING.md`.

## 1. Ringkasan eksekutif

Membangun RME + Clinic Management khusus klinik gigi dengan acuan fitur DentalDiary
(RME, odontogram, ICD-10/ICD-9, appointment, billing, inventory, SATUSEHAT, WhatsApp —
sumber: materi publik P2MW Kemdiktisaintek & situs DentalDiary).

Prinsip: **jangan rewrite**. v1 dibekukan apa adanya sebagai jaring pengaman.
v2 dikerjakan inkremental menuju blueprint, dengan urutan
`Patient → Visit → Clinical → Odontogram → Billing → Inventory → WA → SATUSEHAT`.

Tanpa WhatsApp dulu (sesuai keputusan): reminder diganti calendar view + pengingat in-app.

## 2. Tujuan & keberhasilan

1. Setiap pasien punya **satu profil + timeline kunjungan** yang bisa ditelusuri.
2. Setiap kejadian medis terikat pada **visit/encounter**, bukan atribut pasien.
3. Aliran **Clinical → Financial**: tindakan dokter mengalir ke billing tanpa ketik ulang.
4. Setiap role fokus ke jobdesknya (matriks §6).
5. Siap audit **Permenkes 24/2022** (kontrol akses, audit trail, backup, retensi)
   dan siap bridging **SATUSEHAT Use Case Gigi** (Encounter, Condition ICD-10,
   Procedure ICD-9-CM, Observation odontogram/OHIS, Medication KFA).

## 3. Scope v1 (YANG SUDAH ADA — dibekukan)

Berdasarkan audit kode aktual (Laravel 13 + Livewire + Blade + Spatie Permission):

| Modul | Kondisi v1 aktual |
|---|---|
| Auth + RBAC | Login Breeze, 4 role (`manajemen, admin, doctor, nurse`), 48 permission. Enforcement lemah (hanya Blade, minim `authorize`) — dicatat sebagai tech debt, BUKAN dirombak di v1 |
| Pasien | CRUD `patients` + `patient_addresses`, kolom SATUSEHAT-ready (`nik, ihs_id, birth_place, satusehat_consent`), riwayat kunjungan |
| Appointment + Jadwal | CRUD `appointments`, `schedules`, status `confirmed/paid/recorded/canceled`. Belum calendar view, belum queue |
| RME | `medical_records` + teks `anamnesis/diagnosis/therapy`, foto before/after, master `diagnosis_codes (ICD10/ICD9/SNOMED)` + pivot `medical_record_diagnoses`. Belum odontogram, belum SOAP terstruktur, belum visit |
| Kasir | `transactions`, `installments` (cicilan), laporan income, export Excel, approval pembatalan nota. Model masih `transactions`, BELUM split `invoices/payments/receipts` |
| Dashboard | Per role (admin/manajemen vs doctor vs nurse) + pasien data belum lengkap (NIK/HP/tgl lahir/alamat) |
| Follow-up WA | Hanya redirect `wa.me` manual — BUKAN Official API, tidak diklaim otomatis |

## 4. Out of scope v1 → masuk v2 (bertahap)

`odontogram FDI`, `queues`, `treatment_plans`, `prescriptions` terstruktur,
`invoices/payments/receipts` split, `inventory`, `expenses`, `multi-branch`,
`WhatsApp Official API`, `SATUSEHAT bridging`, `audit_logs` formal, `e-sign`.

## 5. Kebutuhan fungsional v2 (ringkas, detail di Blueprint)

1. **Patient**: NIK 16 digit valid, IHS ID via MPI/KYC, consent, alamat terstruktur.
2. **Appointment**: status `BOOKED→CONFIRMED→CHECKED_IN→IN_PROGRESS→COMPLETED→CANCELLED/NO_SHOW/RESCHEDULED`, calendar view, check-in → queue.
3. **Visit/Encounter**: `visit` sebagai inti (pasien + dokter + appointment + tanggal + status medis TERPISAH dari status billing).
4. **Clinical**: anamnesis, examination (SOAP + intra/extra oral), odontogram per-gigi FDI + surface + SNOMED finding, diagnosis ICD-10 (primer/sekunder, wajib ≥1), tindakan ICD-9-CM (mengalir ke billing), resep (obat KFA + dosis), treatment plan (PLANNED→COMPLETED), lampiran (foto/X-ray, private storage + signed URL).
5. **Billing**: invoice (DRAFT→PAID/VOID) + items dari treatment, payment terpisah (mendukung cicilan + multiple method: CASH/TRANSFER/QRIS/DEBIT/KREDIT), receipt/kwitansi, jasa medis dokter (persentase per tindakan).
6. **Operasional**: jadwal dokter, inventory (item+batch+expiry, movement IN/OUT/ADJUST, low-stock alert), expense, laporan (klinik/dokter/keuangan/inventory).
7. **Integrasi**: SATUSEHAT sebagai integration layer + sync log (PENDING/SUCCESS/FAILED/RETRY). Implementasi ikut spesifikasi resmi saat coding, bukan asumsi. WhatsApp Business API + message log (ditunda sampai v2 akhir).

## 6. Role & jobdesk (final, dipetakan ke role AKTUAL)

> Blueprint asli mengusulkan Owner/Cashier/Super Admin — **disesuaikan**: tidak ada
> role baru di v2 awal. `manajemen` = Owner, `admin` = Front Office, kasir dirangkap
> `admin`+`nurse` sesuai praktik saat ini, `Super Admin` hanya untuk SaaS kelak.

| Modul | manajemen (Owner) | admin (FO) | doctor | nurse (Asisten) |
|---|---|---|---|---|
| Dashboard | penuh + keuangan | operasional/antrian | workspace klinis | antrian/bantuan |
| Pasien | CRUD + hapus | CREATE/VIEW/EDIT (tanpa hapus) | VIEW | VIEW (+draft anamnesis) |
| Appointment/Queue | CRUD | CRUD + check-in | VIEW | VIEW + kelola antrian |
| RME (anamnesis s/d plan) | VIEW | VIEW terbatas | **CRUD miliknya + sign** | VIEW + upload lampiran |
| Billing/Payment | CRUD + approve | VIEW + usul batal | VIEW terbatas | VIEW (kasir: bila ditugaskan) |
| Inventory/Expense | CRUD | CRUD inventory | VIEW | VIEW |
| Reports/Users/Settings | CRUD | operasional/terbatas | terbatas/none | none |
| SATUSEHAT/WA | MANAGE | operasi terbatas | - | - |

Aturan keras: **RME final (SIGNED) hanya bisa dikoreksi dengan jejak audit** (old→new + alasan),
tidak ada hard-delete rekam medis, file medis tidak di public storage.

## 7. Kebutuhan non-fungsional

* **Regulasi**: Permenkes 24/2022 (RME wajib, interoperabilitas SATUSEHAT, registrasi sistem,
  backup, retensi ≥25 thn), UU PDP (persetujuan, minimisasi, keamanan).
* **Keamanan**: RBAC enforcement server-side (`middleware can:` + `authorize()`),
  audit log (who/what/when/old/new/why), signed URL file medis, HTTPS, rate limiting.
* **Kinerja**: halaman pasien & visit < 2 dtk, list paginasi 20.
* **Skalabilitas**: monolit modular Laravel tetap; `branch_id` disiapkan sejak v2
  walau single-branch dulu. TIDAK perlu microservices/K8s/GraphQL di tahap ini.
* **Stack v2**: TETAP Laravel + Livewire + Blade + MySQL/PostgreSQL (menolak usulan
  rewrite ke Next.js/Prisma di blueprint asli — biaya rewrite > manfaat).

## 8. Kriteria penerimaan v2 (definisi selesai)

1. Alur `registrasi → appointment → check-in → queue → visit → odontogram →
   diagnosis ICD-10 → tindakan ICD-9 → resep → billing → payment → receipt → kontrol`
   bisa dijalankan end-to-end per role tanpa URL hacking.
2. `php artisan test` hijau; permission diuji (nurse tidak bisa hapus pasien,
   admin tidak bisa edit RME, doctor tidak bisa lihat omzet global).
3. Payload SATUSEHAT Gigi (sandbox) lolos validasi untuk Patient/Encounter/Condition.
4. Audit log mencatat setiap CREATE/EDIT klinis + finansial.

## 9. Risiko & keputusan tercatat

* Klaim "Terintegrasi SATUSEHAT" DILARANG sebelum bridging production lolos.
* Link `wa.me` saat ini bukan Official API — jangan dipasarkan sebagai automation.
* `storage/framework/views/*.php` saat ini untracked & membanjiri `git status`
  (pre-existing, perbaiki `.gitignore` di v2 — tidak menyentuh v1).
