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
    chmod -R +x node_modules/.bin || sudo chmod -R +x node_modules/.bin
    echo "✓ Vite permissions fixed"
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
npm run build 2>&1 || {
    echo "❌ Build failed - check vite permissions"
    exit 1
}
echo "✓ Assets rebuilt"

echo ""
echo "✅ Fix Completed!"
echo ""
echo "Next steps:"
echo "1. Test application: curl http://localhost"
echo "2. Check assets: ls -la public/build/"

