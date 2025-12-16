# Installation Guide

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL Database

## Step-by-Step Installation

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 3. Database Setup

Edit `.env` file and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bmt_lucky_draw
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:

```sql
CREATE DATABASE bmt_lucky_draw;
```

### 4. Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed
```

This will:
- Create all necessary tables
- Create default admin user (email: `admin@bmt.local`, password: `password`)

### 5. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 6. Create Storage Link (Optional)

If you need to store uploaded files:

```bash
php artisan storage:link
```

### 7. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Default Credentials

- **Email**: admin@bmt.local
- **Password**: password

**⚠️ IMPORTANT**: Change the default password after first login!

## Troubleshooting

### Permission Issues

If you encounter permission issues, run:

```bash
chmod -R 775 storage bootstrap/cache
```

### Composer Issues

If composer install fails, try:

```bash
composer install --no-interaction --prefer-dist
```

### NPM Issues

If npm install fails, try:

```bash
npm install --legacy-peer-deps
```

### Database Connection Issues

Make sure:
1. MySQL service is running
2. Database credentials in `.env` are correct
3. Database exists

## Next Steps

1. Login as admin at `/login`
2. Import participant data from Excel (see `EXCEL_TEMPLATE.md`)
3. Start the lucky draw at `/`

