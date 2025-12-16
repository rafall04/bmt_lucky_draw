# 🧪 Testing Setup Guide

## ⚠️ IMPORTANT: Database Setup Required

Karena SQLite extension tidak tersedia di sistem ini, tests menggunakan **MySQL** untuk testing.

---

## 📋 Setup Steps

### 1. Buat Test Database

Buat database khusus untuk testing:

```sql
CREATE DATABASE bmt_lucky_draw_test;
```

**Atau via command line:**
```bash
mysql -u root -p -e "CREATE DATABASE bmt_lucky_draw_test;"
```

### 2. Configure Database Connection

Pastikan MySQL credentials di `phpunit.xml` sesuai dengan setup Anda:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="bmt_lucky_draw_test"/>
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="3306"/>
<env name="DB_USERNAME" value="root"/>
<env name="DB_PASSWORD" value=""/>
```

**Update sesuai setup Anda:**
- `DB_HOST` - biasanya `127.0.0.1` atau `localhost`
- `DB_USERNAME` - biasanya `root`
- `DB_PASSWORD` - password MySQL Anda (kosong jika tidak ada)

### 3. Run Tests

```bash
composer test
# atau
php artisan test
```

---

## 🔄 Alternative: Enable SQLite (Recommended for Future)

Jika ingin menggunakan SQLite (lebih cepat untuk testing):

### Windows (XAMPP/PHP Standalone)

1. **Buka `php.ini`**
   - Lokasi: `C:\php\php.ini` (atau jalankan `php --ini` untuk mengetahui lokasi)
   - Jika menggunakan XAMPP: `C:\xampp\php\php.ini`

2. **Uncomment/Add extensions:**
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```

3. **Restart terminal/command prompt**

4. **Verify:**
   ```bash
   php -m | findstr /i "sqlite"
   ```

5. **Update `phpunit.xml` kembali ke SQLite:**
   ```xml
   <env name="DB_CONNECTION" value="sqlite"/>
   <env name="DB_DATABASE" value=":memory:"/>
   ```

---

## ✅ Verify Setup

### Check Database Connection

```bash
# Test MySQL connection
mysql -u root -p -e "SHOW DATABASES;"

# Check test database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'bmt_lucky_draw_test';"
```

### Check PHP Extensions

```bash
# Check MySQL extension
php -m | findstr /i "pdo_mysql mysql"

# Check SQLite (if installed)
php -m | findstr /i "sqlite"
```

---

## 🐛 Troubleshooting

### Error: "Access denied for user"

**Solution:** Update credentials di `phpunit.xml`:
```xml
<env name="DB_USERNAME" value="your_username"/>
<env name="DB_PASSWORD" value="your_password"/>
```

### Error: "Unknown database"

**Solution:** Buat database:
```sql
CREATE DATABASE bmt_lucky_draw_test;
```

### Error: "Connection refused"

**Solution:** 
1. Pastikan MySQL service running
2. Check host dan port di `phpunit.xml`

---

## 📊 Current Configuration

**Database:** MySQL (`bmt_lucky_draw_test`)  
**Reason:** SQLite extension tidak tersedia  
**Alternative:** Install SQLite extension untuk performa lebih cepat

---

**Last Updated:** 15 Desember 2025

