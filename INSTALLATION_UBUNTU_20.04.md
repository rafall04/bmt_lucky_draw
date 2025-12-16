# 🐧 Installation Guide - Ubuntu 20.04

Panduan lengkap untuk menginstall BMT Lucky Draw System di Ubuntu 20.04 LTS.

---

## 📋 System Requirements

### Minimum Requirements
- **OS:** Ubuntu 20.04 LTS atau lebih baru
- **RAM:** 2GB (4GB recommended)
- **Storage:** 5GB free space
- **CPU:** 2 cores (4 cores recommended)

### Software Requirements
- **PHP:** 8.2 atau 8.3
- **Composer:** 2.x
- **Node.js:** 18.x atau 20.x
- **NPM:** 9.x atau 10.x
- **MySQL:** 8.0 atau MariaDB 10.6+
- **Web Server:** Apache 2.4+ atau Nginx 1.18+

---

## 🚀 Step-by-Step Installation

### Step 1: Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### Step 2: Install PHP dan Extensions

```bash
# Install PHP 8.3 dan extensions yang diperlukan
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 dan extensions
sudo apt install -y \
    php8.3 \
    php8.3-cli \
    php8.3-fpm \
    php8.3-common \
    php8.3-mysql \
    php8.3-zip \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-xml \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-readline \
    php8.3-tokenizer \
    php8.3-json \
    php8.3-opcache

# Verify PHP installation
php -v
```

**Required PHP Extensions:**
- ✅ `php8.3-mysql` - Database connection
- ✅ `php8.3-zip` - Excel import/export
- ✅ `php8.3-gd` - Image processing
- ✅ `php8.3-mbstring` - Multibyte string support
- ✅ `php8.3-curl` - HTTP requests
- ✅ `php8.3-xml` - XML processing
- ✅ `php8.3-bcmath` - Arbitrary precision mathematics
- ✅ `php8.3-intl` - Internationalization
- ✅ `php8.3-opcache` - Performance optimization

### Step 3: Install Composer

```bash
# Download Composer installer
cd ~
curl -sS https://getcomposer.org/installer | php

# Move to global location
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version
```

### Step 4: Install MySQL

```bash
# Install MySQL Server
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Create database dan user
sudo mysql -u root -p <<EOF
CREATE DATABASE bmt_lucky_draw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bmt_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON bmt_lucky_draw.* TO 'bmt_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF
```

**⚠️ IMPORTANT:** Ganti `strong_password_here` dengan password yang kuat!

### Step 5: Install Node.js dan NPM

```bash
# Install Node.js 20.x
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verify installation
node -v
npm -v
```

### Step 6: Install Web Server

#### Opsi A: Apache

```bash
# Install Apache
sudo apt install -y apache2

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl

# Restart Apache
sudo systemctl restart apache2
```

#### Opsi B: Nginx (Recommended)

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### Step 7: Clone/Upload Project

```bash
# Jika menggunakan Git
cd /var/www
sudo git clone <repository-url> bmt_lucky_draw
cd bmt_lucky_draw

# Atau upload project files ke /var/www/bmt_lucky_draw
```

### Step 8: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install NPM dependencies
npm install

# Build assets
npm run build
```

### Step 9: Setup Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file
nano .env
```

**Konfigurasi `.env` yang penting:**

```env
APP_NAME="BMT Lucky Draw"
APP_ENV=production
APP_KEY=base64:... (auto-generated)
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bmt_lucky_draw
DB_USERNAME=bmt_user
DB_PASSWORD=strong_password_here

SESSION_DRIVER=database
SESSION_LIFETIME=120

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Step 10: Setup Database

```bash
# Run migrations
php artisan migrate

# (Optional) Seed database dengan data awal
php artisan db:seed
```

### Step 11: Setup Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/bmt_lucky_draw

# Set permissions
sudo chmod -R 755 /var/www/bmt_lucky_draw
sudo chmod -R 775 /var/www/bmt_lucky_draw/storage
sudo chmod -R 775 /var/www/bmt_lucky_draw/bootstrap/cache
```

### Step 12: Configure Web Server

#### Apache Configuration

