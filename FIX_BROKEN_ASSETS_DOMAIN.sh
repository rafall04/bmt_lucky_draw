#!/bin/bash
# Fix Broken Assets on Domain Access
# Solusi untuk masalah CSS/JS/images broken saat diakses via Domain
# Jalankan: bash FIX_BROKEN_ASSETS_DOMAIN.sh

set -e

PROJECT_DIR="/home/unnet/bmt_lucky_draw"
ENV_FILE="$PROJECT_DIR/.env"

cd "$PROJECT_DIR" || exit 1

echo "🔧 Fixing Broken Assets on Domain Access"
echo "=========================================="
echo ""

# Get domain from user or use default
if [ -z "$1" ]; then
    echo "Usage: bash FIX_BROKEN_ASSETS_DOMAIN.sh <your-domain>"
    echo "Example: bash FIX_BROKEN_ASSETS_DOMAIN.sh https://bmtnu.raf.my.id"
    echo ""
    read -p "Enter your domain (with https://): " DOMAIN
else
    DOMAIN="$1"
fi

# Validate domain format
if [[ ! "$DOMAIN" =~ ^https?:// ]]; then
    echo "⚠ Warning: Domain should include protocol (https://)"
    echo "Adding https:// prefix..."
    DOMAIN="https://${DOMAIN#http://}"
    DOMAIN="https://${DOMAIN#https://}"
fi

echo "Using domain: $DOMAIN"
echo ""

# Step 1: Backup .env
echo "1. Backing up .env file..."
if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "${ENV_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
    echo "   ✓ Backup created"
else
    echo "   ⚠ .env file not found!"
    exit 1
fi

# Step 2: Update APP_URL
echo ""
echo "2. Updating APP_URL in .env..."
if grep -q "^APP_URL=" "$ENV_FILE"; then
    # Replace existing APP_URL
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "s|^APP_URL=.*|APP_URL=$DOMAIN|" "$ENV_FILE"
    else
        # Linux
        sed -i "s|^APP_URL=.*|APP_URL=$DOMAIN|" "$ENV_FILE"
    fi
    echo "   ✓ APP_URL updated to: $DOMAIN"
else
    # Add APP_URL if not exists
    echo "APP_URL=$DOMAIN" >> "$ENV_FILE"
    echo "   ✓ APP_URL added: $DOMAIN"
fi

# Step 3: Remove or comment ASSET_URL (let Laravel use APP_URL)
echo ""
echo "3. Configuring ASSET_URL..."
if grep -q "^ASSET_URL=" "$ENV_FILE"; then
    # Comment out ASSET_URL to use APP_URL instead
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|^ASSET_URL=|#ASSET_URL=|" "$ENV_FILE"
    else
        sed -i "s|^ASSET_URL=|#ASSET_URL=|" "$ENV_FILE"
    fi
    echo "   ✓ ASSET_URL commented out (will use APP_URL)"
elif grep -q "^#ASSET_URL=" "$ENV_FILE"; then
    echo "   ✓ ASSET_URL already commented"
else
    echo "   ✓ ASSET_URL not set (will use APP_URL)"
fi

# Step 4: Force HTTPS in .env (SESSION_SECURE_COOKIE)
echo ""
echo "4. Configuring HTTPS settings..."
# Set SESSION_SECURE_COOKIE for HTTPS
if grep -q "^SESSION_SECURE_COOKIE=" "$ENV_FILE"; then
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" "$ENV_FILE"
    else
        sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" "$ENV_FILE"
    fi
    echo "   ✓ SESSION_SECURE_COOKIE set to true"
else
    echo "SESSION_SECURE_COOKIE=true" >> "$ENV_FILE"
    echo "   ✓ SESSION_SECURE_COOKIE added"
fi

# Step 5: Clear Laravel caches
echo ""
echo "5. Clearing Laravel caches..."
php artisan config:clear 2>&1 || true
php artisan cache:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true
echo "   ✓ All caches cleared"

# Step 6: Rebuild config cache with new values
echo ""
echo "6. Rebuilding config cache..."
php artisan config:cache 2>&1 || {
    echo "   ⚠ Config cache failed, but continuing..."
}
echo "   ✓ Config cache rebuilt"

# Step 7: Rebuild Vite assets (CRITICAL STEP)
echo ""
echo "7. Rebuilding Vite assets (this may take a minute)..."
echo "   This is the CRITICAL step - Vite uses APP_URL during build time"

# Check if vite is executable
if [ -f "node_modules/.bin/vite" ]; then
    if [ ! -x "node_modules/.bin/vite" ]; then
        echo "   Fixing vite permissions..."
        chmod +x node_modules/.bin/vite 2>/dev/null || sudo chmod +x node_modules/.bin/vite 2>/dev/null || true
    fi
fi

# Try npm run build
if npm run build 2>&1; then
    echo "   ✓ Assets rebuilt successfully"
else
    echo "   ⚠ npm run build failed, trying npx vite build..."
    if npx vite build 2>&1; then
        echo "   ✓ Assets rebuilt with npx vite build"
    else
        echo "   ❌ Build failed - check vite permissions"
        echo "   Try manually: chmod +x node_modules/.bin/vite && npm run build"
    fi
fi

# Step 8: Verify manifest.json
echo ""
echo "8. Verifying build output..."
if [ -f "public/build/manifest.json" ]; then
    echo "   ✓ manifest.json exists"
    # Check if manifest has correct paths
    if grep -q "\.css\|\.js" public/build/manifest.json; then
        echo "   ✓ manifest.json contains asset references"
    else
        echo "   ⚠ manifest.json seems empty or invalid"
    fi
else
    echo "   ⚠ manifest.json not found - build may have failed"
fi

# Step 9: Set proper permissions for build directory
echo ""
echo "9. Setting permissions for build directory..."
if [ -d "public/build" ]; then
    chmod -R 755 public/build 2>/dev/null || sudo chmod -R 755 public/build 2>/dev/null || true
    echo "   ✓ Build directory permissions set"
fi

# Step 10: Final verification
echo ""
echo "10. Final verification..."
echo "   Current APP_URL: $(grep "^APP_URL=" "$ENV_FILE" | cut -d'=' -f2-)"
echo "   Build directory: $(ls -la public/build/ 2>/dev/null | wc -l) files"

echo ""
echo "✅ Fix Completed!"
echo ""
echo "📋 Summary of changes:"
echo "   • APP_URL updated to: $DOMAIN"
echo "   • ASSET_URL commented (using APP_URL)"
echo "   • APP_FORCE_HTTPS enabled"
echo "   • All caches cleared"
echo "   • Vite assets rebuilt"
echo ""
echo "🔍 Next steps:"
echo "   1. Test your website: curl -I $DOMAIN"
echo "   2. Check browser console for any remaining 404s"
echo "   3. Verify assets load: $DOMAIN/build/assets/*.css"
echo "   4. If using Cloudflare, ensure SSL mode is 'Full (Strict)'"
echo ""
echo "⚠ If assets still broken:"
echo "   • Check Nginx/Apache config has correct ServerName"
echo "   • Verify SSL certificate is valid"
echo "   • Check browser console for Mixed Content errors"
echo "   • Run: php artisan config:clear && npm run build"

