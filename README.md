# Berani Digital ID — Technical Challenge

Data Acquisition Engine dengan 3 connector independen (Website Metadata, Domain Intelligence, Company Location) + 1 endpoint integrasi.

## Tech Stack
- Laravel 13 (PHP 8.3)
- Laravel HTTP Client (Guzzle) untuk konsumsi API eksternal
- SQLite untuk database
- Cache untuk endpoint integrasi

## Instalasi

```bash
git clone <url-repo-kamu>
cd berani-digital-challenge
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Server berjalan di `http://127.0.0.1:8000`.

> Catatan: di Laravel versi terbaru, `routes/api.php` tidak otomatis aktif. Kalau endpoint `/api/...` mengembalikan 404, jalankan `php artisan install:api` lalu pastikan `bootstrap/app.php` punya baris `api: __DIR__.'/../routes/api.php'` di dalam `withRouting()`.

## Struktur Project

```
app/
├── Http/Controllers/
│   ├── WebsiteController.php              → POST /api/extract/website
│   ├── DomainController.php               → POST /api/extract/domain
│   ├── LocationController.php             → POST /api/extract/location
│   └── CompanyInformationController.php   → GET  /api/company-information
└── Services/
    ├── WebsiteMetadataService.php      → scraping HTML (title, meta, OG, email, phone, social)
    ├── DomainIntelligenceService.php   → konsumsi RDAP API
    ├── LocationService.php             → konsumsi Nominatim OpenStreetMap
    └── DataAcquisitionEngine.php       → orkestrator, gabungkan 3 service di atas
routes/
└── api.php
```

## Dokumentasi Endpoint

**POST /api/extract/website**
```json
{ "url": "https://paper.id" }
```

**POST /api/extract/domain**
```json
{ "domain": "paper.id" }
```

**POST /api/extract/location**
```json
{ "query": "PT Telkom Indonesia" }
```

**GET /api/company-information?domain=paper.id**
Menggabungkan ketiga connector, hasil di-cache 1 jam per domain.

## Error Handling
Format konsisten di semua endpoint:
```json
{ "success": false, "message": "pesan error" }
```
Status 422 untuk kegagalan, dan tetap 200 dengan field `errors` di endpoint integrasi (partial failure — kalau salah satu connector gagal, connector lain tetap dikembalikan).

## Asumsi & Kendala
- Query lokasi pada endpoint integrasi pakai `title` website sebagai fallback nama pencarian, karena RDAP tidak selalu memuat nama resmi perusahaan.
- Nominatim mewajibkan header `User-Agent` yang unik/spesifik — request dengan User-Agent generic sempat kena 403 selama development.
- Ekstraksi email & nomor telepon dari HTML pakai regex sederhana, jadi berpotensi false positive di halaman kompleks.

## Testing
```bash
curl -X POST http://127.0.0.1:8000/api/extract/website \
  -H "Content-Type: application/json" \
  -d '{"url":"https://paper.id"}'
```
