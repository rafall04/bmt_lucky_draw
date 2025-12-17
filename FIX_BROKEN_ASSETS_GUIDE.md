# 🔧 Fix: Broken Assets on Domain Access

Panduan lengkap untuk memperbaiki masalah CSS/JS/images yang tidak ter-load saat diakses via Domain (HTTPS), padahal normal saat diakses via IP (HTTP).

## 🚨 Gejala Masalah

- ✅ Website accessible via IP (HTTP) - **berfungsi normal**
- ❌ Website accessible via Domain (HTTPS) - **assets broken/404**
- ❌ CSS/JS files return 404 atau blocked (Mixed Content)
- ❌ Layout tidak sesuai, positioning salah
- ❌ Images tidak muncul

## 🔍 Root Cause

Masalah terjadi karena:

1. **APP_URL Mismatch**: `.env` masih set ke IP/HTTP, bukan domain HTTPS
2. **Vite Manifest Paths**: Vite menggunakan `APP_URL` saat build time untuk generate asset paths di `manifest.json`
3. **Mixed Content**: Browser block HTTP assets di HTTPS page
4. **Session Cookies**: Cookies tidak set dengan Secure flag untuk HTTPS

## ✅ Solusi Lengkap

### Method 1: Menggunakan Script Otomatis (RECOMMENDED)

```bash
cd /home/unnet/bmt_lucky_draw

# Pull latest code
git pull origin main

# Jalankan fix script
bash FIX_BROKEN_ASSETS_DOMAIN.sh https://bmtnu.raf.my.id
```

Script akan otomatis:
- ✅ Backup `.env` file
- ✅ Update `APP_URL` ke domain HTTPS
- ✅ Comment `ASSET_URL` (biar pakai APP_URL)
- ✅ Set `SESSION_SECURE_COOKIE=true`
- ✅ Clear semua Laravel caches
- ✅ Rebuild config cache
- ✅ **Rebuild Vite assets** (CRITICAL!)
- ✅ Verify build output

### Method 2: Manual Step-by-Step

#### Step 1: Update .env File

```bash
cd /home/unnet/bmt_lucky_draw

# Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update APP_URL ke domain HTTPS
sed -i 's|^APP_URL=.*|APP_URL=https://bmtnu.raf.my.id|' .env

# Comment ASSET_URL (biar pakai APP_URL)
sed -i 's|^ASSET_URL=|#ASSET_URL=|' .env

# Set SESSION_SECURE_COOKIE untuk HTTPS
sed -i 's|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|' .env
# Atau tambahkan jika belum ada
grep -q "^SESSION_SECURE_COOKIE=" .env || echo "SESSION_SECURE_COOKIE=true" >> .env

# Verify changes
grep -E "^APP_URL=|^ASSET_URL=|^SESSION_SECURE_COOKIE=" .env
```

**Expected output:**
```
APP_URL=https://bmtnu.raf.my.id
#ASSET_URL=...
SESSION_SECURE_COOKIE=true
```

#### Step 2: Clear Laravel Caches

```bash
cd /home/unnet/bmt_lucky_draw

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild config cache (with new APP_URL)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 3: Rebuild Vite Assets (CRITICAL!)

**Ini adalah step yang PALING PENTING!** Vite menggunakan `APP_URL` saat build time untuk generate asset paths.

```bash
cd /home/unnet/bmt_lucky_draw

# Fix vite permissions (jika perlu)
chmod +x node_modules/.bin/vite 2>/dev/null || sudo chmod +x node_modules/.bin/vite 2>/dev/null || true

# Rebuild assets
npm run build

# Jika npm run build gagal, coba:
npx vite build
```

**Verifikasi build:**
```bash
# Check manifest.json exists
ls -la public/build/manifest.json

# Check manifest contains asset references
grep -E "\.css|\.js" public/build/manifest.json | head -5
```

#### Step 4: Verify Nginx Configuration

Pastikan Nginx sudah configure dengan benar untuk HTTPS:

```bash
# Check Nginx config
sudo cat /etc/nginx/sites-available/bmt_lucky_draw | grep -A 5 "server_name"

