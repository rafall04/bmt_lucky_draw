# 🐛 Testing Troubleshooting Guide

## Error: "could not find driver (Connection: sqlite)"

### Masalah

Error ini terjadi karena PHP extension untuk SQLite tidak terinstall atau tidak aktif di sistem Anda.

```
PDOException: could not find driver
```

### Solusi

#### Solusi 1: Enable SQLite Extension (Recommended untuk Windows)

1. **Buka file `php.ini`**
   - Lokasi biasanya: `C:\php\php.ini` atau `C:\xampp\php\php.ini`
   - Atau jalankan: `php --ini` untuk mengetahui lokasi php.ini

2. **Uncomment baris berikut:**
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```

3. **Restart web server (jika menggunakan)**
   - Atau restart terminal/command prompt

4. **Verify extension terinstall:**
   ```bash
   php -m | findstr /i "pdo sqlite"
   ```

#### Solusi 2: Install SQLite Extension (Jika belum ada)

**Untuk XAMPP:**
- Extension biasanya sudah ada, hanya perlu di-enable

**Untuk PHP standalone:**
- Download PHP extension untuk SQLite
- Atau install melalui package manager

#### Solusi 3: Gunakan MySQL untuk Testing (Alternative)

Jika SQLite tidak bisa digunakan, kita bisa menggunakan MySQL untuk testing.

---

## ✅ Verifikasi Setup

### Check PHP Extensions

```bash
# Check PDO
php -m | findstr /i pdo

# Check SQLite
php -m | findstr /i sqlite

# Check semua extensions
php -m
```

**Expected output:**
```
pdo
pdo_sqlite
sqlite3
```

---

## 🔧 Alternative: Menggunakan MySQL untuk Testing

Jika SQLite tidak tersedia, kita bisa mengubah phpunit.xml untuk menggunakan MySQL.

### Langkah-langkah:

1. **Update `phpunit.xml`:**

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="bmt_lucky_draw_test"/>
    <!-- ... other configs ... -->
</php>
```

2. **Buat test database:**

```sql
CREATE DATABASE bmt_lucky_draw_test;
```

3. **Update `.env` untuk testing (opsional):**

```env
DB_CONNECTION=mysql
DB_DATABASE=bmt_lucky_draw_test
```

---

## 📋 Quick Fix Checklist

- [ ] Check PHP version: `php -v` (harus >= 8.2)
- [ ] Check php.ini location: `php --ini`
- [ ] Enable `extension=pdo_sqlite` di php.ini
- [ ] Enable `extension=sqlite3` di php.ini
- [ ] Restart terminal/command prompt
- [ ] Verify: `php -m | findstr /i sqlite`
- [ ] Run tests: `composer test`

---

## 🆘 Still Having Issues?

Jika masih mengalami masalah:

1. **Check PHP version compatibility**
2. **Check php.ini configuration**
3. **Try alternative: Use MySQL for testing**
4. **Check Laravel installation**

---

**Last Updated:** 15 Desember 2025

