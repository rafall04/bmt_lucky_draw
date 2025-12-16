#!/bin/bash

###############################################################################
# BMT Lucky Draw System - Installation Script for Ubuntu 20.04
# Anti-Gagal dengan Error Handling, Verification, dan Rollback
###############################################################################

set -euo pipefail  # Exit on error, undefined vars, pipe failures

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Log file
LOG_FILE="/tmp/bmt_install_$(date +%Y%m%d_%H%M%S).log"
INSTALL_DIR="/var/www/bmt_lucky_draw"
BACKUP_DIR="/tmp/bmt_backup_$(date +%Y%m%d_%H%M%S)"

# Track installed packages for rollback
INSTALLED_PACKAGES=()
INSTALLED_REPOS=()

###############################################################################
# Helper Functions
###############################################################################

log() {
    echo -e "${GREEN}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

step() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n" | tee -a "$LOG_FILE"
}

check_command() {
    if command -v "$1" &> /dev/null; then
        return 0
    else
        return 1
    fi
}

verify_installation() {
    local package=$1
    if check_command "$package"; then
        log "✓ $package installed successfully"
        return 0
    else
        error "✗ $package installation failed"
        return 1
    fi
}

rollback() {
    error "Installation failed! Starting rollback..."
    
    # Remove installed packages
    if [ ${#INSTALLED_PACKAGES[@]} -gt 0 ]; then
        warn "Removing installed packages..."
        sudo apt-get remove -y "${INSTALLED_PACKAGES[@]}" 2>/dev/null || true
    fi
    
    # Remove added repositories
    for repo in "${INSTALLED_REPOS[@]}"; do
        warn "Removing repository: $repo"
        sudo add-apt-repository --remove "$repo" -y 2>/dev/null || true
    done
    
    # Restore backup if exists
    if [ -d "$BACKUP_DIR" ]; then
        warn "Restoring from backup..."
        sudo cp -r "$BACKUP_DIR"/* "$INSTALL_DIR"/ 2>/dev/null || true
    fi
    
    error "Rollback completed. Check log: $LOG_FILE"
    exit 1
}

trap rollback ERR

###############################################################################
# Pre-Installation Checks
###############################################################################

pre_checks() {
    step "Pre-Installation Checks"
    
    # Check if running as root (we need sudo)
    if [ "$EUID" -eq 0 ]; then
        error "Please do not run as root. Script will use sudo when needed."
        exit 1
    fi
    
    # Check Ubuntu version
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [ "$ID" != "ubuntu" ] || [ "$VERSION_ID" != "20.04" ]; then
            warn "This script is designed for Ubuntu 20.04"
            warn "Detected: $ID $VERSION_ID"
            read -p "Continue anyway? (y/N): " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        fi
    fi
    
    # Check internet connection
    if ! ping -c 1 8.8.8.8 &> /dev/null; then
        error "No internet connection detected!"
        exit 1
    fi
    
    # Check disk space (need at least 2GB)
    AVAILABLE_SPACE=$(df / | tail -1 | awk '{print $4}')
    if [ "$AVAILABLE_SPACE" -lt 2097152 ]; then  # 2GB in KB
        error "Insufficient disk space. Need at least 2GB free."
        exit 1
    fi
    
    log "✓ All pre-checks passed"
}

###############################################################################
# System Update
###############################################################################

update_system() {
    step "Updating System Packages"
    
    log "Updating package lists..."
    sudo apt-get update -y | tee -a "$LOG_FILE" || {
        error "Failed to update package lists"
        exit 1
    }
    
    log "Upgrading system packages..."
    sudo apt-get upgrade -y | tee -a "$LOG_FILE" || {
        error "Failed to upgrade packages"
        exit 1
    }
    
    log "✓ System updated successfully"
}

###############################################################################
# Install PHP 8.3
###############################################################################

install_php() {
    step "Installing PHP 8.3 and Extensions"
    
    # Install software-properties-common if not installed (required for add-apt-repository)
    if ! dpkg -l | grep -q "^ii  software-properties-common "; then
        log "Installing software-properties-common..."
        sudo apt-get install -y software-properties-common | tee -a "$LOG_FILE" || {
            error "Failed to install software-properties-common"
            exit 1
        }
    fi
    
    # Check if PHP repository is already added
    if ! grep -q "^deb.*ondrej/php" /etc/apt/sources.list.d/*.list 2>/dev/null; then
        log "Adding PHP repository..."
        sudo add-apt-repository ppa:ondrej/php -y | tee -a "$LOG_FILE" || {
            error "Failed to add PHP repository"
            exit 1
        }
        INSTALLED_REPOS+=("ppa:ondrej/php")
        
        log "Updating package lists..."
        sudo apt-get update -y | tee -a "$LOG_FILE" || {
            error "Failed to update after adding PHP repo"
            exit 1
        }
    else
        log "PHP repository already added, updating package lists..."
        sudo apt-get update -y | tee -a "$LOG_FILE" || {
            error "Failed to update package lists"
            exit 1
        }
    fi
    
    # Check which PHP version is available
    PHP_VERSION=""
    
    # Use apt-cache policy which is more reliable than apt-cache show
    log "Checking available PHP versions in repository..."
    
    if apt-cache policy php8.3-cli 2>/dev/null | grep -q "Candidate:" && ! apt-cache policy php8.3-cli 2>/dev/null | grep -q "Candidate: (none)"; then
        PHP_VERSION="8.3"
        log "PHP 8.3 is available in repository"
    elif apt-cache policy php8.2-cli 2>/dev/null | grep -q "Candidate:" && ! apt-cache policy php8.2-cli 2>/dev/null | grep -q "Candidate: (none)"; then
        PHP_VERSION="8.2"
        warn "PHP 8.3 not available, using PHP 8.2 instead"
    elif apt-cache policy php8.1-cli 2>/dev/null | grep -q "Candidate:" && ! apt-cache policy php8.1-cli 2>/dev/null | grep -q "Candidate: (none)"; then
        PHP_VERSION="8.1"
        warn "PHP 8.3 not available, using PHP 8.1 instead"
    else
        # Try alternative method: search for available PHP packages
        log "Trying alternative method to detect PHP versions..."
        AVAILABLE_PHP=$(apt-cache search --names-only "^php[0-9]\.[0-9]-cli$" 2>/dev/null | grep -oP "php\K[0-9]\.[0-9]" | sort -V | tail -1)
        
        if [ -n "$AVAILABLE_PHP" ]; then
            PHP_VERSION="$AVAILABLE_PHP"
            log "Found PHP ${PHP_VERSION} using alternative detection method"
        else
            error "No suitable PHP version (8.1+) found in repository"
            error "Available PHP packages:"
            apt-cache search --names-only "^php[0-9]" 2>/dev/null | grep -E "^php[0-9]\.[0-9]-cli" | head -5 || true
            exit 1
        fi
    fi
    
    # Install PHP and extensions based on available version
    PHP_PACKAGES=(
        "php${PHP_VERSION}"
        "php${PHP_VERSION}-cli"
        "php${PHP_VERSION}-fpm"
        "php${PHP_VERSION}-common"
        "php${PHP_VERSION}-mysql"
        "php${PHP_VERSION}-zip"
        "php${PHP_VERSION}-gd"
        "php${PHP_VERSION}-mbstring"
        "php${PHP_VERSION}-curl"
        "php${PHP_VERSION}-xml"
        "php${PHP_VERSION}-bcmath"
        "php${PHP_VERSION}-intl"
        "php${PHP_VERSION}-readline"
        "php${PHP_VERSION}-tokenizer"
        "php${PHP_VERSION}-json"
        "php${PHP_VERSION}-opcache"
    )
    
    log "Installing PHP ${PHP_VERSION} packages..."
    
    # Verify packages exist before installing
    MISSING_PACKAGES=()
    for package in "${PHP_PACKAGES[@]}"; do
        # Use apt-cache policy which is more reliable
        if ! apt-cache policy "$package" 2>/dev/null | grep -q "Candidate:" || apt-cache policy "$package" 2>/dev/null | grep -q "Candidate: (none)"; then
            MISSING_PACKAGES+=("$package")
        fi
    done
    
    if [ ${#MISSING_PACKAGES[@]} -gt 0 ]; then
        error "The following packages are not available: ${MISSING_PACKAGES[*]}"
        error "Please check repository configuration"
        error "Trying to search for available PHP packages..."
        apt-cache search --names-only "^php${PHP_VERSION}" 2>/dev/null | head -10 || true
        exit 1
    fi
    
    # Install packages
    for package in "${PHP_PACKAGES[@]}"; do
        if ! dpkg -l | grep -q "^ii  $package "; then
            log "Installing $package..."
            sudo apt-get install -y "$package" | tee -a "$LOG_FILE" || {
                error "Failed to install $package"
                exit 1
            }
            INSTALLED_PACKAGES+=("$package")
        else
            log "✓ $package already installed"
        fi
    done
    
    # Verify PHP installation
    if ! verify_installation php; then
        exit 1
    fi
    
    # Verify PHP is accessible
    if command -v php &>/dev/null; then
        INSTALLED_PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -c 1-3)
        log "✓ PHP $INSTALLED_PHP_VERSION installed successfully"
        export PHP_VERSION_INSTALLED="$INSTALLED_PHP_VERSION"
    else
        error "PHP command not found after installation"
        exit 1
    fi
    
    # Verify required extensions
    REQUIRED_EXTENSIONS=("mysql" "zip" "gd" "mbstring" "curl" "xml" "bcmath" "intl")
    MISSING_EXTENSIONS=()
    
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -qi "^$ext$"; then
            MISSING_EXTENSIONS+=("$ext")
        fi
    done
    
    if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
        error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
        exit 1
    fi
    
    log "✓ All required PHP extensions installed"
    
    # Store PHP version for later use
    export PHP_VERSION_INSTALLED="$PHP_VERSION"
}

###############################################################################
# Install Composer
###############################################################################

install_composer() {
    step "Installing Composer"
    
    if check_command composer; then
        log "✓ Composer already installed: $(composer --version)"
        return 0
    fi
    
    log "Downloading Composer installer..."
    cd /tmp
    curl -sS https://getcomposer.org/installer -o composer-installer.php || {
        error "Failed to download Composer installer"
        exit 1
    }
    
    log "Installing Composer..."
    php composer-installer.php --install-dir=/usr/local/bin --filename=composer || {
        error "Failed to install Composer"
        exit 1
    }
    
    rm -f composer-installer.php
    
    # Verify installation
    if ! verify_installation composer; then
        exit 1
    fi
    
    log "✓ Composer installed: $(composer --version)"
}

###############################################################################
# Install MySQL
###############################################################################

install_mysql() {
    step "Installing MySQL"
    
    if check_command mysql; then
        log "✓ MySQL already installed"
        return 0
    fi
    
    log "Installing MySQL Server..."
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server | tee -a "$LOG_FILE" || {
        error "Failed to install MySQL"
        exit 1
    }
    INSTALLED_PACKAGES+=("mysql-server")
    
    # Start and enable MySQL
    sudo systemctl start mysql || true
    sudo systemctl enable mysql || true
    
    # Verify installation
    if ! verify_installation mysql; then
        exit 1
    fi
    
    log "✓ MySQL installed successfully"
    warn "⚠ Remember to run: sudo mysql_secure_installation"
}

###############################################################################
# Install Node.js
###############################################################################

install_nodejs() {
    step "Installing Node.js and NPM"
    
    if check_command node; then
        NODE_VERSION=$(node -v)
        log "✓ Node.js already installed: $NODE_VERSION"
        
        # Check if version is 18+ or 20+
        MAJOR_VERSION=$(echo "$NODE_VERSION" | cut -d'v' -f2 | cut -d'.' -f1)
        if [ "$MAJOR_VERSION" -lt 18 ]; then
            warn "Node.js version is too old. Upgrading..."
        else
            return 0
        fi
    fi
    
    log "Adding NodeSource repository..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - | tee -a "$LOG_FILE" || {
        error "Failed to add NodeSource repository"
        exit 1
    }
    
    log "Installing Node.js..."
    sudo apt-get install -y nodejs | tee -a "$LOG_FILE" || {
        error "Failed to install Node.js"
        exit 1
    }
    INSTALLED_PACKAGES+=("nodejs")
    
    # Verify installation
    if ! verify_installation node; then
        exit 1
    fi
    
    if ! verify_installation npm; then
        exit 1
    fi
    
    log "✓ Node.js $(node -v) and NPM $(npm -v) installed successfully"
}

###############################################################################
# Install Web Server
###############################################################################

install_webserver() {
    step "Installing Web Server"
    
    echo "Select web server:"
    echo "1) Nginx (Recommended)"
    echo "2) Apache"
    read -p "Choice [1-2]: " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[2]$ ]]; then
        install_apache
    else
        install_nginx
    fi
}

install_nginx() {
    log "Installing Nginx..."
    
    if check_command nginx; then
        log "✓ Nginx already installed"
        return 0
    fi
    
    sudo apt-get install -y nginx | tee -a "$LOG_FILE" || {
        error "Failed to install Nginx"
        exit 1
    }
    INSTALLED_PACKAGES+=("nginx")
    
    sudo systemctl start nginx || true
    sudo systemctl enable nginx || true
    
    if ! verify_installation nginx; then
        exit 1
    fi
    
    log "✓ Nginx installed and started"
}

install_apache() {
    log "Installing Apache..."
    
    if check_command apache2; then
        log "✓ Apache already installed"
        return 0
    fi
    
    sudo apt-get install -y apache2 | tee -a "$LOG_FILE" || {
        error "Failed to install Apache"
        exit 1
    }
    INSTALLED_PACKAGES+=("apache2")
    
    sudo a2enmod rewrite headers ssl || true
    sudo systemctl start apache2 || true
    sudo systemctl enable apache2 || true
    
    if ! verify_installation apache2; then
        exit 1
    fi
    
    log "✓ Apache installed and started"
}

###############################################################################
# Setup Project
###############################################################################

setup_project() {
    step "Setting Up Project"
    
    # Ask for project directory
    read -p "Project directory [$INSTALL_DIR]: " PROJECT_DIR
    PROJECT_DIR=${PROJECT_DIR:-$INSTALL_DIR}
    
    # Check if directory exists
    if [ -d "$PROJECT_DIR" ]; then
        warn "Directory $PROJECT_DIR already exists"
        read -p "Backup existing directory? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            log "Creating backup..."
            sudo cp -r "$PROJECT_DIR" "$BACKUP_DIR" || true
        fi
        
        read -p "Continue with existing directory? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    else
        log "Creating project directory..."
        sudo mkdir -p "$PROJECT_DIR" || {
            error "Failed to create directory"
            exit 1
        }
    fi
    
    # Check if project files exist
    if [ ! -f "$PROJECT_DIR/composer.json" ]; then
        warn "Project files not found in $PROJECT_DIR"
        echo "Please either:"
        echo "1) Clone the repository: git clone <repo-url> $PROJECT_DIR"
        echo "2) Upload project files to $PROJECT_DIR"
        read -p "Press Enter after project files are in place..."
        
        if [ ! -f "$PROJECT_DIR/composer.json" ]; then
            error "Project files still not found!"
            exit 1
        fi
    fi
    
    log "✓ Project directory ready: $PROJECT_DIR"
}

###############################################################################
# Install Dependencies
###############################################################################

install_dependencies() {
    step "Installing Project Dependencies"
    
    cd "$PROJECT_DIR" || {
        error "Failed to change to project directory"
        exit 1
    }
    
    # Install PHP dependencies
    log "Installing PHP dependencies with Composer..."
    if [ -f "composer.json" ]; then
        sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction 2>&1 | tee -a "$LOG_FILE" || {
            error "Failed to install PHP dependencies"
            exit 1
        }
        log "✓ PHP dependencies installed"
    else
        error "composer.json not found!"
        exit 1
    fi
    
    # Install NPM dependencies
    log "Installing NPM dependencies..."
    if [ -f "package.json" ]; then
        npm install --production 2>&1 | tee -a "$LOG_FILE" || {
            error "Failed to install NPM dependencies"
            exit 1
        }
        log "✓ NPM dependencies installed"
        
        log "Building assets..."
        npm run build 2>&1 | tee -a "$LOG_FILE" || {
            error "Failed to build assets"
            exit 1
        }
        log "✓ Assets built successfully"
    else
        warn "package.json not found, skipping NPM install"
    fi
}

###############################################################################
# Setup Environment
###############################################################################

setup_environment() {
    step "Setting Up Environment"
    
    cd "$PROJECT_DIR" || exit 1
    
    # Copy .env if not exists
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            log "Copying .env.example to .env..."
            cp .env.example .env || {
                error "Failed to copy .env.example"
                exit 1
            }
        else
            warn ".env.example not found, creating basic .env..."
            cat > .env <<EOF
APP_NAME="BMT Lucky Draw"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bmt_lucky_draw
DB_USERNAME=bmt_user
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120

LOG_CHANNEL=stack
LOG_LEVEL=error
EOF
        fi
    else
        log "✓ .env file already exists"
    fi
    
    # Generate APP_KEY if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        log "Generating application key..."
        php artisan key:generate --force 2>&1 | tee -a "$LOG_FILE" || {
            error "Failed to generate APP_KEY"
            exit 1
        }
    fi
    
    log "✓ Environment file configured"
    warn "⚠ Please edit .env file with your database credentials and settings"
}

###############################################################################
# Setup Database
###############################################################################

setup_database() {
    step "Setting Up Database"
    
    # Read database credentials from .env or ask user
    if [ -f "$PROJECT_DIR/.env" ]; then
        DB_NAME=$(grep "^DB_DATABASE=" "$PROJECT_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
        DB_USER=$(grep "^DB_USERNAME=" "$PROJECT_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
        DB_PASS=$(grep "^DB_PASSWORD=" "$PROJECT_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
    fi
    
    DB_NAME=${DB_NAME:-bmt_lucky_draw}
    DB_USER=${DB_USER:-bmt_user}
    
    if [ -z "$DB_PASS" ]; then
        read -sp "MySQL root password: " MYSQL_ROOT_PASS
        echo
        read -sp "Database password for $DB_USER: " DB_PASS
        echo
    else
        read -sp "MySQL root password: " MYSQL_ROOT_PASS
        echo
    fi
    
    log "Creating database and user..."
    
    # Create database and user
    mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF 2>&1 | tee -a "$LOG_FILE"
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    if [ ${PIPESTATUS[0]} -ne 0 ]; then
        error "Failed to create database"
        exit 1
    fi
    
    log "✓ Database and user created"
    
    # Update .env with password
    if [ -f "$PROJECT_DIR/.env" ] && [ -n "$DB_PASS" ]; then
        sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" "$PROJECT_DIR/.env"
    fi
    
    # Run migrations
    log "Running database migrations..."
    cd "$PROJECT_DIR" || exit 1
    php artisan migrate --force 2>&1 | tee -a "$LOG_FILE" || {
        error "Failed to run migrations"
        exit 1
    }
    
    log "✓ Database migrations completed"
}

###############################################################################
# Setup Permissions
###############################################################################

setup_permissions() {
    step "Setting Up Permissions"
    
    cd "$PROJECT_DIR" || exit 1
    
    log "Setting ownership to www-data..."
    sudo chown -R www-data:www-data "$PROJECT_DIR" || {
        error "Failed to set ownership"
        exit 1
    }
    
    log "Setting directory permissions..."
    sudo find "$PROJECT_DIR" -type d -exec chmod 755 {} \; || true
    
    log "Setting file permissions..."
    sudo find "$PROJECT_DIR" -type f -exec chmod 644 {} \; || true
    
    log "Setting storage permissions..."
    sudo chmod -R 775 storage bootstrap/cache || {
        error "Failed to set storage permissions"
        exit 1
    }
    
    log "Creating storage link..."
    php artisan storage:link 2>&1 | tee -a "$LOG_FILE" || true
    
    log "✓ Permissions configured"
}

###############################################################################
# Configure Web Server
###############################################################################

configure_webserver() {
    step "Configuring Web Server"
    
    if systemctl is-active --quiet nginx; then
        configure_nginx
    elif systemctl is-active --quiet apache2; then
        configure_apache
    else
        warn "No web server detected, skipping configuration"
    fi
}

configure_nginx() {
    log "Configuring Nginx..."
    
    read -p "Domain name (or IP): " DOMAIN
    DOMAIN=${DOMAIN:-localhost}
    
    # Detect PHP version if not set
    if [ -z "${PHP_VERSION_INSTALLED:-}" ]; then
        if command -v php &>/dev/null; then
            PHP_VERSION_INSTALLED=$(php -v | head -n 1 | cut -d " " -f 2 | cut -c 1-3)
        else
            # Try to detect from installed packages
            PHP_VERSION_INSTALLED=$(dpkg -l | grep -oP 'php\K[0-9]+\.[0-9]+' | head -n 1 | cut -c 1-3)
            if [ -z "$PHP_VERSION_INSTALLED" ]; then
                PHP_VERSION_INSTALLED="8.3"  # Default fallback
            fi
        fi
    fi
    
    NGINX_CONFIG="/etc/nginx/sites-available/bmt_lucky_draw"
    
    log "Creating Nginx configuration for PHP ${PHP_VERSION_INSTALLED}..."
    sudo tee "$NGINX_CONFIG" > /dev/null <<EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${PROJECT_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION_INSTALLED}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    # Enable site
    sudo ln -sf "$NGINX_CONFIG" /etc/nginx/sites-enabled/bmt_lucky_draw || true
    sudo rm -f /etc/nginx/sites-enabled/default || true
    
    # Test configuration
    sudo nginx -t 2>&1 | tee -a "$LOG_FILE" || {
        error "Nginx configuration test failed"
        exit 1
    }
    
    # Reload Nginx
    sudo systemctl reload nginx || {
        error "Failed to reload Nginx"
        exit 1
    }
    
    log "✓ Nginx configured for $DOMAIN"
}

configure_apache() {
    log "Configuring Apache..."
    
    read -p "Domain name (or IP): " DOMAIN
    DOMAIN=${DOMAIN:-localhost}
    
    APACHE_CONFIG="/etc/apache2/sites-available/bmt_lucky_draw.conf"
    
    log "Creating Apache configuration..."
    sudo tee "$APACHE_CONFIG" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${PROJECT_DIR}/public
    
    <Directory ${PROJECT_DIR}/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/bmt_lucky_draw_error.log
    CustomLog \${APACHE_LOG_DIR}/bmt_lucky_draw_access.log combined
</VirtualHost>
EOF
    
    # Enable site and modules
    sudo a2ensite bmt_lucky_draw.conf || true
    sudo a2dissite 000-default.conf || true
    sudo a2enmod rewrite || true
    
    # Test configuration
    sudo apache2ctl configtest 2>&1 | tee -a "$LOG_FILE" || {
        error "Apache configuration test failed"
        exit 1
    }
    
    # Restart Apache
    sudo systemctl restart apache2 || {
        error "Failed to restart Apache"
        exit 1
    }
    
    log "✓ Apache configured for $DOMAIN"
}

###############################################################################
# Final Verification
###############################################################################

final_verification() {
    step "Final Verification"
    
    # Check PHP
    if ! check_command php; then
        error "PHP not found!"
        return 1
    fi
    log "✓ PHP: $(php -v | head -n 1)"
    
    # Check Composer
    if ! check_command composer; then
        error "Composer not found!"
        return 1
    fi
    log "✓ Composer: $(composer --version)"
    
    # Check MySQL
    if ! check_command mysql; then
        error "MySQL not found!"
        return 1
    fi
    log "✓ MySQL installed"
    
    # Check Node.js
    if ! check_command node; then
        error "Node.js not found!"
        return 1
    fi
    log "✓ Node.js: $(node -v)"
    
    # Check Web Server
    if systemctl is-active --quiet nginx || systemctl is-active --quiet apache2; then
        log "✓ Web server running"
    else
        warn "Web server not running"
    fi
    
    # Check project files
    if [ -f "$PROJECT_DIR/composer.json" ] && [ -f "$PROJECT_DIR/.env" ]; then
        log "✓ Project files present"
    else
        error "Project files missing!"
        return 1
    fi
    
    # Test database connection
    cd "$PROJECT_DIR" || exit 1
    if php artisan tinker --execute="DB::connection()->getPdo();" &> /dev/null; then
        log "✓ Database connection successful"
    else
        warn "⚠ Database connection test failed - check .env configuration"
    fi
    
    log "✓ All verifications passed!"
}

###############################################################################
# Main Installation Flow
###############################################################################

main() {
    clear
    echo -e "${GREEN}"
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║  BMT Lucky Draw System - Ubuntu 20.04 Installation Script  ║"
    echo "║              Anti-Gagal dengan Error Handling             ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo -e "${NC}\n"
    
    log "Installation log: $LOG_FILE"
    log "Starting installation at $(date)"
    
    # Run installation steps
    pre_checks
    update_system
    install_php
    install_composer
    install_mysql
    install_nodejs
    install_webserver
    setup_project
    install_dependencies
    setup_environment
    setup_database
    setup_permissions
    configure_webserver
    final_verification
    
    # Success message
    step "Installation Complete!"
    
    echo -e "${GREEN}"
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║                    INSTALLATION SUCCESS!                    ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo -e "${NC}\n"
    
    log "Installation completed successfully at $(date)"
    
    echo -e "${YELLOW}Next Steps:${NC}"
    echo "1. Edit .env file: nano $PROJECT_DIR/.env"
    echo "2. Configure database credentials"
    echo "3. Set APP_URL to your domain"
    echo "4. Run: php artisan config:cache"
    echo "5. Access your application at: http://$DOMAIN"
    echo ""
    echo -e "${YELLOW}Important:${NC}"
    echo "- Review security settings in .env"
    echo "- Run: sudo mysql_secure_installation"
    echo "- Setup SSL certificate (recommended)"
    echo "- Check log file: $LOG_FILE"
    echo ""
}

# Run main function
main

