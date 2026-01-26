# Setup Telegram Webhook untuk Domain Online

## Langkah-langkah Setup:

### 1. Upload Project ke Hosting
Pastikan project sudah di-upload ke hosting dengan domain **smartwarehouse.web.id**

### 2. Setup Webhook Telegram
Akses URL berikut di browser (sekali saja):

```
https://smartwarehouse.web.id/telegram/setup-webhook
```

Jika berhasil, akan muncul response:
```json
{
  "ok": true,
  "result": true,
  "description": "Webhook was set"
}
```

### 3. Test Webhook
Kirim pesan ke bot Telegram Anda:
- `/status` - Cek status gudang terkini
- `/analisis` - Analisis AI kondisi gudang
- `/laporan` - Laporan monitoring
- `/laporan_pdf` - Download laporan PDF

### 4. Verifikasi Webhook Aktif
Cek webhook info dengan URL:
```
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo
```

Ganti `<YOUR_BOT_TOKEN>` dengan token bot Anda dari .env

### Troubleshooting:

**Jika webhook tidak berfungsi:**

1. Pastikan SSL certificate domain valid (HTTPS)
2. Cek log Laravel: `storage/logs/laravel.log`
3. Pastikan route `/telegram/webhook` accessible
4. Test manual webhook:
   ```bash
   curl -X POST https://smartwarehouse.web.id/telegram/webhook \
   -H "Content-Type: application/json" \
   -d '{"message":{"text":"/status","chat":{"id":"YOUR_CHAT_ID"}}}'
   ```

**Reset webhook jika perlu:**
```
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/deleteWebhook
```

Lalu setup ulang dengan langkah 2.

### Catatan Penting:
- Webhook hanya bisa diakses via HTTPS (tidak bisa HTTP)
- Domain harus memiliki SSL certificate yang valid
- Telegram akan mengirim POST request ke `/telegram/webhook` setiap ada pesan baru
