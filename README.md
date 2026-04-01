# 🔓 VulnApp Pentest Lab - Laravel 13

> Aplikasi Laravel 13 yang **sengaja dibuat vulnerable** untuk praktik penetration testing berdasarkan **OWASP Top 10 (2021)**.

## ⚠️ PERINGATAN

```
╔══════════════════════════════════════════════════════════════╗
║  APLIKASI INI SENGAJA MEMILIKI KERENTANAN KEAMANAN!         ║
║  JANGAN DEPLOY KE PRODUCTION ATAU SERVER PUBLIK!            ║
║  HANYA UNTUK TUJUAN EDUKASI DI LINGKUNGAN LOKAL.            ║
╚══════════════════════════════════════════════════════════════╝
```

## 📋 Prasyarat

- PHP 8.2+
- Composer
- SQLite (sudah built-in di PHP)
- Node.js (opsional, untuk frontend assets)

## 🚀 Instalasi

```bash
# Clone repository
git clone <repository-url>
cd pentest_project

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Buat database SQLite
touch database/database.sqlite

# Jalankan migration dan seeder
php artisan migrate --seed

# Buat storage link
php artisan storage:link

# Jalankan server
php artisan serve
```

Buka browser: **http://localhost:8000**

## �� Akun Demo

| Email | Password | Role |
|-------|----------|------|
| `admin@pentest.local` | `admin123` | admin |
| `budi@pentest.local` | `password` | user |
| `siti@pentest.local` | `123456` | user |
| `andi@pentest.local` | `qwerty` | editor |

## 📁 Struktur Kerentanan

```
pentest_project/
├── pentest/                    # 📚 Panduan lengkap
│   ├── README.md               # Overview & cara mengerjakan
│   ├── A01.md                  # Broken Access Control
│   ├── A02.md                  # Cryptographic Failures
│   ├── A03.md                  # Injection (SQLi, XSS, CMDi)
│   ├── A04.md                  # Insecure Design
│   ├── A05.md                  # Security Misconfiguration
│   ├── A06.md                  # Vulnerable Components
│   ├── A07.md                  # Auth Failures
│   ├── A08.md                  # Integrity Failures
│   ├── A09.md                  # Logging Failures
│   └── A10.md                  # SSRF
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php      # A01, A04, A07, A09
│   │   ├── PostController.php      # A01, A03
│   │   ├── NoteController.php      # A01
│   │   ├── AdminController.php     # A01, A03, A05
│   │   ├── ProfileController.php   # A01, A02
│   │   ├── FileController.php      # A03, A08, A10
│   │   └── ApiController.php       # A01, A03, A07, A08, A09
│   └── Models/
│       ├── User.php                # A01 (mass assignment)
│       ├── Post.php
│       ├── Note.php
│       └── ApiKey.php              # A02 (plaintext secrets)
├── resources/views/
│   ├── layouts/app.blade.php       # A05, A06, A08 (debug info, CDN)
│   ├── posts/                      # A03 (XSS via {!! !!})
│   ├── auth/                       # A01, A07 (weak registration)
│   └── admin/                      # A01, A02 (SSN exposure)
├── routes/web.php                  # A01 (missing middleware)
├── config/cors.php                 # A05 (wildcard CORS)
└── .env                            # A05 (APP_DEBUG=true)
```

## 🎯 Mapping OWASP Top 10

| OWASP | Kerentanan | Lokasi |
|-------|-----------|--------|
| **A01** | IDOR, Missing Auth, Privilege Escalation | NoteController, AdminController, ProfileController, Routes |
| **A02** | Plaintext SSN, MD5 Keys, Predictable Tokens | User model, ProfileController, AuthController |
| **A03** | SQL Injection, Stored XSS, Command Injection | PostController, AdminController, FileController |
| **A04** | No Rate Limit, No CAPTCHA, Token No Expiry | AuthController, Routes |
| **A05** | Debug Mode, CORS *, System Info Leak | .env, cors.php, AdminController, Layout |
| **A06** | CDN tanpa SRI, No Dependency Audit | Layout view, composer.json |
| **A07** | Weak Password, Brute Force, Enumeration | AuthController, Seeder |
| **A08** | Insecure Deserialization, Unrestricted Upload | FileController, ApiController |
| **A09** | No Auth Logging, No Audit Trail | All Controllers |
| **A10** | SSRF via URL Fetch, Image Proxy | FileController |

## 📝 Tugas Mahasiswa

1. Baca panduan di folder `pentest/`
2. Buktikan setiap kerentanan (minimal A01-A10)
3. Fix setiap kerentanan di kode
4. Verifikasi fix sudah benar
5. Buat laporan penetration testing

## 📄 Lisensi

Project ini hanya untuk tujuan edukasi. Tidak untuk penggunaan production.