```bash
# Create virtual host
sudo nano /etc/apache2/sites-available/bmt_lucky_draw.conf
```

**Isi file:**

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    
    DocumentRoot /var/www/bmt_lucky_draw/public
    
    <Directory /var/www/bmt_lucky_draw/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/bmt_lucky_draw_error.log
    CustomLog ${APACHE_LOG_DIR}/bmt_lucky_draw_access.log combined
</VirtualHost>
```

```bash
# Enable site
sudo a2ensite bmt_lucky_draw.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

#### Nginx Configuration

```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/bmt_lucky_draw
```

**Isi file:**

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/bmt_lucky_draw/public;

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
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/bmt_lucky_draw /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 13: Setup SSL (Optional but Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx
# atau untuk Apache:
# sudo apt install -y certbot python3-certbot-apache

# Generate SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
# atau untuk Apache:
# sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

### Step 14: Setup Queue Worker (Optional)

Jika menggunakan queue untuk background jobs:

```bash
# Create systemd service
sudo nano /etc/systemd/system/bmt-queue.service
```

**Isi file:**

```ini
[Unit]
Description=BMT Lucky Draw Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/bmt_lucky_draw/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl enable bmt-queue
sudo systemctl start bmt-queue
```

### Step 15: Setup Cron Jobs

```bash
# Edit crontab
sudo crontab -e -u www-data
```

**Tambahkan:**

```cron
* * * * * cd /var/www/bmt_lucky_draw && php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Verification

### Check PHP Extensions

```bash
php -m | grep -E "mysql|zip|gd|mbstring|curl|xml|bcmath|intl"
```

### Check Database Connection

```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Check Application

```bash
# Test routes
php artisan route:list

# Check configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Access Application

Buka browser dan akses:
- `http://your-server-ip` atau
- `https://your-domain.com`

---

## 🔧 Troubleshooting

### Issue: Permission Denied

```bash
sudo chown -R www-data:www-data /var/www/bmt_lucky_draw
sudo chmod -R 755 /var/www/bmt_lucky_draw
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.3-fpm.log

# Check web server logs
sudo tail -f /var/log/nginx/error.log
# atau
sudo tail -f /var/log/apache2/error.log
```

### Issue: Database Connection Failed

```bash
# Test MySQL connection
mysql -u bmt_user -p bmt_lucky_draw

# Check MySQL status
sudo systemctl status mysql

# Verify credentials in .env
php artisan tinker
>>> config('database.connections.mysql')
```

### Issue: Assets Not Loading

```bash
# Rebuild assets
npm run build

# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### Issue: Storage Link Missing

```bash
php artisan storage:link
```

---

## 📦 Quick Install Script

Untuk instalasi cepat, gunakan script berikut:

```bash
#!/bin/bash
# Save as install.sh

set -e

echo "🚀 Installing BMT Lucky Draw System on Ubuntu 20.04..."

# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-zip \
    php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath \
    php8.3-intl php8.3-opcache

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install -y mysql-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

echo "✅ Basic installation complete!"
echo "📝 Next steps:"
echo "   1. Create database and user"
echo "   2. Clone/upload project files"
echo "   3. Run: composer install"
echo "   4. Run: npm install && npm run build"
echo "   5. Configure .env file"
echo "   6. Run: php artisan migrate"
echo "   7. Configure web server"
```

---

## 📚 Additional Resources

- [Laravel Installation Guide](https://laravel.com/docs/11.x/installation)
- [Ubuntu 20.04 Documentation](https://help.ubuntu.com/20.04/)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [Apache Configuration](https://httpd.apache.org/docs/)

---

## 🔒 Security Checklist

Setelah instalasi, pastikan:

- [ ] `APP_DEBUG=false` di production
- [ ] `APP_ENV=production` di production
- [ ] Strong database password
- [ ] SSL/HTTPS enabled
- [ ] Firewall configured (UFW)
- [ ] Regular backups setup
- [ ] File permissions correct
- [ ] `.env` file tidak accessible via web

---

**Last Updated:** Desember 2025  
**Tested on:** Ubuntu 20.04 LTS

