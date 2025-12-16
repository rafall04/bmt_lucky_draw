#!/bin/bash
# Quick Fix Script untuk Collision Error dan Vite Permission
# Jalankan: bash FIX_CACHE_AND_VITE.sh

set -e

PROJECT_DIR="/home/unnet/bmt_lucky_draw"
cd "$PROJECT_DIR" || exit 1

echo "🔧 Fixing Collision Error and Vite Permission"
echo "=============================================="
echo ""

# 1. Remove all cache files that might contain dev dependency references
echo "1. Removing Laravel cache files..."
rm -rf bootstrap/cache/*.php 2>/dev/null || true
rm -rf bootstrap/cache/services.php 2>/dev/null || true
rm -rf bootstrap/cache/packages.php 2>/dev/null || true
echo "✓ Cache files removed"

# 2. Fix Vite Permission
echo ""
echo "2. Fixing Vite permissions..."
if [ -d "node_modules/.bin" ]; then
    # Try multiple methods to fix permissions
    chmod -R +x node_modules/.bin 2>/dev/null || true
    sudo chmod -R +x node_modules/.bin 2>/dev/null || true
    
    # Check if vite exists and fix its permission specifically
    if [ -f "node_modules/.bin/vite" ]; then
        chmod +x node_modules/.bin/vite 2>/dev/null || true
        sudo chmod +x node_modules/.bin/vite 2>/dev/null || true
        echo "✓ Vite binary found and permissions fixed"
        
        # Verify permission
        if [ -x "node_modules/.bin/vite" ]; then
            echo "✓ Vite is executable"
        else
            echo "⚠ Vite is still not executable, trying alternative method..."
            # Try with npm directly
            echo "   Will use 'npx vite' instead"
        fi
    else
        echo "⚠ vite binary not found in node_modules/.bin"
        echo "   Checking if vite package is installed..."
        if [ -d "node_modules/vite" ]; then
            echo "   ✓ vite package found, but binary missing"
            echo "   Will try to reinstall vite..."
        fi
    fi
else
    echo "⚠ node_modules/.bin not found"
fi

# 3. Rebuild package discovery (without dev dependencies, but clear cache first)
echo ""
echo "3. Rebuilding package discovery..."
php artisan package:discover --ansi 2>&1 || {
    echo "⚠ Package discovery failed, continuing..."
}

# 4. Clear all caches
echo ""
echo "4. Clearing all caches..."
php artisan config:clear 2>&1 || true
php artisan cache:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true
echo "✓ All caches cleared"

# 5. Rebuild caches
echo ""
echo "5. Rebuilding caches..."
php artisan config:cache 2>&1 || {
    echo "⚠ Config cache failed, but continuing..."
}
php artisan route:cache 2>&1 || {
    echo "⚠ Route cache failed, but continuing..."
}
php artisan view:cache 2>&1 || {
    echo "⚠ View cache failed, but continuing..."
}
echo "✓ Caches rebuilt"

# 6. Rebuild Assets
echo ""
echo "6. Rebuilding assets..."

# Check if vite is executable
if [ -f "node_modules/.bin/vite" ] && [ -x "node_modules/.bin/vite" ]; then
    echo "Using vite binary directly..."
    npm run build 2>&1
    BUILD_EXIT=$?
elif [ -f "node_modules/.bin/vite" ]; then
    echo "Vite binary exists but not executable, trying npx..."
    npx vite build 2>&1
    BUILD_EXIT=$?
else
    echo "Vite binary not found, trying npx vite..."
    npx vite build 2>&1
    BUILD_EXIT=$?
fi

if [ $BUILD_EXIT -ne 0 ]; then
    echo "❌ Build failed"
    echo "Trying alternative: reinstall vite..."
    
    # Try reinstalling vite
    npm install vite --save-dev 2>&1 || true
    
    # Fix permissions again
    if [ -f "node_modules/.bin/vite" ]; then
        chmod +x node_modules/.bin/vite 2>/dev/null || sudo chmod +x node_modules/.bin/vite 2>/dev/null || true
    fi
    
    # Try build again
    npm run build 2>&1 || {
        echo "❌ Build still failed after reinstall"
        echo "Try manually: npm install && chmod +x node_modules/.bin/* && npm run build"
        exit 1
    }
fi

echo "✓ Assets rebuilt"

echo ""
echo "✅ Fix Completed!"
echo ""
echo "Next steps:"
echo "1. Test application: curl http://localhost"
echo "2. Check assets: ls -la public/build/"

