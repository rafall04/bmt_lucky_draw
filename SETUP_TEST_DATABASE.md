# 🗄️ Setup Test Database - Quick Guide

## ⚠️ SQLite Extension Tidak Tersedia

Karena SQLite extension tidak terinstall, tests menggunakan **MySQL** sebagai alternatif.

---

## 🚀 Quick Setup (5 Menit)

### Step 1: Buat Test Database

**Opsi A: Via phpMyAdmin (Jika menggunakan XAMPP)**
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Klik "New" untuk membuat database baru
3. Nama database: `bmt_lucky_draw_test`
4. Collation: `utf8mb4_unicode_ci`
5. Klik "Create"

**Opsi B: Via Command Line**
```bash
# Masuk ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE bmt_lucky_draw_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit
exit;
```

**Opsi C: Via Laravel Tinker (Jika MySQL sudah running)**
```bash
php artisan tinker

DB::statement('CREATE DATABASE IF NOT EXISTS bmt_lucky_draw_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
```

### Step 2: Verify phpunit.xml Configuration

Pastikan `phpunit.xml` sudah dikonfigurasi dengan benar:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="bmt_lucky_draw_test"/>
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="3306"/>
<env name="DB_USERNAME" value="root"/>
<env name="DB_PASSWORD" value=""/>
```

**Update credentials sesuai setup Anda:**
- Jika MySQL password tidak kosong, update `DB_PASSWORD`
- Jika MySQL di port lain, update `DB_PORT`

### Step 3: Run Tests

```bash
composer test
```

---

## ✅ Verify Setup

### Check Database Created

```sql
SHOW DATABASES LIKE 'bmt_lucky_draw_test';
```

### Check MySQL Connection dari PHP

```bash
php artisan tinker

# Test connection
DB::connection()->getPdo();
```

Jika berhasil, akan return PDO instance tanpa error.

---

## 🔄 Alternative: Install SQLite Extension (Recommended)

Jika ingin menggunakan SQLite (lebih cepat), install extension:

### Windows XAMPP:

1. **Buka:** `C:\xampp\php\php.ini`

2. **Uncomment (hapus `;`):**
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```

3. **Restart XAMPP**

4. **Verify:**
   ```bash
   php -m | findstr /i "sqlite"
   ```

5. **Update `phpunit.xml` kembali:**
   ```xml
   <env name="DB_CONNECTION" value="sqlite"/>
   <env name="DB_DATABASE" value=":memory:"/>
   ```

Lihat `INSTALL_SQLITE.md` untuk detail lengkap.

---

## 📋 Quick Checklist

- [ ] MySQL service running
- [ ] Test database `bmt_lucky_draw_test` dibuat
- [ ] `phpunit.xml` sudah dikonfigurasi dengan benar
- [ ] Database credentials benar (username, password, host)
- [ ] Run `composer test`

---

## 🐛 Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"

**Fix:** Update `DB_PASSWORD` di `phpunit.xml`:
```xml
<env name="DB_PASSWORD" value="your_mysql_password"/>
```

### Error: "Unknown database 'bmt_lucky_draw_test'"

**Fix:** Buat database terlebih dahulu (lihat Step 1)

### Error: "Connection refused"

**Fix:** 
1. Pastikan MySQL service running
2. Check `DB_HOST` dan `DB_PORT` di `phpunit.xml`
3. Jika MySQL di port lain, update `DB_PORT`

---

**After setup, tests akan otomatis menggunakan MySQL untuk testing.**

