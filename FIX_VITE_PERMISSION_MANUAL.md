# 🔧 Fix: Vite Permission Denied (Persistent)

Jika `vite: Permission denied` masih terjadi setelah `chmod +x`, coba solusi berikut:

## ✅ Solusi Manual

### Method 1: Fix Permission dengan Owner yang Benar

```bash
cd /home/unnet/bmt_lucky_draw

# Check who owns node_modules
ls -la node_modules/.bin/vite

# Fix ownership dan permission
sudo chown -R $(whoami):$(whoami) node_modules/.bin
chmod -R +x node_modules/.bin

# Atau jika node_modules dimiliki root/www-data
sudo chown -R www-data:www-data node_modules/.bin
sudo chmod -R +x node_modules/.bin
```

### Method 2: Gunakan npx Vite

```bash
cd /home/unnet/bmt_lucky_draw

# Instead of npm run build, use npx directly
npx vite build
```

### Method 3: Reinstall Vite

```bash
cd /home/unnet/bmt_lucky_draw

# Reinstall vite
npm install vite --save-dev

# Fix permissions
chmod +x node_modules/.bin/vite || sudo chmod +x node_modules/.bin/vite

# Build
npm run build
```

### Method 4: Reinstall All Node Modules

```bash
cd /home/unnet/bmt_lucky_draw

# Remove node_modules
rm -rf node_modules

# Reinstall (jangan pakai --production karena vite adalah dev dependency)
npm install

# Fix permissions
chmod -R +x node_modules/.bin || sudo chmod -R +x node_modules/.bin

# Build
npm run build
```

### Method 5: Use npm exec

```bash
cd /home/unnet/bmt_lucky_draw

# Use npm exec instead
npm exec vite build
```

## 🔍 Diagnosa

### Check Vite Binary

```bash
# Check if vite exists
ls -la node_modules/.bin/vite

# Check if executable
[ -x "node_modules/.bin/vite" ] && echo "Executable" || echo "NOT Executable"

# Check permissions
stat node_modules/.bin/vite

# Try running directly
./node_modules/.bin/vite --version
```

### Check Node Modules Ownership

```bash
# Check owner
ls -ld node_modules/.bin

# Check all files in .bin
ls -la node_modules/.bin/ | head -20
```

### Check npm/npx

```bash
# Check npm version
npm --version

# Check if npx works
npx vite --version

# Check PATH
which vite
echo $PATH
```

## 🎯 Recommended Fix (Step by Step)

```bash
cd /home/unnet/bmt_lucky_draw

# 1. Check current state
ls -la node_modules/.bin/vite

# 2. Fix ownership (sesuaikan dengan user yang install npm)
# Jika install sebagai root:
sudo chown -R root:root node_modules/.bin
sudo chmod -R +x node_modules/.bin

# Atau jika install sebagai user biasa:
chown -R $(whoami):$(whoami) node_modules/.bin
chmod -R +x node_modules/.bin

# 3. Verify
[ -x "node_modules/.bin/vite" ] && echo "✓ Executable" || echo "✗ NOT Executable"

# 4. Try build
npm run build

# 5. If still fails, use npx
npx vite build
```

## 📝 Catatan

- **Root install**: Jika `npm install` dijalankan sebagai root, `node_modules/.bin` mungkin dimiliki root
- **Permission**: `chmod +x` harus dijalankan dengan user yang sama dengan owner file
- **npx**: `npx` akan menggunakan binary dari `node_modules/.bin` atau download jika tidak ada
- **npm exec**: Alternatif untuk `npx` yang lebih modern

## ⚠️ Prevention

Untuk mencegah masalah ini di masa depan:

1. **Jangan install npm sebagai root** jika mungkin
2. **Gunakan npm install --unsafe-perm** jika harus install sebagai root
3. **Fix permissions immediately** setelah npm install
4. **Use npx** sebagai fallback jika binary tidak executable

---

**Setelah fix, test dengan: `npm run build` atau `npx vite build`**

