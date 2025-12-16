#!/bin/bash
# Quick Fix untuk Vite Permission - VERSION 2
# Jalankan: bash FIX_VITE_NOW.sh

cd /home/unnet/bmt_lucky_draw || exit 1

echo "🔧 Fixing Vite Permission (Comprehensive Fix)"
echo "=============================================="
echo ""

# Check current state
echo "1. Checking current state..."
if [ -f "node_modules/.bin/vite" ]; then
    echo "   ✓ vite binary found"
    ls -la node_modules/.bin/vite
else
    echo "   ✗ vite binary NOT found"
fi

# Fix ownership (most likely issue)
echo ""
echo "2. Fixing ownership..."
# Get current user
CURRENT_USER=$(whoami)
CURRENT_GROUP=$(id -gn)

echo "   Current user: $CURRENT_USER"
echo "   Current group: $CURRENT_GROUP"

# Fix ownership recursively
if [ -d "node_modules/.bin" ]; then
    sudo chown -R "$CURRENT_USER:$CURRENT_GROUP" node_modules/.bin
    echo "   ✓ Ownership fixed to $CURRENT_USER:$CURRENT_GROUP"
else
    echo "   ⚠ node_modules/.bin not found"
fi

# Fix permissions
echo ""
echo "3. Fixing permissions..."
if [ -d "node_modules/.bin" ]; then
    chmod -R +x node_modules/.bin
    echo "   ✓ Permissions set to executable"
    
    # Specifically fix vite if exists
    if [ -f "node_modules/.bin/vite" ]; then
        chmod +x node_modules/.bin/vite
        echo "   ✓ vite binary specifically set to executable"
        
        # Verify
        if [ -x "node_modules/.bin/vite" ]; then
            echo "   ✓ Verification: vite IS executable"
        else
            echo "   ✗ Verification: vite is still NOT executable"
            echo "   Trying with sudo..."
            sudo chmod +x node_modules/.bin/vite
        fi
    fi
else
    echo "   ⚠ node_modules/.bin not found"
fi

# Try direct execution test
echo ""
echo "4. Testing vite binary..."
if [ -f "node_modules/.bin/vite" ]; then
    if ./node_modules/.bin/vite --version 2>/dev/null; then
        echo "   ✓ vite binary works directly!"
    else
        echo "   ✗ vite binary still doesn't work"
        echo "   Will use npx as fallback"
    fi
fi

# Build with multiple strategies
echo ""
echo "5. Building assets..."

# Strategy 1: npm run build
echo "   Trying: npm run build"
if npm run build 2>&1; then
    echo "   ✓ Build successful with npm run build"
    exit 0
fi

# Strategy 2: npx vite build
echo ""
echo "   Trying: npx vite build"
if npx vite build 2>&1; then
    echo "   ✓ Build successful with npx vite build"
    exit 0
fi

# Strategy 3: npm exec vite build
echo ""
echo "   Trying: npm exec vite build"
if npm exec vite build 2>&1; then
    echo "   ✓ Build successful with npm exec vite build"
    exit 0
fi

# Strategy 4: Reinstall vite
echo ""
echo "   Trying: Reinstall vite and rebuild"
npm install vite --save-dev 2>&1 || true

# Fix again after reinstall
if [ -f "node_modules/.bin/vite" ]; then
    chmod +x node_modules/.bin/vite || sudo chmod +x node_modules/.bin/vite || true
    chown "$CURRENT_USER:$CURRENT_GROUP" node_modules/.bin/vite 2>/dev/null || sudo chown "$CURRENT_USER:$CURRENT_GROUP" node_modules/.bin/vite 2>/dev/null || true
fi

# Try build again
if npm run build 2>&1; then
    echo "   ✓ Build successful after reinstall"
    exit 0
fi

# Final failure
echo ""
echo "❌ All build strategies failed"
echo ""
echo "Please try manually:"
echo "1. Check ownership: ls -la node_modules/.bin/vite"
echo "2. Fix ownership: sudo chown -R $(whoami):$(whoami) node_modules/.bin"
echo "3. Fix permissions: chmod -R +x node_modules/.bin"
echo "4. Try: npx vite build"
exit 1

