# 🔧 Fix: PHP Code Displayed as Plain Text

Jika Anda melihat kode PHP ditampilkan sebagai plain text di browser (seperti yang Anda alami), ini berarti PHP tidak diproses oleh web server.

## 🚨 Gejala

Browser menampilkan kode PHP mentah seperti:
```php
<?php
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
...
```

## ✅ Solusi Cepat

### 1. Cek PHP-FPM Status

```bash
sudo systemctl status php8.3-fpm
```

**Jika tidak running:**
```bash
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm
```

### 2. Cek PHP-FPM Socket

```bash
ls -la /var/run/php/php8.3-fpm.sock
```

**Jika socket tidak ada atau permission salah:**
```bash
sudo chown www-data:www-data /var/run/php/php8.3-fpm.sock
sudo chmod 666 /var/run/php/php8.3-fpm.sock
sudo systemctl restart php8.3-fpm
```

### 3. Cek Konfigurasi Web Server

#### Untuk Nginx:

```bash
# Cek konfigurasi
sudo nginx -t

# Cek apakah fastcgi_pass benar
sudo grep -A 5 "location ~ \.php" /etc/nginx/sites-available/bmt_lucky_draw
```

**Harus ada:**
```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

**Jika salah, perbaiki:**
```bash
sudo nano /etc/nginx/sites-available/bmt_lucky_draw
# Edit fastcgi_pass ke: unix:/var/run/php/php8.3-fpm.sock
sudo nginx -t
sudo systemctl reload nginx
```

#### Untuk Apache:

```bash
# Cek apakah mod_proxy_fcgi enabled
sudo a2enmod proxy_fcgi

# Cek konfigurasi
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### 4. Restart Semua Service

```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart web server
sudo systemctl restart nginx  # atau apache2
```

### 5. Cek Log Error

```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# PHP-FPM error log
sudo tail -f /var/log/php8.3-fpm.log

# Laravel log
tail -f /home/unnet/bmt_lucky_draw/storage/logs/laravel.log
```

## 🔍 Verifikasi Lengkap

Jalankan script verifikasi ini:

```bash
#!/bin/bash
echo "=== PHP-FPM Status ==="
sudo systemctl status php8.3-fpm --no-pager

echo -e "\n=== PHP-FPM Socket ==="
ls -la /var/run/php/php8.3-fpm.sock 2>/dev/null || echo "Socket tidak ditemukan!"

echo -e "\n=== Web Server Status ==="
if systemctl is-active --quiet nginx; then
    echo "Nginx: Running"
    sudo nginx -t
elif systemctl is-active --quiet apache2; then
    echo "Apache: Running"
    sudo apache2ctl configtest
else
    echo "Web server tidak running!"
fi

echo -e "\n=== PHP Version ==="
php -v

echo -e "\n=== Test PHP Processing ==="
echo "<?php phpinfo(); ?>" | sudo tee /home/unnet/bmt_lucky_draw/public/test.php
curl http://localhost/test.php | head -20
sudo rm /home/unnet/bmt_lucky_draw/public/test.php
```

## 🛠️ Perbaikan Manual (Jika Masih Error)

### 1. Reinstall PHP-FPM

```bash
sudo apt-get install --reinstall php8.3-fpm
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm
```

### 2. Recreate PHP-FPM Pool

```bash
# Backup config
sudo cp /etc/php/8.3/fpm/pool.d/www.conf /etc/php/8.3/fpm/pool.d/www.conf.bak

# Edit config
sudo nano /etc/php/8.3/fpm/pool.d/www.conf

# Pastikan:
# listen = /var/run/php/php8.3-fpm.sock
# listen.owner = www-data
# listen.group = www-data
# listen.mode = 0666

# Restart
sudo systemctl restart php8.3-fpm
```

### 3. Fix Permissions

```bash
cd /home/unnet/bmt_lucky_draw
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## ✅ Setelah Perbaikan

1. **Clear browser cache** (Ctrl+F5)
2. **Test akses** ke aplikasi
3. **Cek log** jika masih error

## 📝 Catatan

- Script installer sudah diperbaiki untuk:
  - Start PHP-FPM otomatis setelah instalasi PHP
  - Verify PHP-FPM socket dan permissions
  - Konfigurasi web server dengan PHP 8.3 yang benar
  - Enable required Apache modules

- Jika masih error setelah menjalankan script installer yang sudah diperbaiki, ikuti langkah-langkah di atas.

---

**Jika masih bermasalah, jalankan script verifikasi dan kirimkan output-nya untuk analisa lebih lanjut.**

