# 🔧 Fix: Collision Error & Vite Permission

## 🚨 Masalah

1. **CollisionServiceProvider Error**: `Class "NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider" not found`
   - Cache file masih reference ke dev dependency yang tidak terinstall
   - Production install menggunakan `--no-dev`, jadi Collision tidak terinstall

2. **Vite Permission Denied**: `sh: 1: vite: Permission denied`
   - `node_modules/.bin/vite` tidak memiliki executable permission

## ✅ Solusi Manual

### Step 1: Remove Cache Files

```bash
cd /home/unnet/bmt_lucky_draw

# Remove cache files that reference dev dependencies
rm -rf bootstrap/cache/services.php
rm -rf bootstrap/cache/packages.php
rm -rf bootstrap/cache/*.php
```

### Step 2: Fix Vite Permission

```bash
# Fix vite permissions
chmod -R +x node_modules/.bin
# atau jika masih error:
sudo chmod -R +x node_modules/.bin
```

### Step 3: Rebuild Package Discovery

```bash
# Rebuild package discovery (will skip dev dependencies)
php artisan package:discover --ansi
```

### Step 4: Clear All Caches

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 5: Rebuild Caches (Optional, bisa skip jika masih error)

```bash
# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Rebuild Assets

```bash
# Build assets
npm run build
```

## 🎯 Quick Fix Script

Atau gunakan script yang sudah saya buat:

```bash
cd /home/unnet/bmt_lucky_draw

# Download atau copy FIX_CACHE_AND_VITE.sh
# Atau jalankan langsung:

bash << 'EOF'
cd /home/unnet/bmt_lucky_draw

# Remove cache files
rm -rf bootstrap/cache/*.php

# Fix vite permissions
chmod -R +x node_modules/.bin || sudo chmod -R +x node_modules/.bin

# Rebuild package discovery
php artisan package:discover --ansi

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Build assets
npm run build

echo "✅ Fix completed!"
EOF
```

## 📝 Penjelasan

**Collision Error:**
- Laravel cache file (`bootstrap/cache/services.php`) masih berisi reference ke `CollisionServiceProvider`
- Collision adalah dev dependency, tidak terinstall di production (`--no-dev`)
- Laravel mencoba load service provider yang tidak ada → Error

**Solusi:**
- Hapus cache files yang reference ke dev dependencies
- Rebuild package discovery (akan skip dev dependencies)
- Clear dan rebuild semua caches

**Vite Permission:**
- `node_modules/.bin/vite` tidak memiliki executable permission
- `chmod +x` untuk membuat executable

## ✅ Verifikasi

```bash
# Test artisan commands
php artisan --version

# Test config cache
php artisan config:cache

# Test build
npm run build

# Cek vite permission
ls -la node_modules/.bin/vite
# Harusnya ada 'x' (executable)
```

## ⚠️ Catatan

- **Production**: Jangan install dev dependencies (`--no-dev` flag)
- **Cache Management**: Selalu clear cache sebelum rebuild jika ada masalah
- **Package Discovery**: Rebuild setelah composer install untuk sync cache

---

**Setelah fix, semua artisan commands dan npm build harus berjalan tanpa error.**

