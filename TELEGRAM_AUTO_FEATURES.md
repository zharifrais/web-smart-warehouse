# Fitur Telegram Otomatis - Smart Warehouse

## 🎯 Fitur yang Tersedia

### 1. Update Status Otomatis (Setiap 15 Menit)
Mengirim status gudang secara berkala setiap 15 menit.

**Format Pesan:**
```
🔔 UPDATE OTOMATIS GUDANG

⏰ Waktu: 26/01/2026 15:30:00

🌡 Suhu: 28.5 °C
💧 Kelembaban: 65 %

🟢 Status: NORMAL
```

**Status:**
- 🟢 NORMAL: Suhu ≤ 30°C dan Kelembaban ≤ 70%
- 🟡 WARNING: Suhu > 30°C atau Kelembaban > 70%
- 🔴 ANOMALY: Suhu ≥ 35°C atau Kelembaban ≥ 80%

---

### 2. Alert Otomatis (Real-time saat Anomali)
Mengirim peringatan segera saat suhu/kelembaban melebihi ambang batas.

**Ambang Batas:**
- Suhu: ≥ 35°C
- Kelembaban: ≥ 80%

**Format Pesan Alert:**
```
🚨 PERINGATAN ANOMALI GUDANG!

🌡 Suhu: 36.5 °C (⚠️ Melebihi batas 35°C)
💧 Kelembaban: 82 % (⚠️ Melebihi batas 80%)

⏰ Waktu: 26/01/2026 15:45:30

🔴 Segera lakukan tindakan!
```

**Cooldown:** 5 menit (untuk menghindari spam alert)

---

## 🚀 Cara Setup

### A. Setup Update Status Otomatis (15 Menit)

#### 1. Setup Cron Job di Hosting

**Via Terminal/SSH:**
```bash
crontab -e
```

Tambahkan:
```
* * * * * cd /home/smartwar/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```

**Via cPanel Cron Jobs:**
- Minute: `*`
- Hour: `*`
- Day: `*`
- Month: `*`
- Weekday: `*`
- Command: `cd /home/smartwar/laravel_app && php artisan schedule:run >> /dev/null 2>&1`

#### 2. Test Manual
```bash
php artisan telegram:send-status
```

Cek Telegram, harusnya dapat pesan update status.

---

### B. Setup Alert Otomatis (Real-time)

#### 1. Jalankan MQTT Subscriber

**Di Server/Hosting (Background):**
```bash
cd /home/smartwar/laravel_app
nohup php artisan mqtt:subscribe > storage/logs/mqtt.log 2>&1 &
```

**Di Local (Development):**
```bash
php artisan mqtt:subscribe
```

#### 2. Verifikasi MQTT Berjalan
```bash
# Cek process
ps aux | grep mqtt:subscribe

# Cek log
tail -f storage/logs/mqtt.log
```

#### 3. Test dengan ESP32
Kirim data dari ESP32 dengan suhu/kelembaban tinggi:
```json
{
  "temperature": 36,
  "humidity": 85
}
```

Telegram harusnya langsung dapat alert!

---

## 📋 Konfigurasi Ambang Batas

Edit file: `app/Console/Commands/MqttSubscribe.php`

```php
private function checkThresholdAndAlert($temperature, $humidity)
{
    $tempThreshold = 35; // ← Ubah di sini
    $humThreshold = 80;  // ← Ubah di sini
    
    // ...
}
```

Edit file: `app/Console/Commands/SendTelegramStatus.php`

```php
private function determineStatus($temp, $hum)
{
    if ($temp >= 35 || $hum >= 80) {  // ← ANOMALY
        return 'ANOMALY';
    }
    if ($temp > 30 || $hum > 70) {    // ← WARNING
        return 'WARNING';
    }
    return 'NORMAL';
}
```

---

## 🔧 Troubleshooting

### 1. Update Status Tidak Jalan

**Cek cron job:**
```bash
crontab -l
```

**Cek log Laravel:**
```bash
tail -f storage/logs/laravel.log
```

**Test manual:**
```bash
php artisan telegram:send-status
```

---

### 2. Alert Tidak Jalan

**Cek MQTT subscriber berjalan:**
```bash
ps aux | grep mqtt:subscribe
```

**Restart MQTT subscriber:**
```bash
# Kill process lama
pkill -f mqtt:subscribe

# Jalankan ulang
nohup php artisan mqtt:subscribe > storage/logs/mqtt.log 2>&1 &
```

**Cek log MQTT:**
```bash
tail -f storage/logs/mqtt.log
```

---

### 3. Alert Terlalu Sering (Spam)

Edit cooldown di `MqttSubscribe.php`:
```php
private $alertCooldown = 300; // 5 menit (dalam detik)
```

Ubah menjadi:
```php
private $alertCooldown = 600; // 10 menit
private $alertCooldown = 900; // 15 menit
```

---

## 📊 Monitoring

### Cek Status Cron Job
```bash
# Cek cron log
grep CRON /var/log/syslog | tail -20

# Cek Laravel scheduler
php artisan schedule:list
```

### Cek Status MQTT
```bash
# Cek process
ps aux | grep mqtt

# Cek log realtime
tail -f storage/logs/mqtt.log
```

### Cek Telegram Bot
Kirim command ke bot:
- `/status` - Cek status manual
- `/laporan` - Lihat laporan

---

## 🎛️ Customize Interval Update

Edit `app/Console/Kernel.php`:

```php
// Setiap 5 menit
$schedule->command('telegram:send-status')->everyFiveMinutes();

// Setiap 10 menit
$schedule->command('telegram:send-status')->everyTenMinutes();

// Setiap 15 menit (default)
$schedule->command('telegram:send-status')->everyFifteenMinutes();

// Setiap 30 menit
$schedule->command('telegram:send-status')->everyThirtyMinutes();

// Setiap jam
$schedule->command('telegram:send-status')->hourly();

// Setiap hari jam 08:00
$schedule->command('telegram:send-status')->dailyAt('08:00');
```

---

## ✅ Checklist Setup

- [ ] Setup cron job untuk scheduler
- [ ] Test `php artisan telegram:send-status`
- [ ] Jalankan `php artisan mqtt:subscribe` di background
- [ ] Test kirim data anomali dari ESP32
- [ ] Verifikasi dapat alert di Telegram
- [ ] Verifikasi dapat update status setiap 15 menit
- [ ] Setup auto-restart MQTT subscriber (opsional)

---

## 🔄 Auto-Restart MQTT Subscriber (Opsional)

Buat script `restart-mqtt.sh`:
```bash
#!/bin/bash
cd /home/smartwar/laravel_app
pkill -f mqtt:subscribe
nohup php artisan mqtt:subscribe > storage/logs/mqtt.log 2>&1 &
```

Tambahkan ke cron (restart setiap hari jam 00:00):
```
0 0 * * * /home/smartwar/laravel_app/restart-mqtt.sh
```

---

## 📱 Command Telegram yang Tersedia

- `/status` - Cek status gudang terkini
- `/analisis` - Analisis AI kondisi gudang
- `/laporan` - Laporan monitoring
- `/laporan_pdf` - Download laporan PDF

---

## 🎉 Selesai!

Sekarang sistem akan:
1. ✅ Kirim update status setiap 15 menit
2. ✅ Kirim alert real-time saat ada anomali
3. ✅ Cooldown 5 menit untuk menghindari spam
