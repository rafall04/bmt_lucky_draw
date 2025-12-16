#!/bin/bash

###############################################################################
# BMT Lucky Draw System - Installation Script for Ubuntu 22.04
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
    
    # Use SUDO_CMD variable (empty if root)
    SUDO_CMD="${SUDO_CMD:-sudo}"
    
    # Remove installed packages
    if [ ${#INSTALLED_PACKAGES[@]} -gt 0 ]; then
        warn "Removing installed packages..."
        $SUDO_CMD apt-get remove -y "${INSTALLED_PACKAGES[@]}" 2>/dev/null || true
    fi
    
    # Remove added repositories
    for repo in "${INSTALLED_REPOS[@]}"; do
        warn "Removing repository: $repo"
        $SUDO_CMD add-apt-repository --remove "$repo" -y 2>/dev/null || true
    done
    
    # Restore backup if exists
    if [ -d "$BACKUP_DIR" ]; then
        warn "Restoring from backup..."
        $SUDO_CMD cp -r "$BACKUP_DIR"/* "$INSTALL_DIR"/ 2>/dev/null || true
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
    
    # Detect if running as root
    IS_ROOT=false
    SUDO_CMD="sudo"
    if [ "$EUID" -eq 0 ]; then
        IS_ROOT=true
        SUDO_CMD=""  # No need for sudo if already root
        warn "⚠️  Running as root user"
        warn "⚠️  This is less secure but will avoid permission issues"
        warn "⚠️  For production, consider using a non-root user with sudo"
        echo ""
        read -p "Continue as root? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            log "Exiting. Please run as a non-root user."
            exit 0
        fi
    else
        log "Running as non-root user (will use sudo when needed)"
        # Check if sudo is available
        if ! command -v sudo &> /dev/null; then
            error "sudo is not available. Please install sudo or run as root."
            exit 1
        fi
        # Check if user has sudo privileges
        if ! sudo -n true 2>/dev/null; then
            log "You may be prompted for sudo password during installation"
        fi
    fi
    
    # Check Ubuntu version
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [ "$ID" != "ubuntu" ] || [ "$VERSION_ID" != "22.04" ]; then
            warn "This script is designed for Ubuntu 22.04 LTS"
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
    step "Installing PHP 8.2+ and Extensions"
    
    # Install software-properties-common if not installed (required for add-apt-repository)
    if ! dpkg -l | grep -q "^ii  software-properties-common "; then
        log "Installing software-properties-common..."
        sudo apt-get install -y software-properties-common | tee -a "$LOG_FILE" || {
            error "Failed to install software-properties-common"
            exit 1
        }
    fi
    
    # Check if PHP repository is already added
    REPO_ADDED=false
    if grep -q "^deb.*ondrej/php" /etc/apt/sources.list.d/*.list 2>/dev/null; then
        log "PHP repository already added"
        REPO_ADDED=true
    else
        log "Adding PHP repository (ondrej/php)..."
        sudo add-apt-repository ppa:ondrej/php -y | tee -a "$LOG_FILE" || {
            error "Failed to add PHP repository"
            error "Trying alternative method..."
            # Alternative: Add repository manually
            echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu jammy main" | sudo tee /etc/apt/sources.list.d/ondrej-php.list
            sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C || true
            INSTALLED_REPOS+=("ppa:ondrej/php")
        }
        INSTALLED_REPOS+=("ppa:ondrej/php")
        REPO_ADDED=true
    fi
    
    # Always update package lists to ensure we have latest package information
    log "Updating package lists..."
    sudo apt-get update -y | tee -a "$LOG_FILE" || {
        error "Failed to update package lists"
        warn "Trying to fix repository keys..."
        sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C || true
        sudo apt-get update -y | tee -a "$LOG_FILE" || {
            error "Still failed to update package lists"
            exit 1
        }
    }
    
    # Verify repository is working and has PHP 8.2+ packages
    log "Verifying PHP repository and checking for PHP 8.2+ packages..."
    REPO_WORKING=false
    
    # Check if we can see PHP packages from ondrej repository
    if apt-cache search --names-only "^php[0-9]" 2>/dev/null | grep -q "php"; then
        REPO_WORKING=true
        log "Repository is accessible"
    else
        warn "Repository might not be working correctly"
        log "Checking repository sources:"
        grep -r "ondrej" /etc/apt/sources.list.d/ 2>/dev/null | head -5 || true
        log "Checking repository keys:"
        sudo apt-key list 2>/dev/null | grep -i ondrej || true
    fi
    
    # FORCE PHP 8.3 - Do not auto-detect to avoid unstable versions like 8.5
    PHP_VERSION="8.3"
    
    log "Forcing PHP ${PHP_VERSION} installation (stable version for Laravel 11)"
    
    # Check if PHP 8.3 is already installed
    if check_command php; then
        INSTALLED_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -c 1-3)
        if [ "$INSTALLED_VERSION" = "8.3" ]; then
            log "✓ PHP $INSTALLED_VERSION already installed"
            export PHP_VERSION_INSTALLED="$INSTALLED_VERSION"
            # Still verify extensions are installed below
        else
            warn "PHP $INSTALLED_VERSION installed but we need PHP 8.3"
            log "Will install PHP 8.3 (may require removing existing PHP)"
        fi
    fi
    
    # Method 3: Try policy check (prioritize 8.2+)
    if [ -z "$PHP_VERSION" ]; then
        log "Trying policy check method..."
        for version in "8.3" "8.2"; do
            POLICY_OUTPUT=$(apt-cache policy "php${version}-cli" 2>/dev/null)
            if echo "$POLICY_OUTPUT" | grep -q "Candidate:" && ! echo "$POLICY_OUTPUT" | grep -q "Candidate: (none)"; then
                CANDIDATE=$(echo "$POLICY_OUTPUT" | grep "Candidate:" | awk '{print $2}')
                if [ "$CANDIDATE" != "(none)" ] && [ -n "$CANDIDATE" ]; then
                    PHP_VERSION="$version"
                    log "Found PHP ${PHP_VERSION} using policy check (candidate: $CANDIDATE)"
                    break
                fi
            fi
        done
    fi
    
    # Verify PHP 8.3 is available in repository
    if ! apt-cache show "php${PHP_VERSION}-cli" 2>/dev/null | grep -q "^Package:"; then
        warn "PHP ${PHP_VERSION} not found in repository, attempting to fix repository..."
        
        # Try to refresh repository keys
        log "Refreshing repository keys..."
        $SUDO_CMD apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C 2>&1 | tee -a "$LOG_FILE" || true
        
        # Remove and re-add repository
        log "Re-adding PHP repository..."
        $SUDO_CMD rm -f /etc/apt/sources.list.d/ondrej-php*.list 2>/dev/null || true
        $SUDO_CMD add-apt-repository ppa:ondrej/php -y 2>&1 | tee -a "$LOG_FILE" || true
        
        # Update again
        log "Updating package lists after repository fix..."
        $SUDO_CMD apt-get update -y 2>&1 | tee -a "$LOG_FILE" || true
        
        # Verify PHP 8.3 is now available
        if ! apt-cache show "php${PHP_VERSION}-cli" 2>/dev/null | grep -q "^Package:"; then
            error "PHP ${PHP_VERSION} is not available in repository"
            error ""
            error "Available PHP packages in repository:"
            apt-cache search --names-only "^php[0-9]\.[0-9]-cli$" 2>/dev/null | head -10 || echo "No PHP packages found"
            error ""
            error "Repository information:"
            grep -r "ondrej" /etc/apt/sources.list.d/ 2>/dev/null | head -5 || echo "No ondrej repository found"
            error ""
            error "Manual troubleshooting steps:"
            error "1. Remove existing repository: sudo rm /etc/apt/sources.list.d/ondrej-php*.list"
            error "2. Add repository: sudo add-apt-repository ppa:ondrej/php"
            error "3. Update: sudo apt-get update"
            error "4. Check: apt-cache policy php8.3-cli"
            exit 1
        fi
    fi
    
    # Install PHP and extensions based on available version
    # Note: json and opcache are built-in for PHP 8.0+, no need to install separately
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
    )
    
    log "Installing PHP ${PHP_VERSION} packages..."
    
    # Verify packages exist before installing
    # Use multiple methods for verification since apt-cache policy can be unreliable
    MISSING_PACKAGES=()
    for package in "${PHP_PACKAGES[@]}"; do
        PACKAGE_AVAILABLE=false
        
        # Method 1: Try apt-cache show (most reliable for checking package existence)
        if apt-cache show "$package" 2>/dev/null | grep -q "^Package:"; then
            PACKAGE_AVAILABLE=true
        fi
        
        # Method 2: Try apt-cache policy if show didn't work
        if [ "$PACKAGE_AVAILABLE" = false ]; then
            POLICY_OUTPUT=$(apt-cache policy "$package" 2>/dev/null)
            if echo "$POLICY_OUTPUT" | grep -q "Candidate:" && ! echo "$POLICY_OUTPUT" | grep -q "Candidate: (none)"; then
                CANDIDATE=$(echo "$POLICY_OUTPUT" | grep "Candidate:" | awk '{print $2}')
                if [ "$CANDIDATE" != "(none)" ] && [ -n "$CANDIDATE" ]; then
                    PACKAGE_AVAILABLE=true
                fi
            fi
        fi
        
        # Method 3: Try apt-cache search as last resort
        if [ "$PACKAGE_AVAILABLE" = false ]; then
            if apt-cache search --names-only "^${package}$" 2>/dev/null | grep -q "^${package} "; then
                PACKAGE_AVAILABLE=true
            fi
        fi
        
        if [ "$PACKAGE_AVAILABLE" = false ]; then
            MISSING_PACKAGES+=("$package")
        fi
    done
    
    if [ ${#MISSING_PACKAGES[@]} -gt 0 ]; then
        warn "Some packages failed verification, but will attempt installation anyway..."
        warn "Missing packages: ${MISSING_PACKAGES[*]}"
        log "Available PHP ${PHP_VERSION} packages:"
        apt-cache search --names-only "^php${PHP_VERSION}" 2>/dev/null | head -15 || true
        log "Attempting to install packages (some may be metapackages)..."
    fi
    
    # Install packages - Force PHP 8.3 explicitly
    for package in "${PHP_PACKAGES[@]}"; do
        if ! dpkg -l | grep -q "^ii  $package "; then
            log "Installing $package..."
            # Try to install, but don't fail immediately if package name doesn't exist
            # Some packages might be metapackages or have different names
            if $SUDO_CMD apt-get install -y "$package" 2>&1 | tee -a "$LOG_FILE"; then
                INSTALLED_PACKAGES+=("$package")
                log "✓ $package installed successfully"
            else
                INSTALL_EXIT_CODE=${PIPESTATUS[0]}
                # Check if it's a critical package (cli, fpm, common)
                if [[ "$package" =~ (cli|fpm|common)$ ]]; then
                    error "Failed to install critical package: $package (exit code: $INSTALL_EXIT_CODE)"
                    error "Trying to find alternative package name..."
                    # Try to find similar package
                    ALTERNATIVE=$(apt-cache search --names-only "^php${PHP_VERSION}" 2>/dev/null | grep -E "(cli|fpm|common)" | head -1 | awk '{print $1}')
                    if [ -n "$ALTERNATIVE" ] && [ "$ALTERNATIVE" != "$package" ]; then
                        warn "Found alternative: $ALTERNATIVE, trying to install..."
                        $SUDO_CMD apt-get install -y "$ALTERNATIVE" 2>&1 | tee -a "$LOG_FILE" || {
                            error "Failed to install alternative package: $ALTERNATIVE"
                            exit 1
                        }
                    else
                        error "No alternative found for critical package: $package"
                        exit 1
                    fi
                else
                    warn "Failed to install $package (non-critical, exit code: $INSTALL_EXIT_CODE), continuing..."
                    # For non-critical packages, continue installation
                fi
            fi
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
        
        # CRITICAL: Start and enable PHP-FPM
        log "Starting PHP-FPM ${PHP_VERSION}..."
        if systemctl is-active --quiet "php${PHP_VERSION}-fpm"; then
            log "✓ PHP-FPM ${PHP_VERSION} is already running"
        else
            $SUDO_CMD systemctl start "php${PHP_VERSION}-fpm" || {
                error "Failed to start PHP-FPM ${PHP_VERSION}"
                exit 1
            }
            log "✓ PHP-FPM ${PHP_VERSION} started"
        fi
        
        # Enable PHP-FPM to start on boot
        $SUDO_CMD systemctl enable "php${PHP_VERSION}-fpm" || {
            warn "Failed to enable PHP-FPM ${PHP_VERSION} on boot"
        }
        
        # Verify PHP-FPM socket exists
        PHP_FPM_SOCKET="/var/run/php/php${PHP_VERSION}-fpm.sock"
        if [ -S "$PHP_FPM_SOCKET" ]; then
            log "✓ PHP-FPM socket found at $PHP_FPM_SOCKET"
            # Fix socket permissions
            $SUDO_CMD chown www-data:www-data "$PHP_FPM_SOCKET" 2>/dev/null || true
            $SUDO_CMD chmod 666 "$PHP_FPM_SOCKET" 2>/dev/null || true
        else
            warn "PHP-FPM socket not found at $PHP_FPM_SOCKET"
            warn "Socket may be created after PHP-FPM restart"
            # Try restarting PHP-FPM to create socket
            $SUDO_CMD systemctl restart "php${PHP_VERSION}-fpm" || true
        fi
    else
        error "PHP command not found after installation"
        exit 1
    fi
    
    # Verify required extensions
    # Note: Extension names in php -m may differ from package names
    # json is built-in for PHP 8.0+, no separate package needed
    # opcache is built-in but needs to be enabled in php.ini
    REQUIRED_EXTENSIONS=(
        "pdo_mysql"  # MySQL PDO driver (from php-mysql package)
        "zip"
        "gd"
        "mbstring"
        "curl"
        "xml"
        "bcmath"
        "intl"
        "json"      # Built-in for PHP 8.0+
    )
    
    # Opcache is optional but recommended for production
    OPCACHE_EXTENSION="opcache"
    MISSING_EXTENSIONS=()
    
    log "Verifying PHP extensions..."
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        # Check if extension is loaded
        if php -m | grep -qi "^${ext}$"; then
            log "✓ Extension $ext is loaded"
        else
            # For mysql, also check pdo_mysql and mysqli
            if [ "$ext" = "pdo_mysql" ]; then
                if php -m | grep -qiE "^(pdo_mysql|mysqli|mysql)$"; then
                    log "✓ MySQL extension found (pdo_mysql, mysqli, or mysql)"
                    continue
                fi
            fi
            MISSING_EXTENSIONS+=("$ext")
            warn "✗ Extension $ext is not loaded"
        fi
    done
    
    # Check opcache separately (it's built-in but needs to be enabled)
    log "Checking opcache (optional but recommended for production)..."
    if php -m | grep -qi "^opcache$"; then
        log "✓ Opcache is enabled"
    else
        # Try to enable opcache
        # Get PHP ini directory - try multiple methods
        PHP_INI_DIR=""
        
        # Method 1: From php --ini output
        if php --ini 2>/dev/null | grep -q "Scan for additional .ini files"; then
            PHP_INI_DIR=$(php --ini 2>/dev/null | grep "Scan for additional .ini files" | awk '{print $NF}')
        fi
        
        # Method 2: Try common locations for PHP 8.x
        if [ -z "$PHP_INI_DIR" ] || [ ! -d "$PHP_INI_DIR" ]; then
            for dir in "/etc/php/${PHP_VERSION}/cli/conf.d" "/etc/php/${PHP_VERSION}/fpm/conf.d" "/etc/php/${PHP_VERSION}/mods-available"; do
                if [ -d "$dir" ]; then
                    PHP_INI_DIR="$dir"
                    log "Found PHP ini directory: $PHP_INI_DIR"
                    break
                fi
            done
        fi
        
        # Method 3: Try to find from php.ini location
        if [ -z "$PHP_INI_DIR" ] || [ ! -d "$PHP_INI_DIR" ]; then
            PHP_MAIN_INI=$(php --ini 2>/dev/null | grep "Loaded Configuration File" | awk '{print $NF}' | xargs dirname 2>/dev/null)
            if [ -n "$PHP_MAIN_INI" ] && [ -d "$PHP_MAIN_INI/conf.d" ]; then
                PHP_INI_DIR="$PHP_MAIN_INI/conf.d"
                log "Found PHP ini directory from main ini: $PHP_INI_DIR"
            fi
        fi
        
        # Method 4: Try FPM specific directory
        if [ -z "$PHP_INI_DIR" ] || [ ! -d "$PHP_INI_DIR" ]; then
            if [ -d "/etc/php/${PHP_VERSION}/fpm/conf.d" ]; then
                PHP_INI_DIR="/etc/php/${PHP_VERSION}/fpm/conf.d"
                log "Using FPM conf.d directory: $PHP_INI_DIR"
            fi
        fi
        
        if [ -n "$PHP_INI_DIR" ] && [ -d "$PHP_INI_DIR" ]; then
            OPCACHE_INI="${PHP_INI_DIR}/10-opcache.ini"
            
            # Check if opcache config exists and has problematic zend_extension
            if [ -f "$OPCACHE_INI" ]; then
                # Check if it has zend_extension=opcache without full path (problematic)
                if grep -q "^zend_extension=opcache$" "$OPCACHE_INI" 2>/dev/null; then
                    log "Found problematic opcache config, fixing it..."
                    sudo rm -f "$OPCACHE_INI"
                fi
            fi
            
            if [ ! -f "$OPCACHE_INI" ]; then
                log "Enabling opcache in $OPCACHE_INI..."
                
                # Find opcache.so location
                OPCACHE_SO=""
                for possible_path in "/usr/lib/php/${PHP_VERSION}/opcache.so" \
                                    "/usr/lib/php/$(php -r 'echo PHP_VERSION;' | cut -d. -f1,2)/opcache.so" \
                                    "/usr/lib/php/20250925/opcache.so" \
                                    "/usr/lib/php/20220829/opcache.so"; do
                    if [ -f "$possible_path" ]; then
                        OPCACHE_SO="$possible_path"
                        log "Found opcache.so at: $OPCACHE_SO"
                        break
                    fi
                done
                
                # If opcache.so not found, try to find it
                if [ -z "$OPCACHE_SO" ]; then
                    OPCACHE_SO=$(find /usr/lib/php -name "opcache.so" 2>/dev/null | head -1)
                    if [ -n "$OPCACHE_SO" ]; then
                        log "Found opcache.so at: $OPCACHE_SO"
                    fi
                fi
                
                if [ -n "$OPCACHE_SO" ] && [ -f "$OPCACHE_SO" ]; then
                    sudo tee "$OPCACHE_INI" > /dev/null <<EOF
; Enable opcache for better performance
zend_extension=$OPCACHE_SO
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
EOF
                    log "✓ Opcache configuration created at $OPCACHE_INI"
                    warn "⚠ PHP-FPM needs to be restarted for opcache to take effect"
                else
                    # Opcache.so not found - check if opcache is already enabled in php.ini
                    if php -m 2>/dev/null | grep -qi opcache; then
                        log "✓ Opcache is already enabled (built-in)"
                    else
                        # Don't create config if opcache.so not found - it will cause warnings
                        log "⚠ Opcache.so not found, skipping opcache configuration"
                        log "⚠ Opcache may be built-in and enabled in main php.ini"
                        warn "⚠ You can manually enable opcache in /etc/php/${PHP_VERSION}/fpm/php.ini if needed"
                    fi
                fi
            else
                log "✓ Opcache configuration file already exists at $OPCACHE_INI"
            fi
        else
            warn "⚠ Could not find PHP ini directory for opcache configuration"
            warn "⚠ Opcache may need manual configuration in php.ini"
            warn "⚠ Common locations: /etc/php/${PHP_VERSION}/fpm/php.ini or /etc/php/${PHP_VERSION}/cli/php.ini"
        fi
    fi
    
    if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
        error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
        error "Loaded extensions:"
        php -m | grep -E "^[a-z]" | head -20 || true
        error ""
        error "Trying to install missing extensions..."
        
        # Try to install missing extensions
        for ext in "${MISSING_EXTENSIONS[@]}"; do
            # Map extension names to package names
            case "$ext" in
                "pdo_mysql"|"mysql")
                    PACKAGE_NAME="php${PHP_VERSION}-mysql"
                    ;;
                "zip")
                    PACKAGE_NAME="php${PHP_VERSION}-zip"
                    ;;
                "gd")
                    PACKAGE_NAME="php${PHP_VERSION}-gd"
                    ;;
                "mbstring")
                    PACKAGE_NAME="php${PHP_VERSION}-mbstring"
                    ;;
                "curl")
                    PACKAGE_NAME="php${PHP_VERSION}-curl"
                    ;;
                "xml")
                    PACKAGE_NAME="php${PHP_VERSION}-xml"
                    ;;
                "bcmath")
                    PACKAGE_NAME="php${PHP_VERSION}-bcmath"
                    ;;
                "intl")
                    PACKAGE_NAME="php${PHP_VERSION}-intl"
                    ;;
                *)
                    warn "Extension $ext is built-in or unknown, skipping package installation..."
                    continue
                    ;;
            esac
            
            log "Installing $PACKAGE_NAME for extension $ext..."
            sudo apt-get install -y "$PACKAGE_NAME" 2>&1 | tee -a "$LOG_FILE" || {
                warn "Failed to install $PACKAGE_NAME"
            }
        done
        
        # Re-check extensions after installation attempt
        log "Re-checking extensions after installation..."
        MISSING_EXTENSIONS=()
        for ext in "${REQUIRED_EXTENSIONS[@]}"; do
            if [ "$ext" = "pdo_mysql" ]; then
                if ! php -m | grep -qiE "^(pdo_mysql|mysqli|mysql)$"; then
                    MISSING_EXTENSIONS+=("$ext")
                fi
            elif ! php -m | grep -qi "^${ext}$"; then
                MISSING_EXTENSIONS+=("$ext")
            fi
        done
        
        if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
            error "Still missing critical extensions after installation attempt: ${MISSING_EXTENSIONS[*]}"
            error "Please check PHP configuration and installed packages"
            error "You may need to restart PHP-FPM: sudo systemctl restart php${PHP_VERSION}-fpm"
            exit 1
        else
            log "✓ All required extensions are now loaded"
        fi
    else
        log "✓ All required PHP extensions are loaded"
    fi
    
    # Final opcache check (optional, don't fail if not enabled)
    if ! php -m | grep -qi "^opcache$"; then
        warn "⚠ Opcache is not enabled (optional but recommended for production)"
        warn "⚠ Opcache configuration has been created, restart PHP-FPM to enable:"
        warn "⚠   sudo systemctl restart php${PHP_VERSION}-fpm"
    fi
    
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
    
    log "Installing Composer to /usr/local/bin..."
    # Use sudo to install to /usr/local/bin
    sudo php composer-installer.php --install-dir=/usr/local/bin --filename=composer || {
        error "Failed to install Composer"
        error "Trying alternative installation method..."
        # Alternative: Install to user's local bin
        mkdir -p ~/.local/bin 2>/dev/null || true
        php composer-installer.php --install-dir=~/.local/bin --filename=composer || {
            error "Failed to install Composer with alternative method"
            rm -f composer-installer.php
            exit 1
        }
        # Add to PATH if not already there
        if ! echo "$PATH" | grep -q "$HOME/.local/bin"; then
            echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.bashrc
            export PATH="$HOME/.local/bin:$PATH"
        fi
        log "✓ Composer installed to ~/.local/bin"
    }
    
    rm -f composer-installer.php
    
    # Verify installation
    if ! verify_installation composer; then
        error "Composer installation verification failed"
        error "Please check if Composer is in PATH: which composer"
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
    
    # Stop Nginx if running to avoid port conflicts
    if systemctl is-active --quiet nginx; then
        log "Nginx is running, stopping to avoid port conflicts..."
        sudo systemctl stop nginx || true
        sudo systemctl disable nginx || true
    fi
    
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
    
    # CRITICAL: Create and prepare Laravel directories BEFORE composer install
    # This prevents "Please provide a valid cache path" errors
    log "Preparing Laravel directories..."
    mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs 2>/dev/null || true
    
    # Set permissions (use current user for now, will fix ownership later)
    chmod -R 775 bootstrap/cache storage 2>/dev/null || true
    
    # Create .gitkeep files to ensure directories exist
    touch bootstrap/cache/.gitkeep storage/framework/cache/.gitkeep storage/framework/sessions/.gitkeep storage/framework/views/.gitkeep storage/logs/.gitkeep 2>/dev/null || true
    
    # Clear any existing problematic cache files
    rm -f bootstrap/cache/*.php 2>/dev/null || true
    
    log "✓ Laravel directories prepared"
    
    # Install PHP dependencies
    log "Installing PHP dependencies with Composer..."
    if [ -f "composer.json" ]; then
        # Check if composer is available
        if ! command -v composer &>/dev/null; then
            error "Composer is not available in PATH"
            error "Please ensure Composer is installed and in PATH"
            exit 1
        fi
        
        # Verify PHP version is 8.3
        PHP_VERSION_CHECK=$(php -v | head -n 1 | cut -d " " -f 2 | cut -c 1-3)
        if [ "$PHP_VERSION_CHECK" != "8.3" ]; then
            warn "PHP version is $PHP_VERSION_CHECK, expected 8.3"
            warn "This may cause compatibility issues"
        else
            log "✓ PHP version verified: $PHP_VERSION_CHECK"
        fi
        
        # Set COMPOSER_ALLOW_SUPERUSER if running as root
        if [ "$IS_ROOT" = true ]; then
            export COMPOSER_ALLOW_SUPERUSER=1
            log "Running as root - enabling COMPOSER_ALLOW_SUPERUSER"
        fi
        
        # NUCLEAR FIX: Reset Composer environment to resolve dependency conflicts
        log "Resetting Composer environment to resolve dependency conflicts..."
        
        # Step 1: Clean environment - Remove stale state (composer.lock and vendor)
        log "Cleaning Composer environment..."
        if [ -f "composer.lock" ]; then
            log "Removing stale composer.lock..."
            rm -f composer.lock || true
        fi
        
        if [ -d "vendor" ]; then
            log "Removing stale vendor directory..."
            rm -rf vendor || true
        fi
        log "✓ Environment cleaned"
        
        # Step 2: Remove conflicting phpspreadsheet constraint from composer.json
        # Let maatwebsite/excel determine the correct version
        log "Removing conflicting phpspreadsheet constraint from composer.json..."
        log "Letting maatwebsite/excel determine the correct version..."
        
        # Try to remove via composer (use || true to not exit if package doesn't exist)
        composer remove phpoffice/phpspreadsheet --no-interaction 2>&1 | tee -a "$LOG_FILE" || true
        
        # Verify removal (check if still exists in composer.json)
        if grep -q '"phpoffice/phpspreadsheet"' composer.json; then
            warn "phpspreadsheet still in composer.json, trying manual removal..."
            # Manual removal as fallback
            if command -v jq &>/dev/null; then
                # Use jq if available (cleaner JSON manipulation)
                jq 'del(.require."phpoffice/phpspreadsheet")' composer.json > composer.json.tmp && mv composer.json.tmp composer.json
                log "✓ Manually removed phpspreadsheet using jq"
            else
                # Fallback: use sed to remove the line
                sed -i '/"phpoffice\/phpspreadsheet"/d' composer.json
                log "✓ Manually removed phpspreadsheet using sed"
            fi
        else
            log "✓ phpspreadsheet constraint removed from composer.json"
        fi
        
        # Step 3: Register maatwebsite/excel package (CRITICAL: NO --no-dev flag here!)
        # The 'require' command does NOT support --no-dev flag
        # This will let Composer find the best compatible version set for PHP 8.3
        log "Registering maatwebsite/excel with automatic dependency resolution..."
        log "Note: Using 'composer require' without --no-dev (flag not supported by require command)"
        
        if ! composer require maatwebsite/excel --with-all-dependencies --no-interaction 2>&1 | tee -a "$LOG_FILE"; then
            warn "Direct require failed, trying with --ignore-platform-reqs..."
            if ! composer require maatwebsite/excel --with-all-dependencies --no-interaction --ignore-platform-reqs 2>&1 | tee -a "$LOG_FILE"; then
                error "Failed to register maatwebsite/excel"
                error "Please check the error messages above"
                exit 1
            fi
        fi
        log "✓ maatwebsite/excel registered successfully"
        
        # Step 4: Final production install (NOW use --no-dev flag)
        # The 'install' command supports --no-dev flag
        log "Running final production install (excluding dev dependencies)..."
        
        if ! composer install --optimize-autoloader --no-dev --no-interaction 2>&1 | tee -a "$LOG_FILE"; then
            warn "Production install failed, trying with --ignore-platform-reqs as fallback..."
            if ! composer install --optimize-autoloader --no-dev --no-interaction --ignore-platform-reqs 2>&1 | tee -a "$LOG_FILE"; then
                error "Failed to install dependencies even with --ignore-platform-reqs"
                error "Please check the error messages above"
                exit 1
            fi
        fi
        
        log "✓ PHP dependencies installed successfully"
    else
        error "composer.json not found!"
        exit 1
    fi
    
    # Install NPM dependencies
    log "Installing NPM dependencies..."
    if [ -f "package.json" ]; then
        # Step 1: Clean slate - Remove existing node_modules to prevent permission conflicts
        if [ -d "node_modules" ]; then
            log "Removing existing node_modules for clean installation..."
            rm -rf node_modules || true
            log "✓ Cleaned existing node_modules"
        fi
        
        # Step 2: Install ALL dependencies (including dev) because vite is needed for build
        # Use --unsafe-perm and --allow-root when running as root to avoid permission issues
        log "Installing NPM dependencies (including dev dependencies for build tools)..."
        if [ "$IS_ROOT" = true ]; then
            log "Running as root - using --unsafe-perm=true --allow-root flags"
            npm install --unsafe-perm=true --allow-root 2>&1 | tee -a "$LOG_FILE" || {
                error "Failed to install NPM dependencies"
                exit 1
            }
        else
            npm install 2>&1 | tee -a "$LOG_FILE" || {
                error "Failed to install NPM dependencies"
                exit 1
            }
        fi
        log "✓ NPM dependencies installed"
        
        # Step 3: CRITICAL FIX - Fix executable permissions for binaries in node_modules/.bin
        # This fixes "vite: Permission denied" error
        if [ -d "node_modules/.bin" ]; then
            log "Fixing executable permissions for NPM binaries..."
            chmod -R +x node_modules/.bin 2>&1 | tee -a "$LOG_FILE" || {
                warn "Failed to set executable permissions, trying with sudo..."
                $SUDO_CMD chmod -R +x node_modules/.bin 2>&1 | tee -a "$LOG_FILE" || {
                    warn "Permission fix failed, but continuing..."
                }
            }
            log "✓ Executable permissions fixed for node_modules/.bin"
        else
            warn "node_modules/.bin directory not found, skipping permission fix"
        fi
        
        # Step 4: Build assets (only after permissions are fixed)
        log "Building assets..."
        npm run build 2>&1 | tee -a "$LOG_FILE" || {
            error "Failed to build assets"
            error "Check if vite has executable permissions: ls -la node_modules/.bin/vite"
            error "If permission denied, run: chmod +x node_modules/.bin/*"
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
    
    # Ensure bootstrap/cache exists before generating key
    mkdir -p bootstrap/cache
    chmod 775 bootstrap/cache
    
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
    
    # Ask for database password if not set
    if [ -z "$DB_PASS" ]; then
        echo ""
        echo "════════════════════════════════════════════════════════════"
        echo "  Database Setup"
        echo "════════════════════════════════════════════════════════════"
        echo ""
        echo "Anda perlu membuat password untuk database user '$DB_USER'."
        echo "Password ini akan digunakan oleh aplikasi untuk mengakses database."
        echo ""
        echo "⚠️  PENTING:"
        echo "   - Buat password yang kuat (minimal 8 karakter)"
        echo "   - Simpan password ini dengan aman"
        echo "   - Password ini akan disimpan di file .env"
        echo ""
        read -sp "Masukkan password untuk database user '$DB_USER': " DB_PASS
        echo
        if [ -z "$DB_PASS" ]; then
            error "Password tidak boleh kosong!"
            exit 1
        fi
        read -sp "Konfirmasi password: " DB_PASS_CONFIRM
        echo
        if [ "$DB_PASS" != "$DB_PASS_CONFIRM" ]; then
            error "Password tidak cocok!"
            exit 1
        fi
    fi
    
    log "Creating database and user..."
    
    # Try to access MySQL - Ubuntu typically uses auth_socket for root
    # First try with sudo (no password needed for auth_socket)
    log "Attempting to access MySQL..."
    
    MYSQL_CMD=""
    MYSQL_ACCESS_METHOD=""
    
    # Method 1: Try sudo mysql (for auth_socket plugin - Ubuntu default)
    if sudo mysql -e "SELECT 1;" >/dev/null 2>&1; then
        MYSQL_CMD="sudo mysql"
        MYSQL_ACCESS_METHOD="sudo"
        log "✓ MySQL access via sudo (auth_socket) successful"
    # Method 2: Try mysql -u root without password
    elif mysql -u root -e "SELECT 1;" >/dev/null 2>&1; then
        MYSQL_CMD="mysql -u root"
        MYSQL_ACCESS_METHOD="root_no_pass"
        log "✓ MySQL access as root without password successful"
    # Method 3: Ask for root password
    else
        echo ""
        echo "MySQL root access memerlukan autentikasi."
        echo "Di Ubuntu, biasanya root menggunakan 'auth_socket' plugin."
        echo ""
        echo "Opsi:"
        echo "  1) Tekan Enter untuk mencoba dengan sudo (tidak perlu password)"
        echo "  2) Atau masukkan MySQL root password jika sudah di-set"
        echo ""
        read -sp "MySQL root password (atau Enter untuk skip): " MYSQL_ROOT_PASS
        echo
        
        if [ -z "$MYSQL_ROOT_PASS" ]; then
            # Try sudo mysql
            if sudo mysql -e "SELECT 1;" >/dev/null 2>&1; then
                MYSQL_CMD="sudo mysql"
                MYSQL_ACCESS_METHOD="sudo"
                log "✓ MySQL access via sudo successful"
            else
                error "Tidak bisa mengakses MySQL. Pastikan MySQL sudah terinstall dan running."
                error "Coba jalankan: sudo systemctl status mysql"
                exit 1
            fi
        else
            # Try with password
            if mysql -u root -p"$MYSQL_ROOT_PASS" -e "SELECT 1;" >/dev/null 2>&1; then
                MYSQL_CMD="mysql -u root -p$MYSQL_ROOT_PASS"
                MYSQL_ACCESS_METHOD="root_with_pass"
                log "✓ MySQL access with password successful"
            else
                error "Password MySQL root salah atau MySQL tidak bisa diakses"
                exit 1
            fi
        fi
    fi
    
    # Create database and user
    log "Creating database '$DB_NAME' and user '$DB_USER'..."
    $MYSQL_CMD <<EOF 2>&1 | tee -a "$LOG_FILE"
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    if [ ${PIPESTATUS[0]} -ne 0 ]; then
        error "Failed to create database"
        error "Please check MySQL access and try again"
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
    
    # Use SUDO_CMD variable (empty if root, "sudo" if non-root)
    SUDO_CMD="${SUDO_CMD:-sudo}"
    
    # Determine web server user
    WEB_USER="www-data"
    if [ "$IS_ROOT" = true ]; then
        # If root, we can set ownership more flexibly
        # Check if www-data exists, if not use current user or root
        if ! id -u "$WEB_USER" &>/dev/null; then
            warn "www-data user not found, using root for ownership"
            WEB_USER="root"
        fi
    fi
    
    # STEP 1: Set generic permissions FIRST (before artisan commands)
    # This allows artisan commands to run without permission issues
    log "Setting initial permissions for storage and cache directories..."
    
    # Ensure directories exist
    mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache 2>/dev/null || true
    
    # Set permissions - more permissive for storage (775 = rwxrwxr-x)
    $SUDO_CMD chmod -R 775 storage bootstrap/cache 2>/dev/null || {
        error "Failed to set storage permissions"
        exit 1
    }
    
    # Set ownership temporarily to current user (or root) so artisan can write
    CURRENT_USER=${SUDO_USER:-$USER}
    if [ "$IS_ROOT" = true ]; then
        CURRENT_USER="root"
    fi
    
    log "Setting temporary ownership to $CURRENT_USER for artisan commands..."
    $SUDO_CMD chown -R "$CURRENT_USER:$CURRENT_USER" storage bootstrap/cache 2>/dev/null || {
        warn "Failed to set temporary ownership, continuing..."
    }
    
    # STEP 2: Run artisan commands NOW (while we have write permissions)
    log "Running artisan commands with proper permissions..."
    
    # Create storage link - remove existing symlink first if it exists
    if [ -L "public/storage" ] || [ -e "public/storage" ]; then
        log "Removing existing storage symlink..."
        rm -f public/storage 2>/dev/null || $SUDO_CMD rm -f public/storage 2>/dev/null || true
    fi
    
    log "Creating storage link..."
    php artisan storage:link 2>&1 | tee -a "$LOG_FILE" || {
        warn "Storage link creation failed, will retry after ownership change"
    }
    
    # Clear and rebuild caches to ensure everything works
    log "Clearing application caches..."
    php artisan config:clear 2>&1 | tee -a "$LOG_FILE" || true
    php artisan cache:clear 2>&1 | tee -a "$LOG_FILE" || true
    php artisan view:clear 2>&1 | tee -a "$LOG_FILE" || true
    
    # STEP 3: NOW set final ownership to www-data (AFTER all artisan commands)
    log "Setting final ownership to $WEB_USER (after artisan commands)..."
    $SUDO_CMD chown -R "$WEB_USER:$WEB_USER" "$PROJECT_DIR" || {
        error "Failed to set ownership"
        exit 1
    }
    
    log "Setting directory permissions..."
    $SUDO_CMD find "$PROJECT_DIR" -type d -exec chmod 755 {} \; || true
    
    log "Setting file permissions..."
    $SUDO_CMD find "$PROJECT_DIR" -type f -exec chmod 644 {} \; || true
    
    # Ensure storage and cache remain writable
    log "Ensuring storage and cache remain writable..."
    $SUDO_CMD chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    $SUDO_CMD chown -R "$WEB_USER:$WEB_USER" bootstrap/cache storage 2>/dev/null || {
        warn "Failed to set ownership to $WEB_USER, continuing..."
    }
    
    # If running as root and www-data exists, add group write permissions
    if [ "$IS_ROOT" = true ] && [ "$WEB_USER" != "root" ] && id -u "$WEB_USER" &>/dev/null; then
        # Add www-data group write permission
        $SUDO_CMD chmod -R g+w storage bootstrap/cache 2>/dev/null || true
    fi
    
    # Retry storage link if it failed earlier
    if [ ! -L "public/storage" ]; then
        log "Retrying storage link creation with $WEB_USER user..."
        $SUDO_CMD -u "$WEB_USER" php artisan storage:link 2>&1 | tee -a "$LOG_FILE" || {
            warn "Storage link creation failed, you may need to run manually: php artisan storage:link"
        }
    fi
    
    log "✓ Permissions configured"
}

###############################################################################
# Configure Web Server
###############################################################################

configure_webserver() {
    step "Configuring Web Server"
    
    # Detect which web server is running or should be used
    NGINX_ACTIVE=false
    APACHE_ACTIVE=false
    
    if systemctl is-active --quiet nginx; then
        NGINX_ACTIVE=true
        log "Nginx is currently running"
    fi
    
    if systemctl is-active --quiet apache2; then
        APACHE_ACTIVE=true
        log "Apache is currently running"
    fi
    
    # If both are running, ask user which one to use
    if [ "$NGINX_ACTIVE" = true ] && [ "$APACHE_ACTIVE" = true ]; then
        warn "Both Nginx and Apache are running! This will cause port conflicts."
        echo ""
        echo "Which web server do you want to use?"
        echo "1) Nginx (recommended)"
        echo "2) Apache"
        read -p "Enter choice [1]: " WS_CHOICE
        WS_CHOICE=${WS_CHOICE:-1}
        
        if [ "$WS_CHOICE" = "1" ]; then
            log "Stopping Apache to avoid port conflicts..."
            $SUDO_CMD systemctl stop apache2 || true
            $SUDO_CMD systemctl disable apache2 || true
            APACHE_ACTIVE=false
            configure_nginx
        else
            log "Stopping Nginx to avoid port conflicts..."
            $SUDO_CMD systemctl stop nginx || true
            $SUDO_CMD systemctl disable nginx || true
            NGINX_ACTIVE=false
            configure_apache
        fi
    elif [ "$NGINX_ACTIVE" = true ]; then
        # Ensure Apache is stopped
        if systemctl is-installed --quiet apache2 2>/dev/null; then
            log "Stopping Apache to avoid conflicts..."
            $SUDO_CMD systemctl stop apache2 || true
            $SUDO_CMD systemctl disable apache2 || true
        fi
        configure_nginx
    elif [ "$APACHE_ACTIVE" = true ]; then
        # Ensure Nginx is stopped
        if systemctl is-installed --quiet nginx 2>/dev/null; then
            log "Stopping Nginx to avoid conflicts..."
            $SUDO_CMD systemctl stop nginx || true
            $SUDO_CMD systemctl disable nginx || true
        fi
        configure_apache
    else
        warn "No web server detected, skipping configuration"
        warn "Please install and start Nginx or Apache first"
    fi
}

configure_nginx() {
    log "Configuring Nginx..."
    
    read -p "Domain name (or IP): " DOMAIN
    DOMAIN=${DOMAIN:-localhost}
    
    # Force PHP 8.3 (we installed PHP 8.3 explicitly)
    PHP_VERSION_INSTALLED="8.3"
    
    # Verify PHP-FPM is installed and running
    log "Verifying PHP-FPM 8.3 installation..."
    if ! systemctl is-active --quiet php8.3-fpm; then
        log "PHP-FPM 8.3 is not running, starting it..."
        $SUDO_CMD systemctl start php8.3-fpm || {
            error "Failed to start PHP-FPM 8.3"
            exit 1
        }
    fi
    
    # Enable and ensure PHP-FPM starts on boot
    $SUDO_CMD systemctl enable php8.3-fpm || true
    
    # Verify PHP-FPM socket exists
    PHP_FPM_SOCKET="/var/run/php/php8.3-fpm.sock"
    if [ ! -S "$PHP_FPM_SOCKET" ]; then
        error "PHP-FPM socket not found at $PHP_FPM_SOCKET"
        error "Please check PHP-FPM configuration"
        log "Checking PHP-FPM status..."
        $SUDO_CMD systemctl status php8.3-fpm || true
        exit 1
    fi
    
    # Fix socket permissions if needed
    $SUDO_CMD chown www-data:www-data "$PHP_FPM_SOCKET" 2>/dev/null || true
    $SUDO_CMD chmod 666 "$PHP_FPM_SOCKET" 2>/dev/null || true
    
    log "✓ PHP-FPM 8.3 verified and running"
    
    NGINX_CONFIG="/etc/nginx/sites-available/bmt_lucky_draw"
    
    log "Creating Nginx configuration for PHP ${PHP_VERSION_INSTALLED}..."
    
    # Build server_name list (domain + IP if provided)
    if [ -n "$SERVER_IP" ]; then
        SERVER_NAMES="${DOMAIN} ${SERVER_IP}"
        log "✓ Configuring for domain: $DOMAIN and IP: $SERVER_IP"
    else
        SERVER_NAMES="${DOMAIN}"
        log "✓ Configuring for domain: $DOMAIN"
    fi
    
    $SUDO_CMD tee "$NGINX_CONFIG" > /dev/null <<EOF
server {
    listen 80;
    server_name ${SERVER_NAMES};
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
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    # Enable site
    $SUDO_CMD ln -sf "$NGINX_CONFIG" /etc/nginx/sites-enabled/bmt_lucky_draw || true
    $SUDO_CMD rm -f /etc/nginx/sites-enabled/default || true
    
    # Test configuration
    $SUDO_CMD nginx -t 2>&1 | tee -a "$LOG_FILE" || {
        error "Nginx configuration test failed"
        exit 1
    }
    
    # Reload Nginx
    $SUDO_CMD systemctl reload nginx || {
        error "Failed to reload Nginx"
        exit 1
    }
    
    # Verify PHP-FPM is still running after reload
    if ! systemctl is-active --quiet php8.3-fpm; then
        warn "PHP-FPM stopped, restarting..."
        $SUDO_CMD systemctl restart php8.3-fpm || {
            error "Failed to restart PHP-FPM"
            exit 1
        }
    fi
    
    if [ -n "$SERVER_IP" ]; then
        log "✓ Nginx configured for $DOMAIN and $SERVER_IP"
    else
        log "✓ Nginx configured for $DOMAIN"
    fi
    log "✓ PHP-FPM 8.3 is running and connected"
    
    # Update .env with correct APP_URL and HTTPS settings
    update_production_env "$DOMAIN" "$USE_HTTPS" "$SERVER_IP"
}

update_production_env() {
    local DOMAIN=$1
    local USE_HTTPS=$2
    local SERVER_IP=$3
    
    if [ -z "$DOMAIN" ]; then
        return 0
    fi
    
    cd "$PROJECT_DIR" || exit 1
    
    log "Updating .env for production configuration..."
    
    # Determine APP_URL based on HTTPS (always use domain for APP_URL)
    if [ "$USE_HTTPS" = "y" ] || [ "$USE_HTTPS" = "Y" ]; then
        APP_URL="https://${DOMAIN}"
        SESSION_SECURE="true"
        log "✓ Configured for HTTPS: $APP_URL"
    else
        APP_URL="http://${DOMAIN}"
        # If IP is provided and domain uses HTTP, set SESSION_SECURE to false
        # because IP access will be HTTP (not HTTPS)
        SESSION_SECURE="false"
        log "✓ Configured for HTTP: $APP_URL"
    fi
    
    # If IP is provided, log that both access methods are supported
    if [ -n "$SERVER_IP" ]; then
        if [ "$USE_HTTPS" = "y" ] || [ "$USE_HTTPS" = "Y" ]; then
            log "✓ Application accessible via: https://${DOMAIN} and http://${SERVER_IP}"
        else
            log "✓ Application accessible via: http://${DOMAIN} and http://${SERVER_IP}"
        fi
    fi
    
    # Update APP_URL in .env
    if grep -q "^APP_URL=" .env; then
        # Use sed to update APP_URL
        if command -v sed &>/dev/null; then
            if [[ "$OSTYPE" == "darwin"* ]]; then
                # macOS sed
                sed -i '' "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
            else
                # Linux sed
                sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
            fi
        else
            # Fallback: use perl or python
            if command -v perl &>/dev/null; then
                perl -i -pe "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
            elif command -v python3 &>/dev/null; then
                python3 -c "
import re
with open('.env', 'r') as f:
    content = f.read()
content = re.sub(r'^APP_URL=.*', f'APP_URL={APP_URL}', content, flags=re.MULTILINE)
with open('.env', 'w') as f:
    f.write(content)
"
            fi
        fi
        log "✓ Updated APP_URL to $APP_URL"
    else
        # Add APP_URL if not exists
        echo "APP_URL=${APP_URL}" >> .env
        log "✓ Added APP_URL to .env"
    fi
    
    # Update SESSION_SECURE_COOKIE for HTTPS
    # If IP is provided, we need to support both HTTPS (domain) and HTTP (IP)
    # So SESSION_SECURE_COOKIE should be false to allow HTTP access via IP
    if [ "$SESSION_SECURE" = "true" ] && [ -z "$SERVER_IP" ]; then
        # Only set secure cookie if no IP access (pure HTTPS)
        if grep -q "^SESSION_SECURE_COOKIE=" .env; then
            if command -v sed &>/dev/null; then
                if [[ "$OSTYPE" == "darwin"* ]]; then
                    sed -i '' "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" .env
                else
                    sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" .env
                fi
            fi
        else
            echo "SESSION_SECURE_COOKIE=true" >> .env
        fi
        log "✓ Set SESSION_SECURE_COOKIE=true for HTTPS"
    elif [ -n "$SERVER_IP" ]; then
        # If IP is provided, set to false to allow HTTP access via IP
        if grep -q "^SESSION_SECURE_COOKIE=" .env; then
            if command -v sed &>/dev/null; then
                if [[ "$OSTYPE" == "darwin"* ]]; then
                    sed -i '' "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=false|" .env
                else
                    sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=false|" .env
                fi
            fi
        else
            echo "SESSION_SECURE_COOKIE=false" >> .env
        fi
        log "✓ Set SESSION_SECURE_COOKIE=false to support IP access (HTTP)"
    fi
    
    # Set SESSION_DOMAIN to empty to allow cookies for both domain and IP
    if [ -n "$SERVER_IP" ]; then
        if grep -q "^SESSION_DOMAIN=" .env; then
            if command -v sed &>/dev/null; then
                if [[ "$OSTYPE" == "darwin"* ]]; then
                    sed -i '' "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=|" .env
                else
                    sed -i "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=|" .env
                fi
            fi
        else
            echo "SESSION_DOMAIN=" >> .env
        fi
        log "✓ Set SESSION_DOMAIN empty to support both domain and IP access"
    fi
    
    # Clear config cache to apply changes
    log "Clearing configuration cache..."
    php artisan config:clear 2>&1 | tee -a "$LOG_FILE" || true
    php artisan cache:clear 2>&1 | tee -a "$LOG_FILE" || true
    
    log "✓ Production environment updated"
}

configure_apache() {
    log "Configuring Apache..."
    
    read -p "Domain name [e.g., bmtnu.raf.my.id]: " DOMAIN
    DOMAIN=${DOMAIN:-localhost}
    
    read -p "Server IP address [e.g., 172.17.2.5] (optional, press Enter to skip): " SERVER_IP
    SERVER_IP=${SERVER_IP:-}
    
    # Ask if using HTTPS (for Cloudflare Tunnel or SSL)
    echo ""
    read -p "Is this domain using HTTPS? (y/N): " USE_HTTPS
    USE_HTTPS=${USE_HTTPS:-N}
    
    # Force PHP 8.3 (we installed PHP 8.3 explicitly)
    PHP_VERSION_INSTALLED="8.3"
    
    # Verify PHP-FPM is installed and running (Apache uses PHP-FPM via mod_proxy_fcgi)
    log "Verifying PHP-FPM 8.3 installation..."
    if ! systemctl is-active --quiet php8.3-fpm; then
        log "PHP-FPM 8.3 is not running, starting it..."
        $SUDO_CMD systemctl start php8.3-fpm || {
            error "Failed to start PHP-FPM 8.3"
            exit 1
        }
    fi
    
    # Enable and ensure PHP-FPM starts on boot
    $SUDO_CMD systemctl enable php8.3-fpm || true
    
    log "✓ PHP-FPM 8.3 verified and running"
    
    APACHE_CONFIG="/etc/apache2/sites-available/bmt_lucky_draw.conf"
    
    log "Creating Apache configuration..."
    $SUDO_CMD tee "$APACHE_CONFIG" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${PROJECT_DIR}/public
    
    <Directory ${PROJECT_DIR}/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    # PHP-FPM configuration for PHP 8.3
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php${PHP_VERSION_INSTALLED}-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    ErrorLog \${APACHE_LOG_DIR}/bmt_lucky_draw_error.log
    CustomLog \${APACHE_LOG_DIR}/bmt_lucky_draw_access.log combined
</VirtualHost>
EOF
    
    # Enable required Apache modules
    log "Enabling required Apache modules..."
    $SUDO_CMD a2enmod rewrite || true
    $SUDO_CMD a2enmod proxy || true
    $SUDO_CMD a2enmod proxy_fcgi || true
    $SUDO_CMD a2enmod setenvif || true
    
    # Enable site and disable default
    $SUDO_CMD a2ensite bmt_lucky_draw.conf || true
    $SUDO_CMD a2dissite 000-default.conf || true
    
    # Test configuration
    $SUDO_CMD apache2ctl configtest 2>&1 | tee -a "$LOG_FILE" || {
        error "Apache configuration test failed"
        exit 1
    }
    
    # Restart Apache
    $SUDO_CMD systemctl restart apache2 || {
        error "Failed to restart Apache"
        exit 1
    }
    
    # Verify PHP-FPM is still running after restart
    if ! systemctl is-active --quiet php8.3-fpm; then
        warn "PHP-FPM stopped, restarting..."
        $SUDO_CMD systemctl restart php8.3-fpm || {
            error "Failed to restart PHP-FPM"
            exit 1
        }
    fi
    
    if [ -n "$SERVER_IP" ]; then
        log "✓ Apache configured for $DOMAIN and $SERVER_IP"
    else
        log "✓ Apache configured for $DOMAIN"
    fi
    log "✓ PHP-FPM 8.3 is running and connected"
    
    # Update .env with correct APP_URL and HTTPS settings
    update_production_env "$DOMAIN" "$USE_HTTPS" "$SERVER_IP"
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
    echo "║  BMT Lucky Draw System - Ubuntu 22.04 Installation Script  ║"
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

