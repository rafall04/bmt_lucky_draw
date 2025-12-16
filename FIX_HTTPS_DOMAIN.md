# 🔧 Fix: HTTPS Domain Assets Not Loading

Jika domain menggunakan HTTPS (via Cloudflare Tunnel) tapi assets tidak ter-load atau layout tidak sesuai, ikuti langkah-langkah ini:

## 🚨 Masalah

- Akses via IP (HTTP) berfungsi normal
- Akses via domain (HTTPS) - assets tidak ter-load, layout tidak sesuai
- CSS/JS files return 404 atau mixed content errors

## ✅ Solusi

### 1. Update Code (Pull Latest)

```bash
cd /home/unnet/bmt_lucky_draw
git pull origin main
```

### 2. Update .env

```bash
# Pastikan APP_URL set ke HTTPS domain
sed -i 's|^APP_URL=.*|APP_URL=https://bmtnu.raf.my.id|' .env

# Pastikan SESSION_SECURE_COOKIE=true untuk HTTPS
sed -i 's|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|' .env
# atau tambahkan jika belum ada
grep -q "^SESSION_SECURE_COOKIE=" .env || echo "SESSION_SECURE_COOKIE=true" >> .env
```

### 3. Update Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/bmt_lucky_draw
```

Tambahkan fastcgi_param untuk proxy headers di dalam `location ~ \.php$`:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
    include fastcgi_params;
    fastcgi_read_timeout 300;
    
    # Forward proxy headers for Cloudflare Tunnel / Load Balancers
    fastcgi_param HTTP_X_FORWARDED_PROTO $http_x_forwarded_proto;
    fastcgi_param HTTP_X_FORWARDED_FOR $proxy_add_x_forwarded_for;
    fastcgi_param HTTP_X_FORWARDED_HOST $http_x_forwarded_host;
    fastcgi_param HTTP_X_REAL_IP $remote_addr;
}
```

Test dan reload:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Clear Laravel Cache

```bash
cd /home/unnet/bmt_lucky_draw
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Verify TrustProxies Middleware

Pastikan file `app/Http/Middleware/TrustProxies.php` ada dan `bootstrap/app.php` sudah di-update:

```bash
# Check TrustProxies exists
ls -la app/Http/Middleware/TrustProxies.php

# Check bootstrap/app.php has trustProxies
grep -A 2 "trustProxies" bootstrap/app.php
```

### 6. Rebuild Assets (Jika Perlu)

```bash
npm run build
```

## 🔍 Verifikasi

### 1. Test HTTPS Detection

```bash
# Test dari server
curl -H "X-Forwarded-Proto: https" http://localhost

# Atau test langsung dari browser
# Buka: https://bmtnu.raf.my.id
# Check browser console untuk errors
```

### 2. Check APP_URL

```bash
grep APP_URL .env
# Harus: APP_URL=https://bmtnu.raf.my.id
```

### 3. Check Browser Console

Buka browser DevTools (F12) dan cek:
- Network tab: Pastikan CSS/JS assets load dengan status 200
- Console tab: Tidak ada mixed content errors
- Application tab: Cookies set dengan Secure flag

## 📝 Penjelasan

Masalah terjadi karena:

1. **Cloudflare Tunnel**: Domain menggunakan HTTPS, tapi server menerima HTTP
2. **Laravel tidak detect HTTPS**: Tanpa TrustProxies, Laravel tidak tahu request sebenarnya HTTPS
3. **Asset paths salah**: Assets generate dengan HTTP karena Laravel pikir request adalah HTTP
4. **Mixed content**: Browser block HTTP assets di HTTPS page

**Solusi**:
- **TrustProxies middleware**: Detects HTTPS dari `X-Forwarded-Proto` header
- **Nginx fastcgi_param**: Forward proxy headers ke PHP-FPM
- **APP_URL HTTPS**: Asset paths generate dengan HTTPS
- **SESSION_SECURE_COOKIE**: Cookies set dengan Secure flag

## ✅ Checklist

- [ ] Code sudah di-update (git pull)
- [ ] APP_URL=https://bmtnu.raf.my.id
- [ ] SESSION_SECURE_COOKIE=true
- [ ] Nginx config sudah di-update dengan fastcgi_param
- [ ] TrustProxies middleware ada
- [ ] bootstrap/app.php sudah di-update
- [ ] Laravel cache sudah di-clear dan rebuild
- [ ] Nginx sudah di-reload
- [ ] Test akses domain - assets ter-load
- [ ] Test akses IP - masih berfungsi

---

**Setelah fix, test akses ke https://bmtnu.raf.my.id dan pastikan:**
- ✅ Assets (CSS/JS) ter-load dengan benar
- ✅ Layout sesuai/tidak ada positioning issues
- ✅ Tidak ada mixed content errors di console
- ✅ Login berfungsi dengan benar

