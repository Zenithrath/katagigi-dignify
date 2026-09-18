# Strategi Branching & Rilis

## 1. Peta branch

```text
main            = PRODUCTION (stabil, bisa deploy). Hanya merge dari develop/v2.
release/v1      = Beku di v1.0.0. Jaring pengaman / rollback. JANGAN dikembangkan.
develop/v2      = Integrasi kerja v2. Semua fitur digabung di sini dulu.
feature/*       = Kerja harian, branch dari develop/v2. Contoh: feature/odontogram.
hotfix/*        = Perbaikan darurat production, branch dari main, merge balik ke
                  main + develop/v2 + (bila perlu) release/v1.
```

## 2. Membuat struktur ini (sudah dilakukan saat baseline)

```powershell
git checkout main
git add docs/PRD-RME-DENTAL-v1.md docs/BLUEPRINT-RME-DENTAL-v1.md docs/ROADMAP-v1-to-v2.md docs/BRANCHING.md
git commit -m "docs: tambah PRD + blueprint + roadmap + branching RME v1 baseline"
git tag -a v1.0.0 -m "v1 baseline stabil sebelum develop v2"
git branch release/v1 v1.0.0
git branch develop/v2 main
git push origin main v1.0.0 release/v1 develop/v2   # push saat siap
```

## 3. Alur kerja harian v2

```powershell
git checkout develop/v2; git pull
git checkout -b feature/nama-fitur develop/v2
# ... coding + test (php artisan test) ...
git push -u origin feature/nama-fitur
# → buka Pull Request: feature/nama-fitur → develop/v2 → review → merge
# Rilis ke production:
git checkout main; git merge --no-ff develop/v2; git tag -a v2.0.0 -m "rilis v2"
```

## 4. Rollback darurat

```powershell
# Opsi A — pindah ke v1 beku (aman, tanpa hapus history):
git checkout release/v1
# deploy dari branch ini sementara; v2 tetap utuh di develop/v2 untuk diperbaiki.

# Opsi B — kembalikan production ke v1:
git checkout main
git reset --hard v1.0.0
git push --force-with-lease origin main   # hanya darurat, koordinasikan dulu
```

## 5. Aturan proteksi (atur di GitHub → Settings → Branches)

* `main`: require PR + 1 approval + `php artisan test` hijau, no direct push.
* `release/v1`: lock / no push kecuali hotfix kritis.
* `develop/v2`: require PR untuk `feature/*`, boleh squash merge.

Remote: `origin https://github.com/Zenithrath/katagigi-dignify.git`.
