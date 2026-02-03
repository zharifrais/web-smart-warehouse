# File yang Perlu Diupload ke Hosting (Manual)

## ✅ WAJIB UPLOAD (Semua Folder & File Ini)

```
smart-warehouse/
├── app/                    ✅ UPLOAD (semua isi)
├── bootstrap/              ✅ UPLOAD (semua isi)
├── config/                 ✅ UPLOAD (semua isi)
├── database/               ✅ UPLOAD (semua isi)
├── public/                 ✅ UPLOAD (semua isi)
├── resources/              ✅ UPLOAD (semua isi)
├── routes/                 ✅ UPLOAD (semua isi)
├── storage/                ✅ UPLOAD (struktur folder saja, lihat detail di bawah)
├── artisan                 ✅ UPLOAD
├── composer.json           ✅ UPLOAD
├── composer.lock           ✅ UPLOAD
└── .env.example            ✅ UPLOAD (nanti copy jadi .env)
```

## ❌ JANGAN UPLOAD (Skip Folder Ini)

```
❌ .git/                    (folder git, tidak perlu)
❌ node_modules/            (akan di-install ulang jika perlu)
❌ vendor/                  (akan di-install via composer)
❌ .env                     (buat baru di hosting)
❌ .gitignore               (opsional)
❌ README.md                (opsional)
❌ *.md files               (dokumentasi, opsional)
```

## 📁 Struktur Folder storage/ yang Perlu Diupload

```
storage/
├── app/
│   └── public/             ✅ UPLOAD (folder kosong atau isi jika ada file)
├── framework/
│   ├── cache/
│   │   └── data/           ✅ UPLOAD (folder kosong, buat .gitkeep)
│   ├── sessions/           ✅ UPLOAD (folder kosong)
│   ├── testing/            ✅ UPLOAD (folder kosong)
│   └── views/              ✅ UPLOAD (folder kosong)
└── logs/                   ✅ UPLOAD (folder kosong)
```

## 📋 Langkah Upload Manual

### 1. Persiapan di Local

```bash
# Hapus folder yang tidak perlu
rmdir /s /q node_modules
rmdir /s /q vendor
rmdir /s /q .git

# Atau buat ZIP tanpa folder tersebut
```

### 2. Buat ZIP File

**Windows (PowerShell):**
```powershell
# Compress semua kecuali folder yang tidak perlu
Compress-Archive -Path * -DestinationPath smart-warehouse.zip -Force
```

**Atau manual:**
- Pilih semua folder/file KECUALI: `.git`, `node_modules`, `vendor`, `.env`
- Klik kanan → Send to → Compressed (zipped) folder
- Nama: `smart-warehouse.zip`

### 3. Upload ke Hosting

**Via cPanel File Manager:**
1. Login cPanel
2. File Manager → `/home/smartwar/`
3. Upload `smart-warehouse.zip`
4. Klik kanan → Extract
5. Rename folder hasil extract menjadi `laravel_app`

**Via FTP (FileZilla):**
1. Connect ke hosting
2. Upload folder `smart-warehouse` ke `/home/smartwar/laravel_app/`

### 4. Setup di Hosting (via Terminal/SSH)

```bash
# Masuk ke folder project
cd /home/smartwar/laravel_app

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Buat .env dari .env.example
cp .env.example .env
nano .env  # Edit konfigurasi

# Generate app key
php artisan key:generate

# Set permission
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache

# Buat symbolic link storage
php artisan storage:link

# Migrate database
php artisan migrate --force

# Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Setup Public Folder

**Opsi A: Symbolic Link (Recommended)**
```bash
cd /home/smartwar/
rm -rf public_html
ln -s /home/smartwar/laravel_app/public public_html
```

**Opsi B: Edit index.php di public_html**
```bash
# Copy file public ke public_html
cp -r laravel_app/public/* public_html/

# Edit public_html/index.php
nano public_html/index.php
```

Ubah path menjadi:
```php
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

## 🔍 Checklist File Penting

### File Wajib Ada:
- ✅ `artisan` (CLI Laravel)
- ✅ `composer.json` & `composer.lock`
- ✅ `app/` (semua controller, model, dll)
- ✅ `config/` (konfigurasi)
- ✅ `routes/` (web.php, api.php)
- ✅ `resources/views/` (blade templates)
- ✅ `public/` (index.php, css, js)
- ✅ `database/migrations/` (struktur database)
- ✅ `storage/` (struktur folder)
- ✅ `bootstrap/` (app.php, cache/)

### File yang Akan Dibuat di Hosting:
- `.env` (copy dari .env.example)
- `vendor/` (via composer install)
- `storage/logs/laravel.log` (otomatis)
- `bootstrap/cache/*.php` (otomatis)

## 📦 Ukuran File

Setelah hapus `node_modules`, `vendor`, `.git`:
- **Ukuran ZIP:** ~5-10 MB
- **Setelah extract + composer install:** ~50-100 MB

## ⚠️ Catatan Penting

1. **Jangan upload `.env`** - Buat baru di hosting dengan konfigurasi production
2. **Jangan upload `vendor/`** - Install via `composer install` di hosting
3. **Set permission storage/** - `chmod -R 775 storage bootstrap/cache`
4. **Pastikan PHP version** - Minimal PHP 8.1 (sesuai Laravel 10)
5. **Install Composer** - Pastikan hosting punya Composer

## 🚀 Quick Command (Setelah Upload)

```bash
cd /home/smartwar/laravel_app
composer install --optimize-autoloader --no-dev
cp .env.example .env
nano .env
php artisan key:generate
php artisan migrate --force
chmod -R 775 storage bootstrap/cache
php artisan storage:link
php artisan optimize
```

Selesai! 🎉
