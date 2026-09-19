# Todo Fase 2 — RME Gigi (v2.0-beta)

- [ ] Task 1 (`feature/visit-foundation`): branches + visits + permission visit + test. Dep: none. Verifikasi: migrate fresh + php artisan test hijau.
- [ ] Task 2 (`feature/queue-checkin`): check-in + halaman antrian + aksi status + sidebar. Dep: 1. Verifikasi: alur appointment→WAITING→CALLED→IN_TREATMENT hijau via test.
- [ ] Checkpoint Fondasi: migrate fresh + seed hijau; antrian e2e OK.
- [ ] Task 3 (`feature/soap`): anamnesis + examination SOAP + workspace shell. Dep: 1. Verifikasi: form simpan/tampil per visit.
- [ ] Task 4 (`feature/odontogram`): findings + chart FDI interaktif. Dep: 1. Verifikasi: temuan per gigi tersimpan & tampil.
- [ ] Task 5 (`feature/dx-tx`): diagnoses ICD-10 + treatments ICD-9 per visit. Dep: 1 (reuse master diagnosis_codes). Verifikasi: wajib ≥1 ICD-10 per visit (aturan mirip D-03).
- [ ] Checkpoint Isi klinis: satu visit lengkap bisa dibuat & dibaca per role.
- [ ] Task 6 (`feature/treatment-plan`): plans + items + status. Dep: 5. Verifikasi: CRUD + status flow.
- [ ] Task 7 (`feature/prescription`): prescriptions + items. Dep: 1. Verifikasi: CRUD per visit.
- [ ] Task 8 (`feature/attachments`): private disk + signed URL + daftar. Dep: 1. Verifikasi: tanpa signed URL = 403/404;CRUD.
- [ ] Checkpoint Penunjang: resep/plan/lampiran menempel visit; file privat.
- [ ] Task 9 (`feature/workspace-calendar`): workspace dokter + calendar + prefill nota. Dep: 2,3,4,5. Verifikasi: smoke halaman + prefill mengisi form nota.
- [ ] Task 10 (`feature/visit-sign`): sign + kunci + e2e penuh. Dep: 2–9. Verifikasi: SIGNED tak bisa diubah; rantai penuh hijau.
- [ ] Checkpoint Lengkap: siap review manusia; merge ke develop/v2 per task sudah dilakukan.
