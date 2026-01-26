# Setup Telegram Auto-Send Status

## Fitur
Mengirim status gudang otomatis ke Telegram setiap 15 menit.

## Setup di Hosting (Linux/cPanel)

### 1. Test Command Manual
Jalankan command ini untuk test:
```bash
php artisan telegram:send-status
```

Jika berhasil, akan muncul pesan di Telegram.

### 2. Setup Cron Job di cPanel

**Masuk ke cPanel → Cron Jobs**

Tambahkan cron job baru:
```
*/15 * * * * cd /home/smartwar/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```

**Penjelasan:**
- `*/15 * * * *` = Setiap 15 menit
- `cd /home/smartwar/laravel_app` = Masuk ke folder Laravel (sesuaikan path)
- `php artisan schedule:run` = Jalankan Laravel scheduler
- `>> /dev/null 2>&1` = Sembunyikan output

### 3. Setup Cron Job di VPS/Server

Edit crontab:
```bash
crontab -e
```

Tambahkan baris ini:
```
* * * * * cd /path/to/your/laravel && php artisan schedule:run >> /dev/null 2>&1
```

**Catatan:** Laravel scheduler harus dijalankan setiap menit, tapi command kita akan dieksekusi setiap 15 menit.

### 4. Alternatif: Setup di Local (Windows)

Buat file `telegram-scheduler.bat`:
```batch
@echo off
cd /d "D:\kuliah\smt 7\TA\web\smart-warehouse"
php artisan schedule:run
```

Lalu setup di Task Scheduler Windows untuk run setiap 1 menit.

## Verifikasi

Cek log Laravel untuk memastikan command berjalan:
```bash
tail -f storage/logs/laravel.log
```

Atau cek langsung di Telegram, seharusnya menerima pesan setiap 15 menit.

## Customize Interval

Edit `app/Console/Kernel.php`:

```php
// Setiap 5 menit
$schedule->command('telegram:send-status')->everyFiveMinutes();

// Setiap 30 menit
$schedule->command('telegram:send-status')->everyThirtyMinutes();

// Setiap jam
$schedule->command('telegram:send-status')->hourly();

// Setiap hari jam 08:00
$schedule->command('telegram:send-status')->dailyAt('08:00');
```

## Troubleshooting

**Jika tidak jalan:**
1. Pastikan cron job sudah aktif
2. Cek permission folder storage: `chmod -R 775 storage`
3. Test manual: `php artisan telegram:send-status`
4. Cek log: `storage/logs/laravel.log`
