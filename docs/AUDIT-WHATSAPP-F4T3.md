# 📋 Laporan Audit — Fitur WhatsApp Official (Fase 4 T3)

> Tanggal audit: 21 September 2026
> Branch: `develop/v2`
> Cakupan: seluruh file fitur WhatsApp (controller, service, model, migrasi, seeder, command, view, test, config, routes)

---

## Ringkasan Singkat

Fitur WhatsApp sudah **±85% jadi dan berfungsi** untuk alur dasarnya, tapi audit menemukan **3 bug nyata** (2 di antaranya bikin fitur utama tidak jalan sebagaimana mestinya) dan beberapa kelemahan kecil.

Semua bug **sudah diperbaiki** dan ditambah test regresinya — **8 test WhatsApp lulus semua**, total 115 test aplikasi tetap hijau.

---

## ✅ Yang Sudah Jadi & Sesuai Rencana

| Bagian | Status | Catatan |
|---|---|---|
| Tabel database (`whatsapp_templates`, `whatsapp_messages`) | ✅ Jadi | UUID, FK ke pasien & template, index status+tanggal |
| Outbox pesan (record setiap kirim) | ✅ Jadi | Status QUEUED → SENT/FAILED tercatat rapi |
| Normalisasi nomor (08… → 628…) | ✅ Jadi | Termasuk hapus spasi/strip/tanda + |
| Driver `log` untuk dev (tanpa API asli) | ✅ Jadi | Aman untuk development & test |
| Driver `cloud` (WhatsApp Cloud API Meta) | ✅ Jadi | Panggilan HTTP-nya benar; tinggal isi token di `.env` |
| Halaman admin (kirim manual + riwayat + filter status) | ✅ Jadi | Ada pagination, badge status, daftar template |
| Hak akses (`manage whatsapp`) | ✅ Jadi | Manajemen + Admin bisa; dokter/perawat ditolak (teruji) |
| 2 template seeder (reminder H-1, kontrol lanjutan) | ✅ Jadi | Idempoten, variabel `{nama}`, `{tanggal}`, dll. terganti |
| Command `wa:remind-h1` (reminder H-1) | ✅ Jadi* | Logika filter & idempotensi benar (*tapi lihat Bug #2) |
| Test otomatis | ✅ Jadi | 5 test lama + 3 baru dari hasil audit ini |
| Konfigurasi `.env.example` | ✅ Jadi | Semua variabel WA_* tersedia |

**Yang belum/sengaja belum:**
- Webhook DELIVERED/READ dari Meta (kolom statusnya sudah disiapkan di DB, tapi belum ada endpoint penerimaannya)
- Queue/worker (pesan dikirim sinkron, request web menunggu)
- UI kelola template (hanya lewat seeder)

Ini wajar untuk fase sandbox — bukan bug.

---

## 🐞 Bug yang Ditemukan & Sudah Diperbaiki

### Bug #1 — Pesan error tidak pernah muncul di layar (parahnya: menyesatkan)

**File:** `app/Http/Controllers/Integration/WhatsappController.php`

Kalau pengiriman manual gagal, argumen `withErrors()` **tertulis terbalik**:

```php
// SEBELUM (salah) — key = pesan exception, isi = 'error'
return back()->withErrors('error', $th->getMessage())->withInput();
```

Akibatnya pesan error yang sebenarnya (misal "Nomor WhatsApp tidak valid") **tidak pernah tampil** — user cuma lihat tulisan "Pesan gagal, cek log" yang malah tidak benar karena pesan error-nya memang tidak di-flash ke session.

**Fix:**

```php
// SESUDAH (benar)
report($th);
return back()->withErrors(['phone' => $th->getMessage()])->withInput();
```

Pesan sukses/gagal juga dibuat jujur: yang gagal sekarang menampilkan alasan dari outbox, bukan "cek log" yang menyesatkan.

---

### Bug #2 — Reminder H-1 otomatis TIDAK PERNAH jalan ⚠️ (bug paling penting)

**File:** `routes/console.php`

Command `wa:remind-h1` sudah dibuat dan bisa dijalankan manual, tapi **tidak pernah didaftarkan ke scheduler** — `routes/console.php` masih kosong. Artinya fitur andalan "pengingat otomatis H-1 ke pasien" tidak akan pernah berjalan sendiri meskipun cron server sudah diset.

Test lama lulus karena test memanggil command-nya secara manual, jadi masalah ini luput.

**Fix:**

```php
Schedule::command('wa:remind-h1')->dailyAt('07:00')->onOneServer()->runInBackground();
```

**Catatan deployment:** pastikan cron `php artisan schedule:run` sudah aktif di server produksi (jalan tiap menit), kalau belum ini juga harus disetel.

---

### Bug #3 — Laporan command menghitung bohong & nomor invalid bisa spam

**File:** `app/Services/Whatsapp/WhatsappService.php`, `app/Console/Commands/WhatsappRemindH1.php`

Dua masalah nyambung:

1. Service `send()` **menelan exception** saat pengiriman gagal (status di-set FAILED lalu return), sehingga command menghitung pesan gagal sebagai **"terkirim"** — output "5 terkirim, 0 gagal" bisa berarti 5 pesan gagal semua.
2. Nomor pasien yang invalid/omong kosong **tidak meninggalkan jejak** di outbox (ditolak sebelum dicatat), jadi dedup harian command tidak pernah "melihat"nya → kalau command dipicu berkali-kali sehari, pasien dengan nomor bermasalah terus dicoba lagi.

**Fix:**
- Service sekarang **melempar ulang exception** setelah mencatat status FAILED — pemanggil (controller/command) jadi tahu pengiriman gagal.
- **Setiap percobaan — termasuk nomor invalid — tercatat di outbox** (dengan alasan error-nya), sehingga dedup harian bekerja untuk semua kasus dan staff bisa lihat penyebab kegagalan.

---

### Kelemahan kecil yang ikut dibereskan

- **Kill switch `WA_ENABLED` tidak pernah dicek** — config-nya ada, tapi kodenya mangabaikan; matikan pun aplikasi tetap "mengirim". Sekarang dicek di awal `send()`.
- `external_id` bisa ter-set `null` kalau Meta balas tanpa ID — sekarang di-guard.

---

## 🧪 Bukti Perbaikan

3 test regresi baru di `tests/Feature/WhatsappTest.php`:

1. `test_manual_send_failure_shows_error_to_user` — error tampil ke user via session, percobaan gagal tercatat di outbox
2. `test_remind_h1_counts_invalid_phone_as_failed_and_stays_idempotent` — gagal terhitung jujur ("0 terkirim, 1 gagal") + jalan kedua tidak menggandakan
3. `test_remind_h1_is_scheduled` — scheduler terdaftar di `schedule:list`

Hasil akhir:

```
Tests\Feature\WhatsappTest  →  8/8 PASS
Seluruh suite aplikasi      →  115 test / 580 assertion PASS
```

---

## 📁 File yang Diubah

| File | Perubahan |
|---|---|
| `app/Http/Controllers/Integration/WhatsappController.php` | Fix `withErrors` terbalik + flash sukses/gagal yang jujur + `report($th)` |
| `app/Services/Whatsapp/WhatsappService.php` | Guard `WA_ENABLED`, invalid phone tercatat FAILED, lempar ulang exception, guard `external_id` |
| `app/Console/Commands/WhatsappRemindH1.php` | Komentar klarifikasi counting gagal (logika hitung jadi akurat lewat fix service) |
| `routes/console.php` | Daftarkan scheduler `wa:remind-h1` harian jam 07:00 |
| `tests/Feature/WhatsappTest.php` | +3 test regresi |

---

## 💡 Rekomendasi Selanjutnya (belum urgent)

1. **Set cron di produksi** untuk `php artisan schedule:run` tiap menit — tanpa ini scheduler yang barusan diperbaiki tetap tidak jalan.
2. **Webhook status Meta** (DELIVERED/READ) — skema DB sudah siap, tinggal endpoint + verifikasi signature.
3. **Kirim pesan via queue** agar request web tidak menunggu HTTP call ke Meta (driver cloud, timeout 30 detik).
4. **UI kelola template** (aktif/nonaktif, edit isi) — sekarang hanya lewat seeder.
