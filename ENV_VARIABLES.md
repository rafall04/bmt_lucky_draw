# Environment Variables Documentation

File ini menjelaskan semua environment variables yang digunakan dalam aplikasi BMT Lucky Draw System.

## Cara Menggunakan

1. Copy file ini dan buat file `.env` di root project
2. Isi semua nilai yang diperlukan
3. Untuk production, pastikan `APP_DEBUG=false` dan `APP_ENV=production`

## Template .env

```env
APP_NAME="BMT Lucky Draw"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null
LOG_DEPRECATIONS_TRACE=false
LOG_DAILY_DAYS=14

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bmt_lucky_draw
DB_USERNAME=root
DB_PASSWORD=

# Database SSL (Optional)
DB_SOCKET=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
MYSQL_ATTR_SSL_CA=

# Database URL (Alternative to above)
# DB_URL=

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_CONNECTION=
SESSION_TABLE=sessions
SESSION_STORE=
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=
SESSION_SAME_SITE=lax
SESSION_PARTITIONED_COOKIE=false

# Cache Configuration
FILESYSTEM_DISK=local

# Redis Configuration (Optional)
REDIS_CLIENT=phpredis
REDIS_CLUSTER=redis
REDIS_HOST=127.0.0.1
REDIS_USERNAME=
REDIS_PASSWORD=
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_URL=
REDIS_PREFIX=

# Authentication
AUTH_GUARD=web
AUTH_PASSWORD_BROKER=users
AUTH_MODEL=App\Models\User
AUTH_PASSWORD_RESET_TOKEN_TABLE=password_reset_tokens
AUTH_PASSWORD_TIMEOUT=10800

# Telegram Bot Configuration (Optional)
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=

# Mail Configuration (Optional)
POSTMARK_TOKEN=

# AWS Configuration (Optional)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1

# View Configuration
VIEW_COMPILED_PATH=
```

## Penjelasan Environment Variables

### Application Configuration

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `APP_NAME` | Yes | "BMT Lucky Draw" | Nama aplikasi |
| `APP_ENV` | Yes | production | Environment: `local`, `staging`, `production` |
| `APP_KEY` | Yes | - | Application encryption key (generate dengan `php artisan key:generate`) |
| `APP_DEBUG` | Yes | false | Debug mode. **HARUS false untuk production** |
| `APP_TIMEZONE` | No | Asia/Jakarta | Timezone aplikasi |
| `APP_URL` | Yes | http://localhost | URL aplikasi |

### Database Configuration

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `DB_CONNECTION` | Yes | mysql | Database driver |
| `DB_HOST` | Yes | 127.0.0.1 | Database host |
| `DB_PORT` | No | 3306 | Database port |
| `DB_DATABASE` | Yes | bmt_lucky_draw | Nama database |
| `DB_USERNAME` | Yes | root | Database username |
| `DB_PASSWORD` | Yes | - | Database password |
| `DB_CHARSET` | No | utf8mb4 | Database charset |
| `DB_COLLATION` | No | utf8mb4_unicode_ci | Database collation |

### Session Configuration

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `SESSION_DRIVER` | No | database | Session driver: `file`, `database`, `redis` |
| `SESSION_LIFETIME` | No | 120 | Session lifetime dalam menit |
| `SESSION_SECURE_COOKIE` | No | - | HTTPS only cookies (set `true` untuk production dengan HTTPS) |
| `SESSION_SAME_SITE` | No | lax | SameSite cookie attribute |

### Logging Configuration

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `LOG_CHANNEL` | No | stack | Log channel |
| `LOG_LEVEL` | No | error | Log level: `debug`, `info`, `warning`, `error`, `critical` |
| `LOG_DAILY_DAYS` | No | 14 | Jumlah hari log disimpan |

### Telegram Configuration (Optional)

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `TELEGRAM_BOT_TOKEN` | No | - | Telegram bot token dari BotFather |
| `TELEGRAM_CHAT_ID` | No | - | Telegram chat ID untuk notifikasi |

Lihat [TELEGRAM_SETUP.md](TELEGRAM_SETUP.md) untuk panduan setup Telegram.

### Production Checklist

Sebelum deploy ke production, pastikan:

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `LOG_LEVEL=error` atau `warning`
- [ ] `SESSION_SECURE_COOKIE=true` (jika menggunakan HTTPS)
- [ ] `APP_KEY` sudah di-generate
- [ ] Database credentials sudah benar
- [ ] `DB_PASSWORD` tidak kosong dan kuat

## Security Notes

1. **JANGAN** commit file `.env` ke version control
2. **JANGAN** share file `.env` dengan siapapun
3. Gunakan password yang kuat untuk database
4. Untuk production, gunakan SSL/TLS untuk database connection
5. Set `SESSION_SECURE_COOKIE=true` jika menggunakan HTTPS

