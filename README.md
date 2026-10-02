# KataGigi Dignify

Rebuild modern dari `katagigi-banjarmasin` dengan Laravel, Livewire, Tailwind, dan PostgreSQL.

## Stack

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel + PHP 8.4 |
| UI | Livewire 3 + Volt + Tailwind + Vite |
| Auth/RBAC | Breeze + `spatie/laravel-permission` |
| Database Docker | PostgreSQL |
| Kualitas | Pint, PHPUnit |

## Demo Login

Password semua akun demo: `password`.

| Role | Email |
|---|---|
| Manajemen | `manajemen@gmail.com` |
| Admin | `admin@gmail.com` |
| Doctor | `doctor@gmail.com` |
| Nurse | `nurse@gmail.com` |

## Jalankan Dengan Docker

Prasyarat: Docker Desktop.

```powershell
Copy-Item .env.example .env
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Buka aplikasi:

```text
http://localhost:8101
```

Port yang dipakai:

| Service | Port host |
|---|---:|
| Nginx / Laravel | `8101` |
| PostgreSQL | `5434` |
| Vite dev server | `5173` |

Jalankan Vite jika sedang mengubah frontend:

```powershell
docker compose --profile frontend up -d node
```

Untuk production-style asset build:

```powershell
docker compose exec node npm run build
```

## Perintah Harian

```powershell
docker compose ps -a
docker compose logs -f app
docker compose logs -f nginx
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan test --compact
```

Reset database Docker dari awal:

```powershell
docker compose down -v
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

Jika login demo belum bisa karena database sudah pernah dibuat tanpa seeder:

```powershell
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder
```

Perintah full generate seeder
```powershell
docker compose exec app php artisan dn:seed
```

## Catatan Docker

Container `app` otomatis:

- membuat `.env` dari `.env.example` jika belum ada;
- menjalankan `composer install` jika `vendor` belum ada;
- membuat folder `storage` dan `bootstrap/cache`;
- memperbaiki owner/permission folder cache ke `www-data`;
- membersihkan compiled Blade view sebagai `www-data`;
- membuat `APP_KEY` jika masih kosong.

Ini mencegah error seperti `touch(): Utime failed: Operation not permitted` yang sebelumnya bisa memicu `504 Gateway Time-out` setelah login.

Dependency PHP (`vendor`) disimpan di Docker named volume agar Laravel tidak membaca ribuan file PHP dari bind mount Windows. Ini membuat request dashboard dan navigasi jauh lebih stabil di Docker Desktop.

## Jalankan Tanpa Docker

```powershell
composer install
php artisan key:generate
npm install
php artisan migrate --seed
npm run dev
php artisan serve
```

Pastikan konfigurasi database di `.env` sesuai environment lokal.
