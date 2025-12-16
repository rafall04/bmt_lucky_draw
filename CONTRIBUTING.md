# Contributing Guide

Terima kasih atas minat Anda untuk berkontribusi pada BMT Lucky Draw System!

## 🚀 Getting Started

1. Fork repository
2. Clone repository Anda
3. Install dependencies:
   ```bash
   composer install
   npm install
   ```
4. Copy `.env.example` ke `.env` dan konfigurasi
5. Generate key: `php artisan key:generate`
6. Run migrations: `php artisan migrate`

## 📝 Development Workflow

### 1. Create Feature Branch

```bash
git checkout -b feature/nama-fitur
```

### 2. Make Changes

- Ikuti [Code Style Guide](CODE_STYLE.md)
- Tulis kode yang clean dan readable
- Tambahkan comments jika perlu
- Update documentation jika ada perubahan

### 3. Format Code

Sebelum commit, format kode Anda:

```bash
# Format PHP
./vendor/bin/pint

# Format JavaScript (jika ada)
npm run lint --fix
```

### 4. Run Tests

```bash
php artisan test
```

### 5. Commit Changes

Gunakan conventional commits:

```bash
git commit -m "feat: add new feature"
git commit -m "fix: resolve bug"
git commit -m "docs: update documentation"
```

### 6. Push & Create Pull Request

```bash
git push origin feature/nama-fitur
```

## ✅ Pre-Commit Checklist

- [ ] Code sudah di-format dengan Pint
- [ ] Tidak ada error atau warning
- [ ] Tests passing
- [ ] Documentation updated
- [ ] Tidak ada sensitive data (credentials, API keys)
- [ ] Migration files sudah dibuat jika ada perubahan database

## 🐛 Reporting Bugs

Saat melaporkan bug, sertakan:

1. Deskripsi bug yang jelas
2. Langkah-langkah untuk reproduce
3. Expected behavior
4. Actual behavior
5. Screenshots (jika ada)
6. Environment (PHP version, Laravel version, dll)

## 💡 Suggesting Features

Saat menyarankan fitur baru:

1. Jelaskan use case
2. Berikan contoh implementasi jika memungkinkan
3. Jelaskan manfaat untuk project

## 📋 Code Review Process

1. Semua PR akan di-review
2. Reviewer mungkin meminta perubahan
3. Setelah approved, PR akan di-merge

## 🙏 Thank You!

Terima kasih telah berkontribusi!

