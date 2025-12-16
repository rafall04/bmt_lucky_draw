# 🔧 Manual Fix untuk Production Issues

Jika Anda sudah terlanjur install dan mengalami masalah, ikuti langkah-langkah ini:

## 🚨 Masalah yang Ditemukan

1. **Web Server Conflict**: Apache dan Nginx running bersamaan → Port 80 conflict
2. **APP_URL Salah**: Set ke IP (172.17.2.5) padahal domain pakai HTTPS
3. **Asset Loading Gagal**: Vite assets tidak bisa di-load karena APP_URL salah
4. **Session Cookie Error**: HTTPS butuh secure cookies tapi tidak di-set
5. **404 Not Found**: Web server salah atau konfigurasi tidak benar

## ✅ Solusi Manual

### 1. Fix Web Server Conflict

```bash
# Cek web server yang running
sudo systemctl status nginx
sudo systemctl status apache2

# Pilih salah satu (disarankan Nginx)
# Jika pilih Nginx, stop Apache:
sudo systemctl stop apache2
sudo systemctl disable apache2

# Jika pilih Apache, stop Nginx:
sudo systemctl stop nginx
sudo systemctl disable nginx
```

### 2. Fix APP_URL dan HTTPS Configuration

```bash
cd /home/unnet/bmt_lucky_draw

# Edit .env
nano .env

# Update APP_URL ke domain HTTPS:
APP_URL=https://bmtnu.raf.my.id

# Tambahkan untuk HTTPS:
SESSION_SECURE_COOKIE=true

# Save dan exit (Ctrl+X, Y, Enter)
```

### 3. Clear Laravel Cache

```bash
cd /home/unnet/bmt_lucky_draw

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Rebuild Assets (Jika Tampilan Rusak)

```bash
cd /home/unnet/bmt_lucky_draw

# Rebuild assets dengan APP_URL yang benar
npm run build

# Pastikan manifest.json ada
ls -la public/build/manifest.json
```

### 5. Fix Web Server Configuration

#### Untuk Nginx:

```bash
# Cek konfigurasi
sudo nano /etc/nginx/sites-available/bmt_lucky_draw

# Pastikan server_name benar:
server_name bmtnu.raf.my.id 172.17.2.5;

# Test konfigurasi
sudo nginx -t

# Reload
sudo systemctl reload nginx
```

#### Untuk Apache:

```bash
# Cek konfigurasi
sudo nano /etc/apache2/sites-available/bmt_lucky_draw.conf

# Pastikan ServerName benar:
ServerName bmtnu.raf.my.id

# Test konfigurasi
sudo apache2ctl configtest

# Restart
sudo systemctl restart apache2
```

### 6. Fix Permissions

```bash
cd /home/unnet/bmt_lucky_draw

# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart web server (pilih salah satu)
sudo systemctl restart nginx  # atau
sudo systemctl restart apache2
```

## 🔍 Verifikasi

### 1. Cek Web Server

```bash
# Cek port 80
sudo netstat -tlnp | grep :80

# Harusnya hanya satu web server yang listening
```

### 2. Cek APP_URL

```bash
cd /home/unnet/bmt_lucky_draw
grep APP_URL .env
# Harus: APP_URL=https://bmtnu.raf.my.id
```

### 3. Test Akses

```bash
# Test dari server
curl -I http://172.17.2.5
curl -I https://bmtnu.raf.my.id

# Cek asset loading
curl -I https://bmtnu.raf.my.id/build/manifest.json
```

### 4. Cek Log Error

```bash
# Nginx
sudo tail -f /var/log/nginx/error.log

# Apache
sudo tail -f /var/log/apache2/error.log

# Laravel
tail -f storage/logs/laravel.log
```

## 🎯 Quick Fix Script

Jalankan script ini untuk fix otomatis:

```bash
#!/bin/bash
cd /home/unnet/bmt_lucky_draw

# Stop Apache (jika pakai Nginx)
sudo systemctl stop apache2
sudo systemctl disable apache2

# Update .env
sed -i 's|^APP_URL=.*|APP_URL=https://bmtnu.raf.my.id|' .env
if ! grep -q "^SESSION_SECURE_COOKIE=" .env; then
    echo "SESSION_SECURE_COOKIE=true" >> .env
else
    sed -i 's|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|' .env
fi

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Rebuild assets
npm run build

# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx

echo "✓ Fix completed!"
```

## 📝 Catatan Penting

1. **Cloudflare Tunnel**: Jika pakai Cloudflare Tunnel, APP_URL harus HTTPS domain, bukan IP
2. **Session Cookies**: HTTPS memerlukan `SESSION_SECURE_COOKIE=true`
3. **Asset Paths**: Vite menggunakan APP_URL untuk generate asset paths
4. **Web Server**: Hanya satu web server yang boleh running di port 80

## ✅ Checklist

- [ ] Hanya satu web server running (Nginx atau Apache)
- [ ] APP_URL set ke `https://bmtnu.raf.my.id`
- [ ] SESSION_SECURE_COOKIE=true di .env
- [ ] Laravel cache sudah di-clear dan rebuild
- [ ] Assets sudah di-rebuild (`npm run build`)
- [ ] Permissions sudah benar
- [ ] PHP-FPM dan web server sudah restart
- [ ] Test akses dari browser

---

**Setelah fix, test akses ke https://bmtnu.raf.my.id dan pastikan:**
- ✅ Halaman load dengan benar
- ✅ CSS/JS assets ter-load
- ✅ Login berfungsi
- ✅ Tidak ada error di console browser

