# Berani Digital ID — Technical Challenge

Data Acquisition Engine dengan 3 connector (Website Metadata, Domain Intelligence, Company Location) + 1 endpoint integrasi.

## Tech Stack
- Laravel 11 (PHP 8.2+)
- Laravel HTTP Client (Guzzle) untuk konsumsi API eksternal
- Cache (file/redis) untuk endpoint integrasi

## Instalasi

```bash
# 1. Clone repo
git clone <url-repo-kamu>
cd berani-digital-challenge

# 2. Install dependency PHP
composer install

# 3. Copy env & generate key
cp .env.example .env
php artisan key:generate

# 4. Jalankan server
php artisan serve
```

Server akan berjalan di `http://127.0.0.1:8000`.

## ⚠️ PENTING untuk Laravel 11 (langkah yang sering kelewat pemula)

Di Laravel 11, `routes/api.php` **tidak otomatis aktif**. Kamu harus daftarkan dulu di `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',   // <-- tambahkan baris ini
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

Kalau kamu pakai Laravel 10 ke bawah, `routes/api.php` sudah otomatis ke-load, tidak perlu langkah ini.

## Struktur Project

```
app/
├── Http/Controllers/
│   ├── WebsiteController.php          → POST /api/extract/website
│   ├── DomainController.php           → POST /api/extract/domain
│   ├── LocationController.php         → POST /api/extract/location
│   └── CompanyInformationController.php → GET  /api/company-information
└── Services/
    ├── WebsiteMetadataService.php     → scraping HTML (title, meta, OG, email, phone, social)
    ├── DomainIntelligenceService.php  → konsumsi RDAP API
    └── LocationService.php            → konsumsi Nominatim OpenStreetMap
routes/
└── api.php                            → daftar route
```

## Dokumentasi Endpoint

### 1. POST /api/extract/website
Body (JSON):
```json
{ "url": "https://paper.id" }
```

### 2. POST /api/extract/domain
Body (JSON):
```json
{ "domain": "paper.id" }
```

### 3. POST /api/extract/location
Body (JSON):
```json
{ "query": "PT Telkom Indonesia" }
```

### 4. GET /api/company-information?domain=paper.id
Menggabungkan ketiga connector di atas, hasil di-cache 1 jam per domain.

## Error Handling
Semua endpoint mengembalikan format konsisten:
```json
{ "success": false, "message": "pesan error" }
```
dengan HTTP status 422 untuk kegagalan validasi/proses, dan tetap 200 dengan field `errors` pada endpoint integrasi (partial failure — jika salah satu connector gagal, connector lain tetap dikembalikan).

## Asumsi & Kendala
- Query lokasi pada endpoint integrasi menggunakan `title` website sebagai fallback nama pencarian ke Nominatim, karena RDAP tidak selalu memuat nama resmi perusahaan.
- Nominatim API mewajibkan header `User-Agent` yang jelas sesuai usage policy mereka.
- Ekstraksi email & nomor telepon dari HTML menggunakan regex sederhana; false positive mungkin terjadi pada halaman kompleks.

## Testing Manual (Postman/curl)
```bash
curl -X POST http://127.0.0.1:8000/api/extract/website \
  -H "Content-Type: application/json" \
  -d '{"url":"https://paper.id"}'
