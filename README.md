# Data Acquisition Engine — Berani Digital ID Technical Challenge

API yang menggabungkan 3 connector independen (Website Metadata, Domain Intelligence, Company Location) menjadi satu endpoint integrasi.

## Tech Stack
Laravel 13, PHP 8.3, SQLite

## Instalasi
```bash
git clone <url-repo-kamu>
cd Data-Acquisition-Engine
composer install
cp .env.example .env
php artisan key:generate
type nul > database\database.sqlite   # (Linux/Mac: touch database/database.sqlite)
php artisan migrate
php artisan serve
```
Jalan di `http://127.0.0.1:8000`.

> Kalau endpoint `/api/...` 404: jalankan `php artisan install:api`, lalu pastikan `bootstrap/app.php` punya baris `api: __DIR__.'/../routes/api.php'` di dalam `withRouting()`.

## Struktur
```
app/Http/Controllers/   → WebsiteController, DomainController, LocationController, CompanyInformationController
app/Services/           → WebsiteMetadataService, DomainIntelligenceService, LocationService, DataAcquisitionEngine (orkestrator)
routes/api.php          → daftar endpoint
```

## Endpoint

| Method | URL | Body |
|---|---|---|
| POST | `/api/extract/website` | `{ "url": "https://paper.id" }` |
| POST | `/api/extract/domain` | `{ "domain": "paper.id" }` |
| POST | `/api/extract/location` | `{ "query": "PT Telkom Indonesia" }` |
| GET | `/api/company-information?domain=paper.id` | - |

## Error Handling
- Semua endpoint mengembalikan format konsisten saat gagal: `{ "success": false, "message": "..." }` dengan HTTP status **422**.
- Khusus endpoint integrasi (`/company-information`), kegagalan salah satu connector **tidak** membuat seluruh request gagal (partial failure) — status tetap **200**, dan connector yang error dicatat di field `errors`, sementara connector lain yang berhasil tetap dikembalikan.

## Asumsi & Kendala
- Query lokasi di endpoint integrasi pakai `title` website sebagai fallback, karena RDAP tidak selalu memuat nama resmi perusahaan.
- Nominatim mewajibkan header `User-Agent` yang unik — User-Agent generic sempat kena 403 saat development.
- Ekstraksi email/telepon dari HTML pakai regex sederhana, berpotensi false positive di halaman kompleks.

## Test cepat
```bash
curl -X POST http://127.0.0.1:8000/api/extract/domain -H "Content-Type: application/json" -d "{\"domain\":\"paper.id\"}"
```