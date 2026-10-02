# Implementation Plan: Fase 2 — RME Gigi (v2.0-beta)

## Overview
Membangun alur klinis gigi berbasis **visit** di samping jalur v1 (`medical_records`, tidak diutak-atik):
`registrasi → appointment → check-in → queue → visit (anamnesis → SOAP → odontogram →
diagnosis ICD-10 → tindakan ICD-9 → resep → treatment plan → attachments) → sign →
nota (prefill dari visit) → kontrol`. Hasil: v2.0-beta layak uji internal (PRD §3.2).

## Architecture Decisions
- **Koeksistensi**: jalur v1 tetap jalan; visit = jalur baru. Tanpa migrasi data v1 di Fase 2.
- **Antrian = visits berstatus** (`clinical_status`), bukan tabel antrean terpisah (sesuai Blueprint §4).
- ** ICD**: diagnosis visit pakai master `diagnosis_codes` (ICD-10); tindakan pakai yang sama (ICD-9-CM). Tanpa master obat dulu — resep pakai nama + kode KFA bebas (master obat = Fase 3 + inventory).
- **File**: disk `local` (private) + signed URL; tanpa hard-delete file rekam medis.
- **Konvensi**: PK uuid, migrasi reversible, permission baru di seeder + test negatif, Blade + Alpine mengikuti pola existing (tanpa dependensi baru).
- **Status visit**: `REGISTERED → WAITING → CALLED → IN_TREATMENT → DONE → SIGNED` (`clinical_status`);
  `billing_status`: `UNBILLED → BILLED` terpisah (split invoice = Fase 3).
- **Sign = kunci**: visit SIGNED tak bisa diubah (koreksi beraudit = Fase 3 + audit_logs).

## Task List (satu task = satu branch fitur)

### Gelombang 1: Fondasi + antrian
- [ ] Task 1 (`feature/visit-foundation`): tabel `branches` (+seed cabang default) & `visits` (+model, relasi, nomor visit); permission `read/create/update/sign visit` + seeder peran; test model. [M]
- [ ] Task 2 (`feature/queue-checkin`): check-in appointment → visit WAITING; halaman Antrian hari ini; aksi panggil/mulai/selesai; menu sidebar; test alur. [M]

### Checkpoint: Fondasi
- [ ] migrate fresh + seed hijau; antrian end-to-end manual OK

### Gelombang 2: Isi klinis visit
- [ ] Task 3 (`feature/soap`): tabel + form Anamnesis (1-1) & Examination SOAP per visit; workspace shell (header visit + tab). [M]
- [ ] Task 4 (`feature/odontogram`): `odontogram_findings` (FDI 11–48/51–85 + surface + kondisi SNOMED); chart gigi interaktif; simpan/tampil temuan. [L]
- [ ] Task 5 (`feature/dx-tx`): `diagnoses` (ICD-10 per visit/gigi) + `treatments` (ICD-9-CM + qty/harga); UI CRUD per visit. [M]

### Checkpoint: Isi klinis
- [ ] satu visit lengkap (SOAP + odontogram + dx/tx) bisa dibuat & dibaca per role

### Gelombang 3: Penunjang + workspace
- [ ] Task 6 (`feature/treatment-plan`): `treatment_plans` + items (usulan dari diagnosis/tindakan) + status. [S/M]
- [ ] Task 7 (`feature/prescription`): `prescriptions` + items (nama obat + kode KFA + dosis/aturan). [S/M]
- [ ] Task 8 (`feature/attachments`): upload ke disk private + signed URL + daftar/hapus (tanda hapus, bukan hard-delete file aktif). [S/M]

### Checkpoint: Penunjang
- [ ] resep, plan, lampiran menempel pada visit; file tak bisa diakses tanpa signed URL

### Gelombang 4: Workspace + kunci
- [ ] Task 9 (`feature/workspace-calendar`): Doctor Workspace (antrian hari ini → pasien saat ini → ringkasan visit) + calendar appointment + tombol "Buat nota dari visit" (prefill transactions.create). [L]
- [ ] Task 10 (`feature/visit-sign`): sign visit (SIGNED + kunci edit) + test e2e rantai penuh Fase 2. [M]

### Checkpoint: Lengkap (v2.0-beta)
- [ ] `registrasi → … → sign → nota → kontrol` hijau per role; siap review manusia

## Risks and Mitigations
| Risk | Impact | Mitigation |
|---|---|---|
| Odontogram UI melebar (32+20 gigi + surface) | Med | Batasi: chart statis + klik kondisi per gigi; surface opsional dropdown; tanpa drag/zoom |
| Jalur v1 vs visit membingungkan user | Med | Sidebar terpisah ("Antrian", "Workspace"); v1 tidak diubah;Flow baru hanya dari check-in |
| Prefill nota merusak alur kasir | Low | Prefill hanya via query param; tanpa param = perilaku v1 |
| Status visit vs status appointment ganda | Low | Appointment selesai = check-in; sumber kebenaran klinis = visit |

## Open Questions
- Perlu batasi dokter hanya visit miliknya di Fase 2, atau cukup di workspace (filter) seperti v1? → Default: filter workspace + authorize umum (kepemilikan ketat = Fase 3 + audit).
- Obat: cukup nama + KFA bebas, atau perlu master obat kecil sekarang? → Default: bebas (master = Fase 3).

Tasks tracked in: `tasks/todo.md` (ini) + `docs/ROADMAP-v1-to-v2.md` (Fase 2).
