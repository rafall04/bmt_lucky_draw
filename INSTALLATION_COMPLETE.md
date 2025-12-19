# 🚀 Panduan Instalasi Lengkap - BMT Lucky Draw System

Panduan lengkap dari awal instalasi hingga akses Super Admin di Ubuntu 22.04 LTS.

---

## 📋 Daftar Isi

1. [Persiapan Sistem](#persiapan-sistem)
2. [Instalasi Otomatis (Recommended)](#instalasi-otomatis-recommended)
3. [Instalasi Manual](#instalasi-manual)
4. [Akses Super Admin](#akses-super-admin)
5. [Troubleshooting](#troubleshooting)

---

## 🖥️ Persiapan Sistem

### Requirements

- **OS**: Ubuntu 22.04 LTS (atau Ubuntu 20.04 LTS)
- **RAM**: Minimal 2GB (Recommended: 4GB)
- **Storage**: Minimal 10GB free space
- **Network**: Koneksi internet untuk download dependencies

### Akses Server

```bash
# SSH ke server Ubuntu Anda
ssh user@your-server-ip

# Atau jika menggunakan root
ssh root@your-server-ip
```

---

## 🎯 Instalasi Otomatis (Recommended)

### Step 1: Clone/Upload Project

**Opsi A: Clone dari GitHub**
```bash
cd /home/$(whoami)
git clone https://github.com/your-username/bmt_lucky_draw.git
cd bmt_lucky_draw
```

**Opsi B: Upload Project Files**
```bash
# Upload project ke server menggunakan SCP/SFTP
# Pastikan semua file sudah ada di: /home/your-username/bmt_lucky_draw
cd /home/your-username/bmt_lucky_draw
```

### Step 2: Buat Script Installer Executable

```bash
# Untuk Ubuntu 22.04
chmod +x install-ubuntu-22.04.sh

# Atau untuk Ubuntu 20.04
chmod +x install-ubuntu-20.04.sh
```

### Step 3: Jalankan Installer

**Untuk Ubuntu 22.04:**
```bash
sudo ./install-ubuntu-22.04.sh
```

**Untuk Ubuntu 20.04:**
```bash
sudo ./install-ubuntu-20.04.sh
```

### Step 4: Ikuti Prompts

Installer akan menanyakan beberapa informasi:

1. **Database Configuration:**
   - Database name: `bmt_lucky_draw` (atau sesuai keinginan)
   - Database username: `bmt_user` (atau sesuai keinginan)
   - Database password: (masukkan password yang aman)
   - Root password: (password MySQL root)

2. **Web Server Selection:**
   - Pilih `1` untuk Nginx (Recommended)
   - Atau pilih `2` untuk Apache

3. **Domain Configuration:**
   - Domain name: `bmtnu.raf.my.id` (atau domain Anda)
   - Server IP: `172.17.2.5` (atau IP server Anda)
   - Use HTTPS? `y` (ya) atau `n` (tidak)

4. **Environment Setup:**
   - APP_NAME: `BMT Lucky Draw` (atau sesuai keinginan)
   - APP_ENV: `production`
   - APP_DEBUG: `false`

### Step 5: Tunggu Instalasi Selesai

Installer akan:
- ✅ Update sistem
- ✅ Install PHP 8.3 dan extensions
- ✅ Install Composer
- ✅ Install MySQL
- ✅ Install Node.js & NPM
- ✅ Install Web Server (Nginx/Apache)
- ✅ Install dependencies (Composer & NPM)
- ✅ Setup database
- ✅ Configure web server
- ✅ Setup permissions
- ✅ Build frontend assets
- ✅ Run migrations & seeders

**Waktu estimasi**: 10-15 menit (tergantung kecepatan internet)

### Step 6: Verifikasi Instalasi

```bash
# Cek PHP version
php -v
# Should show: PHP 8.3.x

# Cek Composer
composer --version

# Cek Node.js
node -v

# Cek Web Server status
sudo systemctl status nginx
# atau
sudo systemctl status apache2

# Cek PHP-FPM status
sudo systemctl status php8.3-fpm
```

---

## 🛠️ Instalasi Manual

Jika Anda ingin melakukan instalasi manual tanpa script, ikuti langkah-langkah berikut:

### Step 1: Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### Step 2: Install PHP 8.3

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-intl
```

### Step 3: Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Step 4: Install MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Buat database dan user
sudo mysql -u root -p
```

```sql
CREATE DATABASE bmt_lucky_draw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bmt_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON bmt_lucky_draw.* TO 'bmt_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 5: Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Step 6: Install Web Server

**Nginx:**
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

**Apache:**
```bash
sudo apt install -y apache2
sudo a2enmod rewrite
sudo a2enmod proxy_fcgi
sudo a2enmod setenvif
sudo systemctl enable apache2
sudo systemctl start apache2
```

### Step 7: Setup Project

```bash
cd /home/your-username/bmt_lucky_draw

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install --unsafe-perm=true --allow-root
chmod -R +x node_modules/.bin

# Build assets
npm run build

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 8: Configure .env

Edit `.env` file:
```bash
nano .env
```

Update konfigurasi berikut:
```env
APP_NAME="BMT Lucky Draw"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://bmtnu.raf.my.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bmt_lucky_draw
DB_USERNAME=bmt_user
DB_PASSWORD=your_password

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=
```

### Step 9: Setup Database

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Step 10: Setup Permissions

```bash
sudo chmod -R 775 storage bootstrap/cache
php artisan storage:link
sudo chown -R www-data:www-data /home/your-username/bmt_lucky_draw
```

### Step 11: Configure Web Server

**Nginx Configuration** (`/etc/nginx/sites-available/bmt_lucky_draw`):

```nginx
server {
    listen 80;
    server_name bmtnu.raf.my.id 172.17.2.5;
    root /home/your-username/bmt_lucky_draw/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        
        fastcgi_param HTTP_X_FORWARDED_PROTO $http_x_forwarded_proto;
        fastcgi_param HTTP_X_FORWARDED_FOR $proxy_add_x_forwarded_for;
        fastcgi_param HTTP_X_FORWARDED_HOST $http_x_forwarded_host;
        fastcgi_param HTTP_X_REAL_IP $remote_addr;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/bmt_lucky_draw /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

**Apache Configuration** (`/etc/apache2/sites-available/bmt_lucky_draw.conf`):

```apache
<VirtualHost *:80>
    ServerName bmtnu.raf.my.id
    ServerAlias 172.17.2.5
    DocumentRoot /home/your-username/bmt_lucky_draw/public

    <Directory /home/your-username/bmt_lucky_draw/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/bmt_lucky_draw_error.log
    CustomLog ${APACHE_LOG_DIR}/bmt_lucky_draw_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite bmt_lucky_draw.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### Step 12: Setup SSL (Optional but Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx
# atau untuk Apache: sudo apt install -y certbot python3-certbot-apache

# Generate SSL certificate
sudo certbot --nginx -d bmtnu.raf.my.id
# atau untuk Apache: sudo certbot --apache -d bmtnu.raf.my.id
```

### Step 13: Clear & Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔐 Akses Super Admin

### Step 1: Akses Aplikasi

Buka browser dan akses URL aplikasi:

**Via Domain:**
```
https://bmtnu.raf.my.id/login
```

**Via IP:**
```
http://172.17.2.5/login
```

**Via Localhost:**
```
http://localhost/login
```

### Step 2: Login dengan Default Credentials

**Default Super Admin Credentials:**
- **Email**: `admin@bmt.local`
- **Password**: `password`

⚠️ **PENTING**: Segera ganti password setelah login pertama kali!

### Step 3: Ganti Password (WAJIB)

Setelah login berhasil:

1. Klik menu **Profile** di pojok kanan atas
2. Pilih **Change Password**
3. Masukkan password baru (minimal 8 karakter)
4. Konfirmasi password baru
5. Klik **Update Password**

### Step 4: Mulai Menggunakan Aplikasi

Setelah login, Anda akan diarahkan ke **Dashboard Admin** dengan menu:

- 🎯 **Dashboard** - Overview sistem
- 👥 **Peserta** - Kelola data peserta (Import/Export)
- 🎲 **Undian** - Jalankan undian berhadiah
- 🏆 **Pemenang** - Lihat daftar pemenang
- 👤 **Users** - Kelola user admin
- ⚙️ **Settings** - Konfigurasi aplikasi
- 📊 **Activity Logs** - Log aktivitas sistem
- 💾 **Backups** - Backup & restore database

---

## 🐛 Troubleshooting

### Problem: Aplikasi Tidak Bisa Diakses

**Solusi:**
```bash
# Cek status web server
sudo systemctl status nginx
# atau
sudo systemctl status apache2

# Cek status PHP-FPM
sudo systemctl status php8.3-fpm

# Cek firewall
sudo ufw status
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### Problem: Error 500 Internal Server Error

**Solusi:**
```bash
# Cek Laravel logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Problem: Database Connection Failed

**Solusi:**
```bash
# Test MySQL connection
mysql -u bmt_user -p -e "USE bmt_lucky_draw; SELECT 1;"

# Cek MySQL status
sudo systemctl status mysql

# Verify .env configuration
cat .env | grep DB_
```

### Problem: Assets (CSS/JS) Tidak Load

**Solusi:**
```bash
# Rebuild assets
npm run build

# Clear Laravel cache
php artisan config:clear
php artisan view:clear

# Verify APP_URL in .env
cat .env | grep APP_URL
```

### Problem: Storage Link Missing

**Solusi:**
```bash
# Remove existing symlink
rm -f public/storage

# Create new storage link
php artisan storage:link

# Fix permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

### Problem: Permission Denied

**Solusi:**
```bash
# Fix permissions
sudo chown -R www-data:www-data /home/your-username/bmt_lucky_draw
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R +x node_modules/.bin
```

### Problem: Vite Permission Denied

**Solusi:**
```bash
# Fix Vite permissions
chmod -R +x node_modules/.bin

# Rebuild assets
npm run build
```

---

## ✅ Checklist Post-Installation

Setelah instalasi berhasil, pastikan:

- [ ] Aplikasi bisa diakses via domain/IP
- [ ] Login dengan default credentials berhasil
- [ ] Password sudah diganti
- [ ] Assets (CSS/JS) loading dengan benar
- [ ] Database connection berjalan normal
- [ ] Storage link sudah dibuat
- [ ] Web server running normal
- [ ] PHP-FPM running normal
- [ ] SSL certificate sudah diinstall (jika menggunakan HTTPS)

---

## 📞 Informasi Penting

### Lokasi File Penting

- **Project Directory**: `/home/your-username/bmt_lucky_draw`
- **Environment File**: `.env`
- **Nginx Config**: `/etc/nginx/sites-available/bmt_lucky_draw`
- **Apache Config**: `/etc/apache2/sites-available/bmt_lucky_draw.conf`
- **Laravel Logs**: `storage/logs/laravel.log`
- **Nginx Logs**: `/var/log/nginx/error.log`
- **Apache Logs**: `/var/log/apache2/error.log`

### Perintah Berguna

```bash
# Restart web server
sudo systemctl restart nginx
# atau
sudo systemctl restart apache2

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Clear Laravel cache
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Check application status
php artisan about
php artisan route:list
```

---

## 🎉 Selesai!

Aplikasi BMT Lucky Draw System sudah siap digunakan!

Jika ada pertanyaan atau masalah, silakan cek dokumentasi lain:
- `INSTALLATION.md` - Panduan instalasi dasar
- `TESTING_GUIDE.md` - Panduan testing
- `CODE_STYLE.md` - Coding standards
- `CONTRIBUTING.md` - Panduan kontribusi

