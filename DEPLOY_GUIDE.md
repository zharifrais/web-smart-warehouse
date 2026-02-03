# Panduan Deploy Laravel ke Hosting (dengan Terminal)

## Persiapan Sebelum Deploy

### 1. Pastikan File Siap
```bash
# Di local, pastikan tidak ada error
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 2. Update .gitignore
Pastikan `.gitignore` sudah benar (vendor tidak di-commit):
```
/vendor
/node_modules
.env
```

## Cara Deploy ke Hosting

### Metode 1: Upload via Git (Recommended)

#### A. Push ke GitHub (dari local)
```bash
git add .
git commit -m "Ready for production"
git push origin main
```

#### B. Clone di Hosting (via SSH/Terminal)
```bash
# Login ke hosting via SSH
ssh username@smartwarehouse.web.id

# Masuk ke folder web
cd /home/smartwar/

# Clone repository
git clone https://github.com/zharifrais/web-smart-warehouse.git laravel_app

# Masuk ke folder project
cd laravel_app
```

#### C. Install Dependencies
```bash
# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Set permission
chmod -R 775 storage bootstrap/cache
```

#### D. Setup Environment
```bash
# Copy .env
cp .env.example .env

# Edit .env
nano .env
```

Isi .env dengan konfigurasi production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smartwarehouse.web.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartwar_db
DB_USERNAME=smartwar_user
DB_PASSWORD=your_password

TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id
```

#### E. Generate Key & Migrate Database
```bash
# Generate app key
php artisan key:generate

# Run migration
php artisan migrate --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### F. Setup Public Folder
```bash
# Buat symbolic link dari public_html ke laravel_app/public
cd /home/smartwar/
rm -rf public_html
ln -s /home/smartwar/laravel_app/public public_html

# Atau copy isi public ke public_html
cp -r laravel_app/public/* public_html/
```

Edit `public_html/index.php`:
```php
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

### Metode 2: Upload Manual via FTP/cPanel File Manager

#### A. Compress Project (di local)
```bash
# Hapus folder yang tidak perlu
rm -rf node_modules vendor

# Zip project
zip -r smart-warehouse.zip . -x "*.git*" "node_modules/*" "vendor/*"
```

#### B. Upload & Extract (di hosting)
1. Upload `smart-warehouse.zip` ke `/home/smartwar/`
2. Extract via File Manager atau terminal:
```bash
cd /home/smartwar/
unzip smart-warehouse.zip -d laravel_app
cd laravel_app
```

#### C. Lanjutkan dengan langkah C-F dari Metode 1

## Setup Cron Job untuk Telegram Auto-Send

### Via Terminal
```bash
# Edit crontab
crontab -e

# Tambahkan baris ini (tekan i untuk insert mode)
* * * * * cd /home/smartwar/laravel_app && php artisan schedule:run >> /dev/null 2>&1

# Save (tekan ESC, ketik :wq, Enter)
```

### Via cPanel Cron Jobs
1. Masuk cPanel → Cron Jobs
2. Tambahkan:
   - **Minute:** `*`
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:** `cd /home/smartwar/laravel_app && php artisan schedule:run >> /dev/null 2>&1`

## Update Code (Setelah Deploy Pertama)

```bash
# SSH ke hosting
ssh username@smartwarehouse.web.id

# Masuk ke folder project
cd /home/smartwar/laravel_app

# Pull update dari GitHub
git pull origin main

# Install dependencies baru (jika ada)
composer install --optimize-autoloader --no-dev

# Clear & cache ulang
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrate database (jika ada perubahan)
php artisan migrate --force
```

## Troubleshooting

### 1. Error 500
```bash
# Cek log
tail -f storage/logs/laravel.log

# Set permission
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

### 2. Database Connection Error
```bash
# Test koneksi database
php artisan tinker
>>> DB::connection()->getPdo();
```

### 3. Telegram Webhook Tidak Jalan
```bash
# Test manual
php artisan telegram:send-status

# Setup webhook
curl https://smartwarehouse.web.id/telegram/setup-webhook
```

### 4. Cron Job Tidak Jalan
```bash
# Cek cron log
grep CRON /var/log/syslog

# Test manual
cd /home/smartwar/laravel_app && php artisan schedule:run
```

## Checklist Deploy

- [ ] Push code ke GitHub
- [ ] Clone/Upload ke hosting
- [ ] Install composer dependencies
- [ ] Setup .env (production)
- [ ] Generate app key
- [ ] Migrate database
- [ ] Setup public folder/symbolic link
- [ ] Set permission storage & bootstrap/cache
- [ ] Cache config/route/view
- [ ] Setup Telegram webhook
- [ ] Setup cron job
- [ ] Test website di browser
- [ ] Test Telegram bot
- [ ] Test auto-send status (tunggu 15 menit)

## Perintah Berguna

```bash
# Cek versi PHP
php -v

# Cek versi Composer
composer -V

# Cek Laravel version
php artisan --version

# Cek route list
php artisan route:list

# Cek scheduled tasks
php artisan schedule:list

# Clear all cache
php artisan optimize:clear

# Optimize for production
php artisan optimize
```
