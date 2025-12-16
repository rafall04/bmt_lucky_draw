# 📦 Install SQLite Extension untuk PHP (Windows)

## Mengapa SQLite untuk Testing?

- ✅ Lebih cepat (in-memory database)
- ✅ Tidak perlu setup database terpisah
- ✅ Ideal untuk unit testing
- ✅ Zero configuration

---

## 🪟 Windows Installation Guide

### Opsi 1: XAMPP (Termudah)

**XAMPP biasanya sudah include SQLite, hanya perlu enable:**

1. **Buka `php.ini`**
   - Lokasi: `C:\xampp\php\php.ini`

2. **Cari dan uncomment (hapus `;` di depan):**
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```

3. **Restart XAMPP**

4. **Verify:**
   ```bash
   php -m | findstr /i "sqlite"
   ```

**Expected output:**
```
pdo_sqlite
sqlite3
```

---

### Opsi 2: PHP Standalone

**Jika menggunakan PHP standalone:**

1. **Download SQLite DLL**
   - Download dari: https://windows.php.net/downloads/pecl/releases/sqlite3/
   - Pilih versi yang sesuai dengan PHP Anda

2. **Extract DLL ke extension directory**
   - Biasanya: `C:\php\ext\`

3. **Edit `php.ini`**
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```

4. **Restart terminal**

---

### Opsi 3: Composer (Tidak Recommended)

SQLite biasanya sudah include di PHP 8.2+, hanya perlu enable extension.

---

## ✅ Verification

### Step 1: Check PHP Version

```bash
php -v
```

**Required:** PHP >= 8.2

### Step 2: Check Extensions

```bash
php -m | findstr /i "pdo sqlite"
```

**Expected:**
```
PDO
pdo_sqlite
sqlite3
```

### Step 3: Check php.ini Location

```bash
php --ini
```

### Step 4: Test SQLite Connection

```php
<?php
try {
    $db = new PDO('sqlite::memory:');
    echo "SQLite connection successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## 🔧 After Installation

Setelah SQLite terinstall, update `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Hapus konfigurasi MySQL dari `<php>` section.

---

## 📝 Notes

- SQLite extension biasanya sudah include di PHP modern
- Hanya perlu di-enable di `php.ini`
- Tidak perlu install software tambahan
- Lebih cepat untuk testing dibanding MySQL

---

**Last Updated:** 15 Desember 2025

