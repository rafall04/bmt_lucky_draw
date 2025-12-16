# 🚀 Panduan Akses Aplikasi BMT Lucky Draw

Setelah instalasi berhasil, berikut cara mengakses aplikasi:

## 📍 Cara Mengakses

### 1. Via Web Browser

Buka browser dan akses URL yang dikonfigurasi saat instalasi:

**Format URL:**
```
http://[DOMAIN_ATAU_IP]
```

**Contoh:**
- Jika domain: `http://bmtnu.raf.my.id`
- Jika IP: `http://172.17.2.5`
- Jika localhost: `http://localhost`

### 2. Halaman Login

Akses halaman login di:
```
http://[DOMAIN_ATAU_IP]/login
```

## 🔐 Default Credentials

Setelah instalasi pertama kali, gunakan credentials berikut:

- **Email**: `admin@bmt.local`
- **Password**: `password`

**⚠️ PENTING**: 
- **SEGERA ganti password** setelah login pertama kali!
- Jangan gunakan password default di production!

## 📋 Langkah Setelah Login

### 1. Ganti Password (WAJIB)

1. Login dengan credentials default
2. Klik menu profil atau settings
3. Ganti password ke yang lebih aman

### 2. Import Data Peserta

1. Di dashboard admin, klik **Import Peserta**
2. Upload file Excel dengan format:
   - Kolom: `no_rekening`, `nama`, `alamat`, `cabang`
   - Format file: `.xlsx` atau `.xls`
3. Klik **Import** dan tunggu proses selesai

### 3. Mulai Undian

1. Buka halaman utama: `http://[DOMAIN]/`
2. Klik tombol **START** untuk memulai rolling animation
3. Klik **STOP** untuk memilih pemenang
4. Pilih kategori hadiah dari dropdown
5. Klik **SIMPAN** untuk menyimpan pemenang

## 🔧 Troubleshooting

### Aplikasi Tidak Bisa Diakses

#### 1. Cek Web Server Status

```bash
# Jika menggunakan Nginx
sudo systemctl status nginx

# Jika menggunakan Apache
sudo systemctl status apache2
```

**Jika tidak running:**
```bash
# Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Apache
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### 2. Cek PHP-FPM Status

```bash
sudo systemctl status php8.3-fpm
```

**Jika tidak running:**
```bash
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm
```

#### 3. Cek Firewall

```bash
# Cek status firewall
sudo ufw status

# Jika firewall aktif, buka port 80 dan 443
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

#### 4. Cek Konfigurasi Web Server

**Nginx:**
```bash
# Test konfigurasi
sudo nginx -t

# Reload jika konfigurasi benar
sudo systemctl reload nginx
```

**Apache:**
```bash
# Test konfigurasi
sudo apache2ctl configtest

# Restart jika konfigurasi benar
sudo systemctl restart apache2
```

#### 5. Cek Log Error

```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# Apache error log
sudo tail -f /var/log/apache2/error.log

# Laravel log
tail -f /home/unnet/bmt_lucky_draw/storage/logs/laravel.log
```

### Error 500 Internal Server Error

1. **Cek permissions:**
   ```bash
   cd /home/unnet/bmt_lucky_draw
   sudo chmod -R 775 storage bootstrap/cache
   sudo chown -R www-data:www-data storage bootstrap/cache
   ```

2. **Cek .env file:**
   ```bash
   # Pastikan APP_KEY sudah di-generate
   grep APP_KEY .env
   
   # Jika kosong, generate:
   php artisan key:generate
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

### Error Database Connection

1. **Cek MySQL service:**
   ```bash
   sudo systemctl status mysql
   ```

2. **Cek credentials di .env:**
   ```bash
   cat .env | grep DB_
   ```

3. **Test koneksi:**
   ```bash
   php artisan tinker
   # Di dalam tinker:
   DB::connection()->getPdo();
   ```

### Tidak Bisa Login

1. **Pastikan seeder sudah dijalankan:**
   ```bash
   php artisan db:seed
   ```

2. **Cek user di database:**
   ```bash
   mysql -u bmt_user -p bmt_lucky_draw
   # Password: [password yang dibuat saat instalasi]
   
   SELECT email, role FROM users;
   ```

3. **Reset password manual (jika perlu):**
   ```bash
   php artisan tinker
   # Di dalam tinker:
   $user = App\Models\User::where('email', 'admin@bmt.local')->first();
   $user->password = Hash::make('password_baru');
   $user->save();
   ```

## 🌐 Konfigurasi Domain/IP

### Jika Menggunakan Domain

1. **Pastikan DNS sudah pointing ke server IP:**
   ```
   A Record: bmtnu.raf.my.id -> [SERVER_IP]
   ```

2. **Update APP_URL di .env:**
   ```bash
   nano /home/unnet/bmt_lucky_draw/.env
   ```
   
   Ubah:
   ```
   APP_URL=http://bmtnu.raf.my.id
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:cache
   ```

### Jika Menggunakan IP

1. **Update APP_URL di .env:**
   ```bash
   nano /home/unnet/bmt_lucky_draw/.env
   ```
   
   Ubah:
   ```
   APP_URL=http://172.17.2.5
   ```

2. **Clear config cache:**
   ```bash
   php artisan config:cache
   ```

## 🔒 Security Checklist

Setelah instalasi, pastikan:

- [ ] Password default sudah diganti
- [ ] `APP_DEBUG=false` di `.env` (untuk production)
- [ ] `APP_ENV=production` di `.env`
- [ ] Firewall sudah dikonfigurasi dengan benar
- [ ] SSL certificate sudah diinstall (jika menggunakan HTTPS)
- [ ] Backup database sudah dijadwalkan

## 📞 Informasi Penting

### Lokasi File

- **Project Directory**: `/home/unnet/bmt_lucky_draw`
- **Environment File**: `/home/unnet/bmt_lucky_draw/.env`
- **Log File**: `/home/unnet/bmt_lucky_draw/storage/logs/laravel.log`
- **Installation Log**: `/tmp/bmt_install_*.log`

### Perintah Berguna

```bash
# Restart web server
sudo systemctl restart nginx  # atau apache2

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Clear Laravel cache
cd /home/unnet/bmt_lucky_draw
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check aplikasi status
php artisan about
```

## ✅ Verifikasi Instalasi

Jalankan perintah berikut untuk memverifikasi instalasi:

```bash
cd /home/unnet/bmt_lucky_draw

# Check PHP version
php -v

# Check Composer
composer --version

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check routes
php artisan route:list
```

---

**Selamat! Aplikasi BMT Lucky Draw sudah siap digunakan! 🎉**

