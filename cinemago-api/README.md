# CinemaGo API

REST API (PHP + MySQL) untuk aplikasi booking film **CinemaGo** yang dibuat dengan Flutter.

## Teknologi

- PHP 7.4+ (native, tanpa framework)
- MySQL / MariaDB (dikelola lewat phpMyAdmin)
- Format data: JSON

## Struktur Folder

```
cinemago-api/
├── api/
│   ├── config.php                  # koneksi DB + helper response JSON
│   ├── config.local.example.php    # contoh kredensial (salin ke config.local.php)
│   ├── register.php                # POST  - daftar akun
│   └── login.php                   # POST  - login
├── database/
│   ├── migrate.php                 # runner migrasi
│   └── migrations/
│       └── 001_create_users_table.sql
├── .gitignore
└── README.md
```

## Cara Instalasi (XAMPP)

1. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Clone repo ini ke folder `htdocs`:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/USERNAME/cinemago-api.git
   ```
3. *(Opsional)* Jika MySQL Anda memakai password / nama database berbeda:
   salin `api/config.local.example.php` menjadi `api/config.local.php`, lalu edit isinya.
4. Jalankan migrasi database:
   ```bash
   cd C:\xampp\htdocs\cinemago-api
   C:\xampp\php\php.exe database\migrate.php
   ```
   Database `cinemago` dan tabel-tabelnya akan dibuat otomatis.

   > **Alternatif lewat phpMyAdmin:** buat database `cinemago`, pilih database itu,
   > buka tab **Import**, lalu import file di `database/migrations/` satu per satu sesuai urutan nomor.

5. Cek API: buka `http://localhost/cinemago-api/api/login.php`.
   Jika muncul `{"success":false,"message":"Method harus POST"}`, API sudah berjalan.

> Base URL di Flutter (`api_service.dart`) menyesuaikan lokasi folder.
> Contoh: `http://localhost/cinemago-api/api` (Web) atau `http://10.0.2.2/cinemago-api/api` (emulator Android).

## Endpoint

Semua response berformat:
```json
{ "success": true, "message": "...", "data": { } }
```

### POST `/api/register.php`

Request:
```json
{
  "first_name": "Budi",
  "last_name": "Santoso",
  "email": "budi@mail.com",
  "password": "rahasia123"
}
```

| Status | Arti |
|---|---|
| 201 | Registrasi berhasil |
| 400 | Field kosong / email tidak valid / password < 6 karakter |
| 409 | Email sudah terdaftar |

### POST `/api/login.php`

Request:
```json
{ "email": "budi@mail.com", "password": "rahasia123" }
```

Response sukses (200):
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": { "id": 1, "first_name": "Budi", "last_name": "Santoso", "email": "budi@mail.com" }
}
```

| Status | Arti |
|---|---|
| 200 | Login berhasil |
| 400 | Field kosong |
| 401 | Email atau password salah |

## Menambah Migrasi Baru

Buat file SQL baru di `database/migrations/` dengan nomor urut berikutnya, misalnya
`002_create_movies_table.sql`, lalu jalankan lagi `php database/migrate.php`.
Hanya migrasi yang belum pernah dijalankan yang akan dieksekusi.

## Keamanan

- Password disimpan dengan `password_hash()` (bcrypt).
- Semua query memakai *prepared statement* untuk mencegah SQL injection.
- `config.local.php` masuk `.gitignore` agar kredensial tidak ter-upload.