# Pastikan ada fastcgi_param untuk proxy headers
sudo grep -A 10 "location ~ \.php$" /etc/nginx/sites-available/bmt_lucky_draw
```

Jika belum ada, tambahkan di `location ~ \.php$`:

```nginx
# Forward proxy headers for Cloudflare Tunnel / Load Balancers
fastcgi_param HTTP_X_FORWARDED_PROTO $http_x_forwarded_proto;
fastcgi_param HTTP_X_FORWARDED_FOR $proxy_add_x_forwarded_for;
fastcgi_param HTTP_X_FORWARDED_HOST $http_x_forwarded_host;
fastcgi_param HTTP_X_REAL_IP $remote_addr;
```

Reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

## 🔍 Verifikasi

### 1. Check APP_URL

```bash
cd /home/unnet/bmt_lucky_draw
grep "^APP_URL=" .env
# Harus: APP_URL=https://bmtnu.raf.my.id
```

### 2. Check Manifest.json

```bash
cd /home/unnet/bmt_lucky_draw
cat public/build/manifest.json | head -20
# Harus ada entries untuk CSS/JS files
```

### 3. Test Assets Directly

```bash
# Test dari server
curl -I https://bmtnu.raf.my.id/build/assets/app-*.css
curl -I https://bmtnu.raf.my.id/build/assets/app-*.js

# Harus return 200 OK, bukan 404
```

### 4. Check Browser Console

Buka browser DevTools (F12) dan cek:
- **Network tab**: Pastikan CSS/JS assets load dengan status 200
- **Console tab**: Tidak ada mixed content errors
- **Application tab**: Cookies set dengan Secure flag

## 📋 Quick Fix (One-Liner)

Jika ingin cepat, jalankan semua perintah sekaligus:

```bash
cd /home/unnet/bmt_lucky_draw && \
sed -i 's|^APP_URL=.*|APP_URL=https://bmtnu.raf.my.id|' .env && \
sed -i 's|^ASSET_URL=|#ASSET_URL=|' .env && \
sed -i 's|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|' .env && \
grep -q "^SESSION_SECURE_COOKIE=" .env || echo "SESSION_SECURE_COOKIE=true" >> .env && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan config:cache && \
chmod +x node_modules/.bin/vite 2>/dev/null || sudo chmod +x node_modules/.bin/vite 2>/dev/null || true && \
npm run build || npx vite build && \
echo "✅ Fix completed! Test your website now."
```

## ⚠️ Troubleshooting

### Assets Masih 404

1. **Check manifest.json exists:**
   ```bash
   ls -la public/build/manifest.json
   ```

2. **Check build directory permissions:**
   ```bash
   ls -la public/build/
   chmod -R 755 public/build
   ```

3. **Rebuild assets lagi:**
   ```bash
   npm run build
   ```

### Mixed Content Errors

1. **Check APP_URL is HTTPS:**
   ```bash
   grep APP_URL .env
   ```

2. **Check TrustProxies middleware:**
   ```bash
   grep -A 2 "trustProxies" bootstrap/app.php
   ```

3. **Check Nginx proxy headers:**
   ```bash
   sudo grep "X_FORWARDED_PROTO" /etc/nginx/sites-available/bmt_lucky_draw
   ```

### Vite Build Fails

1. **Fix vite permissions:**
   ```bash
   chmod +x node_modules/.bin/vite || sudo chmod +x node_modules/.bin/vite
   ```

2. **Use npx as fallback:**
   ```bash
   npx vite build
   ```

3. **Reinstall vite:**
   ```bash
   npm install vite --save-dev
   chmod +x node_modules/.bin/vite
   npm run build
   ```

## 📝 Penjelasan Teknis

### Mengapa Vite Build Diperlukan?

Vite menggunakan `APP_URL` dari `.env` saat **build time** (bukan runtime) untuk:
1. Generate asset paths di `public/build/manifest.json`
2. Set base URL untuk asset references
3. Create proper absolute URLs untuk assets

Jika `APP_URL` masih HTTP/IP saat build, manifest akan berisi paths yang salah.

### Mengapa Clear Cache Diperlukan?

Laravel cache config di `bootstrap/cache/config.php`. Setelah update `.env`, cache harus di-clear agar Laravel membaca nilai baru.

### Mengapa SESSION_SECURE_COOKIE?

Untuk HTTPS, cookies harus set dengan `Secure` flag agar browser tidak reject cookies. Ini penting untuk authentication.

## ✅ Checklist

Setelah fix, pastikan:

- [ ] `APP_URL=https://bmtnu.raf.my.id` di `.env`
- [ ] `ASSET_URL` commented atau tidak ada
- [ ] `SESSION_SECURE_COOKIE=true` di `.env`
- [ ] Laravel caches cleared dan rebuilt
- [ ] Vite assets rebuilt (`npm run build`)
- [ ] `manifest.json` exists dan berisi asset references
- [ ] Nginx config has proxy headers
- [ ] TrustProxies middleware enabled
- [ ] Test domain - assets load correctly
- [ ] Test IP - masih berfungsi
- [ ] Browser console - no errors

---

**Setelah fix, test akses ke https://bmtnu.raf.my.id dan pastikan:**
- ✅ Assets (CSS/JS) ter-load dengan benar
- ✅ Layout sesuai/tidak ada positioning issues
- ✅ Tidak ada mixed content errors di console
- ✅ Login berfungsi dengan benar

