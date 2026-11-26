# API Contract — Sudut Biru POS

**Version:** 0.1.0  
**Status:** Core Authentication Ready  
**Backend:** PHP Native + MySQL/MariaDB  
**Authentication:** PHP Session + CSRF Token

## 1. Base URL

Local development:

`http://localhost/pos-sudut-biru-be/api`

Base URL dapat disesuaikan dengan alamat server backend saat integrasi.

## 2. Standard Response

Seluruh endpoint menggunakan format JSON berikut:

```json
{
    "status": "success",
    "message": "Pesan response",
    "data": null
}
```

## 3. Available Endpoints

| Method | Endpoint | Authentication | CSRF |
|---|---|---|---|
| GET | /health.php | No | No |
| POST | /auth/login.php | No | No |
| GET | /auth/check-session.php | Yes | No |
| POST | /auth/logout.php | Yes | Yes |

## 4. Health Check

**GET /health.php**

Response 200:

```json
{
    "status": "success",
    "message": "API Sudut Biru POS berjalan normal.",
    "data": {
        "app_env": "local",
        "database": "sudut_biru_pos",
        "db_version": "10.4.28-MariaDB",
        "server_time": "2026-11-26 23:58:24"
    }
}
```

## 5. Login

**POST /auth/login.php**

Header:

`Content-Type: application/json`

Request body:

```json
{
    "username": "feylisha.admin",
    "password": "PASSWORD_ASLI",
    "role": "admin"
}
```

Role yang tersedia: `admin` dan `kasir`.

Response 200:

```json
{
    "status": "success",
    "message": "Login berhasil.",
    "data": {
        "user": {
            "id": 1,
            "staff_code": "FA1707",
            "name": "Feylisha Azzahra",
            "username": "feylisha.admin",
            "email": "admin@sudutbiru.id",
            "role": "admin",
            "shift_id": null,
            "must_change_password": false
        },
        "csrf_token": "TOKEN_DARI_BACKEND"
    }
}
```

## 6. Check Session

**GET /auth/check-session.php**

Tidak membutuhkan request body.

Response 200 mengembalikan data user dan CSRF token dengan struktur data yang sama seperti login, serta pesan `Session aktif.`.

Jika session tidak valid, backend mengembalikan status 401.

## 7. Logout

**POST /auth/logout.php**

Header:

`X-CSRF-Token: TOKEN_DARI_BACKEND`

Tidak membutuhkan request body.

Response 200:

```json
{
    "status": "success",
    "message": "Logout berhasil.",
    "data": null
}
```

## 8. HTTP Status Codes

| Code | Description |
|---|---|
| 200 | Request berhasil |
| 401 | Kredensial atau session tidak valid |
| 403 | Hak akses atau CSRF token tidak valid |
| 404 | Resource tidak ditemukan |
| 405 | HTTP method tidak sesuai |
| 422 | Validasi input gagal |
| 500 | Internal server error |

## 9. Frontend Integration Rules

- Gunakan `credentials: "include"` pada request Fetch API.
- Gunakan password asli ketika login, bukan password hash.
- Arahkan pengguna berdasarkan role yang dikembalikan backend.
- Jalankan check-session ketika halaman yang dilindungi dibuka atau di-refresh.
- Sertakan CSRF token pada request perubahan data yang membutuhkan autentikasi.
- Jika mendapatkan status 401 pada halaman yang dilindungi, arahkan pengguna ke halaman login.
- Jika mendapatkan status 403, tampilkan pesan akses ditolak.
- Jangan menyimpan password maupun session ID secara manual di localStorage.
- Gunakan origin yang sama selama integrasi lokal apabila memungkinkan.

## 10. Development Status

Core Authentication:
- Login Admin dan Kasir
- Session validation
- Logout
- Role authorization helper
- CSRF protection

Upcoming Features:
- Profile management
- Change password
- Forgot and reset password
- Menu management
- Inventory management
- Transactions and payment
- Shift management
- Staff management
- Dashboard and reports