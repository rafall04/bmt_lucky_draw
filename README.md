# BMT Lucky Draw System

Sistem undian berhadiah untuk BMT NU dengan fitur anti-double winner dan anti-race condition.

## Tech Stack

- **Framework**: Laravel 11
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Interactive**: Livewire 3
- **Database**: MySQL
- **Excel Library**: maatwebsite/excel

## Installation

1. Clone repository atau extract project
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Copy environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure database in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bmt_lucky_draw
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Run migrations and seeders:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. Build frontend assets:
   ```bash
   npm run dev
   # or for production
   npm run build
   ```

8. Start development server:
   ```bash
   php artisan serve
   ```

## Default Admin Credentials

- **Email**: admin@bmt.local
- **Password**: password

## Features

### Phase 1: Database & Model Setup
- Migration dan Model untuk tabel `pesertas`
- Unique constraint pada `no_rekening` untuk mencegah duplikasi
- Index pada `status_menang` untuk performa query

### Phase 2: Authentication & Admin Backend
- Login system dengan Laravel authentication
- Admin dashboard dengan statistik
- Import data peserta dari Excel (xlsx, xls)
- Reset semua pemenang dengan password confirmation

### Phase 3: Core Logic (The Spinner Engine)
- Livewire component untuk undian
- Anti-double winner dengan DB transaction dan `lockForUpdate()`
- Anti-race condition dengan double-check sebelum update
- Reset display tanpa mengubah database

### Phase 4: Frontend UI
- Green BMT NU theme dengan gradient background
- Alpine.js untuk rolling animation effect
- Control buttons: START, STOP, SIMPAN, BATAL
- Dropdown untuk memilih kategori hadiah
- Responsive design dengan Tailwind CSS

### Phase 5: Routing & Middleware
- Public route untuk halaman undian (`/`)
- Protected admin routes dengan middleware `auth`
- Admin routes: Dashboard, Import, Reset

## Usage

### Import Data Peserta

1. Login sebagai admin di `/login`
2. Di dashboard admin, upload file Excel dengan format:
   - Kolom: `no_rekening`, `nama`, `alamat`, `cabang`
   - File harus berformat `.xlsx` atau `.xls`

### Melakukan Undian

1. Buka halaman utama (`/`)
2. Klik tombol **START** untuk memulai rolling animation
3. Klik tombol **STOP** untuk memilih pemenang
4. Pilih kategori hadiah dari dropdown
5. Klik **SIMPAN** untuk menyimpan pemenang ke database
6. Atau klik **BATAL** untuk membatalkan dan memilih ulang

### Reset Pemenang

1. Login sebagai admin
2. Di dashboard, masukkan password: `RESET_CONFIRM`
3. Klik **Reset Pemenang** untuk mereset semua status pemenang

## Security Features

- **Anti-Double Winner**: Menggunakan DB transaction dengan `lockForUpdate()` untuk mencegah pemenang ganda
- **Anti-Race Condition**: Double-check status sebelum update untuk mencegah race condition
- **Password Protection**: Reset pemenang memerlukan password confirmation
- **Authentication**: Admin routes dilindungi dengan middleware `auth`

## Code Style & Best Practices

Project ini mengikuti standar coding dan best practices yang ketat:

### Quick Commands

```bash
# Format PHP code
composer format

# Check code formatting
composer format:test

# Run static analysis
composer analyse

# Format JavaScript
npm run format

# Lint JavaScript
npm run lint
```

### Documentation

- **[CODE_STYLE.md](CODE_STYLE.md)** - Panduan lengkap code style dan best practices
- **[RULES_SUMMARY.md](RULES_SUMMARY.md)** - Ringkasan cepat rules dan conventions
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Panduan untuk kontributor

### Tools Used

- **Laravel Pint** - PHP code formatter (PSR-12)
- **PHPStan** - PHP static analysis
- **ESLint** - JavaScript linting
- **Prettier** - Code formatter
- **EditorConfig** - Editor consistency

## License

MIT

