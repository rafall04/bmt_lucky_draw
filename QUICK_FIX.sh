#!/bin/bash
# Quick Fix Script untuk Production Issues
# Jalankan: bash QUICK_FIX.sh

set -e

PROJECT_DIR="/home/unnet/bmt_lucky_draw"
cd "$PROJECT_DIR" || exit 1

echo "🔧 Quick Fix untuk Production Issues"
echo "===================================="
echo ""

# 1. Fix Vite Permission
echo "1. Fixing Vite permissions..."
if [ -d "node_modules/.bin" ]; then
    chmod -R +x node_modules/.bin || sudo chmod -R +x node_modules/.bin
    echo "✓ Vite permissions fixed"
else
    echo "⚠ node_modules/.bin not found, skipping..."
fi

# 2. Fix Route Cache (skip route:cache if duplicate route exists)
echo ""
echo "2. Clearing route cache..."
php artisan route:clear
echo "✓ Route cache cleared"

# 3. Rebuild Assets
echo ""
echo "3. Rebuilding assets..."
npm run build
echo "✓ Assets rebuilt"

# 4. Clear All Caches
echo ""
echo "4. Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✓ All caches cleared"

# 5. Rebuild Caches (skip route:cache)
echo ""
echo "5. Rebuilding caches (skipping route cache due to duplicate route)..."
php artisan config:cache
php artisan view:cache
echo "✓ Caches rebuilt (route cache skipped)"

# 6. Fix Permissions
echo ""
echo "6. Fixing permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
sudo chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "✓ Permissions fixed"

echo ""
echo "✅ Quick Fix Completed!"
echo ""
echo "Next steps:"
echo "1. Fix duplicate route name in routes/web.php"
echo "2. Run: php artisan route:cache"
echo "3. Restart services: sudo systemctl restart php8.3-fpm nginx"

