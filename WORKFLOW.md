# 📋 BMT Lucky Draw System - Workflow Lengkap

Dokumentasi workflow lengkap untuk sistem undian berhadiah BMT NU Temayang.

---

## 📑 Daftar Isi

1. [Overview Sistem](#overview-sistem)
2. [Workflow Instalasi & Setup](#workflow-instalasi--setup)
3. [Workflow Database](#workflow-database)
4. [Workflow Authentication](#workflow-authentication)
5. [Workflow Undian (Spinner)](#workflow-undian-spinner)
6. [Workflow Admin Dashboard](#workflow-admin-dashboard)
7. [Workflow Import Peserta](#workflow-import-peserta)
8. [Workflow Reset Pemenang](#workflow-reset-pemenang)
9. [Workflow Manajemen User](#workflow-manajemen-user)
10. [Workflow Technical (Backend)](#workflow-technical-backend)
11. [Workflow Frontend (Alpine.js & Livewire)](#workflow-frontend-alpinejs--livewire)
12. [Troubleshooting Workflow](#troubleshooting-workflow)

---

## 🎯 Overview Sistem

### Arsitektur Sistem
```
┌─────────────────────────────────────────────────────────────┐
│                    BMT Lucky Draw System                     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐         ┌──────────────┐                  │
│  │   Frontend   │◄───────►│   Backend    │                  │
│  │              │         │              │                  │
│  │ • Blade      │         │ • Laravel    │                  │
│  │ • Alpine.js  │         │ • Livewire   │                  │
│  │ • Tailwind   │         │ • Eloquent   │                  │
│  └──────────────┘         └──────┬───────┘                  │
│                                   │                           │
│                          ┌────────▼────────┐                 │
│                          │    Database     │                 │
│                          │     MySQL       │                 │
│                          └─────────────────┘                 │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Komponen Utama
1. **Public Interface**: Halaman undian untuk semua pengunjung
2. **Admin Panel**: Dashboard untuk mengelola peserta, pemenang, dan user
3. **Database**: Menyimpan data peserta, pemenang, dan user admin
4. **Import System**: Import peserta dari file Excel

---

## 🚀 Workflow Instalasi & Setup

### Step 1: Persiapan Environment
```
1. Pastikan PHP 8.2+ terinstall
2. Pastikan Composer terinstall
3. Pastikan Node.js 20+ terinstall
4. Pastikan MySQL/MariaDB terinstall
5. Pastikan extension PHP aktif:
   - fileinfo
   - gd
   - zip
   - pdo_mysql
```

### Step 2: Clone/Download Project
```bash
# Jika dari repository
git clone <repository-url>
cd bmtnu

# Atau extract dari zip file
```

### Step 3: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 4: Konfigurasi Environment
```bash
# Copy file .env
copy .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file:
# - DB_DATABASE=bmt_lucky_draw
# - DB_USERNAME=root
# - DB_PASSWORD=
# - APP_URL=http://localhost:8000
```

### Step 5: Setup Database
```bash
# Buat database
mysql -u root -p
CREATE DATABASE bmt_lucky_draw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate

# Run seeders (optional, untuk data dummy)
php artisan db:seed
```

### Step 6: Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### Step 7: Start Server
```bash
# Development server
php artisan serve

# Atau dengan port tertentu
php artisan serve --port=8000
```

### Step 8: Akses Aplikasi
```
Public: http://localhost:8000
Admin:  http://localhost:8000/login
        Email: admin@bmt.local
        Password: password
```

---

## 💾 Workflow Database

### Struktur Database

#### Tabel: `pesertas`
```sql
CREATE TABLE pesertas (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    no_rekening VARCHAR(255) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    alamat TEXT NOT NULL,
    cabang VARCHAR(255) NULLABLE,
    status_menang BOOLEAN DEFAULT 0,
    hadiah_didapat VARCHAR(255) NULLABLE,
    waktu_menang TIMESTAMP NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status_menang (status_menang),
    INDEX idx_no_rekening (no_rekening)
);
```

#### Tabel: `users`
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Tabel: `cache`
```sql
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INTEGER NOT NULL,
    INDEX idx_expiration (expiration)
);
```

### Flow Database Operations

#### 1. Insert Peserta (Import)
```
Excel File → PesertaImport → Validation → 
Check Duplicate (no_rekening) → Insert to DB → 
Return Success/Error
```

#### 2. Select Winner
```
Query: WHERE status_menang = 0 → 
Random Selection → 
Lock Row (lockForUpdate) → 
Update status_menang = 1, waktu_menang = NOW() → 
Commit Transaction
```

#### 3. Update Hadiah
```
Select Winner → Update hadiah_didapat → 
Save to DB
```

#### 4. Reset Pemenang
```
Transaction Start → 
UPDATE ALL SET status_menang = 0, 
hadiah_didapat = NULL, waktu_menang = NULL → 
Commit Transaction
```

---

## 🔐 Workflow Authentication

### Flow Login Admin

```
┌─────────────┐
│   User      │
│  Access     │
│  /login     │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│ Check if        │
│ Already Auth?   │
└──────┬──────────┘
       │
       ├── YES ──► Redirect to /admin/dashboard
       │
       └── NO ──► Show Login Form
                  │
                  ▼
            ┌─────────────┐
            │ User Input  │
            │ Credentials │
            └──────┬──────┘
                   │
                   ▼
            ┌──────────────────┐
            │ Validate Input   │
            │ (email, password)│
            └──────┬───────────┘
                   │
                   ▼
            ┌──────────────────┐
            │ Check Database   │
            │ (Auth::attempt)  │
            └──────┬───────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
        ▼                     ▼
   ┌─────────┐         ┌──────────────┐
   │ Invalid │         │ Valid        │
   │ Show    │         │ Regenerate   │
   │ Error   │         │ Session      │
   └─────────┘         └──────┬───────┘
                              │
                              ▼
                       ┌──────────────┐
                       │ Redirect to  │
                       │ /admin/      │
                       │ dashboard    │
                       └──────────────┘
```

### Flow Logout

```
User Click Logout → 
POST /logout → 
Auth::logout() → 
Session Invalidate → 
Session Regenerate Token → 
Clear url.intended → 
Redirect to /login
```

### Middleware Protection

```
Route: /admin/* → 
Middleware: auth → 
Check: Is User Authenticated? → 
  YES: Continue → 
  NO: Redirect to /login (with intended URL)
```

---

## 🎰 Workflow Undian (Spinner)

### Flow Lengkap Undian

```
┌─────────────────────────────────────────────────────────────┐
│                    UNDIAN WORKFLOW                           │
└─────────────────────────────────────────────────────────────┘

1. USER ACCESS PAGE (/)
   │
   ├─► Livewire Component: Undian
   │   │
   │   ├─► render() method called
   │   │   │
   │   │   ├─► getDummyData() → Query DB (50 random non-winners)
   │   │   │   │
   │   │   │   └─► Cache result (60 seconds)
   │   │   │
   │   │   └─► Return view with dummyData
   │   │
   │   └─► Alpine.js init()
   │       │
   │       ├─► Parse dummyData from data-dummy attribute
   │       ├─► Shuffle dummyData array
   │       └─► Setup event listeners
   │
   ▼
2. USER CLICK START BUTTON
   │
   ├─► Alpine.js: startRolling()
   │   │
   │   ├─► Cleanup any existing interval (from global storage)
   │   ├─► Set isStopped = false
   │   ├─► Set rolling = true
   │   ├─► Reset display to '---'
   │   ├─► Shuffle dummyData
   │   └─► Create setInterval (50ms)
   │       │
   │       └─► Interval Callback (every 50ms):
   │           │
   │           ├─► Check: isStopped? → YES: Clear interval & return
   │           ├─► Check: rolling? → NO: Clear interval & return
   │           └─► Update display with next dummyData item
   │
   ▼
3. USER CLICK STOP BUTTON
   │
   ├─► Alpine.js: handleStopClick()
   │   │
   │   ├─► Check: isStopped or !rolling? → YES: Return (prevent double click)
   │   ├─► Call stopRolling()
   │   │   │
   │   │   ├─► Set isStopped = true
   │   │   ├─► Set rolling = false
   │   │   ├─► Clear local intervalId
   │   │   └─► Clear interval from global storage
   │   │
   │   └─► Call Livewire: pickWinner()
   │       │
   │       ├─► Check: is_processing? → YES: Return
   │       ├─► Set is_processing = true
   │       ├─► Set is_rolling = false
   │       ├─► Dispatch event: 'stop-rolling-animation'
   │       │   │
   │       │   └─► Alpine.js: stopRolling() (double safety)
   │       │
   │       └─► Database Transaction:
   │           │
   │           ├─► BEGIN TRANSACTION
   │           │
   │           ├─► Query: Get MIN(id), MAX(id), COUNT(*) 
   │           │   WHERE status_menang = 0
   │           │
   │           ├─► Random Selection Algorithm:
   │           │   │
   │           │   ├─► Generate random ID between MIN and MAX
   │           │   ├─► Query: WHERE id >= randomId AND status_menang = 0
   │           │   ├─► Lock row: lockForUpdate()
   │           │   └─► If not found, try from MIN(id)
   │           │
   │           ├─► Fallback: inRandomOrder() if range method fails
   │           │
   │           ├─► Update Selected Participant:
   │           │   │
   │           │   ├─► status_menang = 1
   │           │   ├─► waktu_menang = NOW()
   │           │   └─► Save to DB
   │           │
   │           ├─► Verify Update (before commit)
   │           │
   │           ├─► COMMIT TRANSACTION
   │           │
   │           ├─► Wait 100ms (usleep) for DB processing
   │           │
   │           ├─► Verify Update (after commit) - Raw Query
   │           │
   │           ├─► Verify Update (after commit) - Eloquent Query
   │           │
   │           ├─► Clear cache: undian_dummy_data
   │           │
   │           ├─► Set pemenang = selectedPeserta
   │           │
   │           └─► Dispatch event: 'winner-selected'
   │               │
   │               └─► Alpine.js: handleWinnerSelected()
   │                   │
   │                   └─► updateDisplayFromPemenang()
   │                       │
   │                       ├─► Set isStopped = true
   │                       └─► Update display with winner data
   │
   ▼
4. DISPLAY WINNER
   │
   ├─► Show winner information box
   ├─► Show prize selection dropdown
   ├─► Show SIMPAN and REFRESH buttons
   │
   ▼
5. USER SELECT PRIZE (Optional)
   │
   ├─► Select from dropdown
   │
   ▼
6. USER CLICK SIMPAN
   │
   ├─► Livewire: saveWinner()
   │   │
   │   ├─► Validate: pemenang exists?
   │   ├─► Validate: hadiah_selected not empty?
   │   └─► Update: hadiah_didapat = selected prize
   │
   ▼
7. USER CLICK REFRESH (Optional)
   │
   ├─► Livewire: resetDisplay()
   │   │
   │   ├─► Set pemenang = null
   │   ├─► Set hadiah_selected = ''
   │   └─► Set is_rolling = false
   │
   └─► Alpine.js: Reset display to '---'
```

### Race Condition Prevention

```
┌─────────────────────────────────────────┐
│  RACE CONDITION PREVENTION              │
└─────────────────────────────────────────┘

1. Multiple Click Prevention:
   ├─► is_processing flag (Livewire)
   ├─► isStopped flag (Alpine.js)
   └─► Button disabled state

2. Interval Cleanup:
   ├─► Global storage: window.undianIntervals
   ├─► Cleanup on init()
   ├─► Cleanup on stopRolling()
   └─► Cleanup on component destroy

3. Database Lock:
   ├─► lockForUpdate() on selected row
   ├─► Transaction isolation
   └─► Verify after commit

4. Double Winner Prevention:
   ├─► Check status_menang before update
   ├─► Lock row during selection
   └─► Verify after commit
```

---

## 📊 Workflow Admin Dashboard

### Flow Dashboard Access

```
User Login → 
Redirect to /admin/dashboard → 
PesertaController@index → 
Query Statistics:
  ├─► Total Peserta: COUNT(*)
  ├─► Total Pemenang: COUNT(*) WHERE status_menang = 1
  ├─► Remaining: COUNT(*) WHERE status_menang = 0
  ├─► Recent Winners: ORDER BY waktu_menang DESC LIMIT 10
  └─► Prize Stats: GROUP BY hadiah_didapat
→ 
Render Dashboard View
```

### Dashboard Features

1. **Statistics Cards**
   - Total Peserta
   - Total Pemenang
   - Sisa Kandidat

2. **Recent Winners Table**
   - 10 pemenang terakhir
   - No Rekening, Nama, Hadiah, Waktu Menang

3. **Prize Statistics**
   - Jumlah per hadiah
   - Visual representation

4. **Quick Actions**
   - Import Peserta
   - Reset Pemenang
   - View All Winners

---

## 📥 Workflow Import Peserta

### Flow Import Excel

```
┌─────────────────────────────────────────────────────────────┐
│                    IMPORT WORKFLOW                           │
└─────────────────────────────────────────────────────────────┘

1. ADMIN ACCESS IMPORT
   │
   ├─► Go to Dashboard
   └─► Click "Import Peserta" button
       │
       ▼
2. SELECT EXCEL FILE
   │
   ├─► File Requirements:
   │   ├─► Format: .xlsx or .xls
   │   ├─► Max Size: 10MB
   │   └─► Columns:
   │       ├─► no_rekening (required, unique)
   │       ├─► nama (required)
   │       ├─► alamat (required)
   │       └─► cabang (optional)
   │
   ▼
3. UPLOAD FILE
   │
   ├─► POST /admin/import
   │   │
   │   ├─► Validation:
   │   │   ├─► File exists?
   │   │   ├─► File type valid?
   │   │   └─► File size < 10MB?
   │   │
   │   └─► Excel::import(new PesertaImport, file)
   │       │
   │       └─► PesertaImport Class:
   │           │
   │           ├─► For each row:
   │           │   │
   │           │   ├─► Validate row data
   │           │   │
   │           │   ├─► Check duplicate:
   │           │   │   └─► Peserta::where('no_rekening', $no_rekening)->exists()
   │           │   │
   │           │   ├─► If duplicate:
   │           │   │   └─► Skip row (continue)
   │           │   │
   │           │   └─► If not duplicate:
   │           │       └─► Peserta::create([...])
   │           │
   │           └─► Return import result
   │
   ▼
4. DISPLAY RESULT
   │
   ├─► Success: Redirect with success message
   └─► Error: Redirect with error message
```

### Excel Format

```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ no_rekening  │    nama      │   alamat     │   cabang     │
├──────────────┼──────────────┼──────────────┼──────────────┤
│ 000000000001 │ John Doe     │ Jl. ABC 123  │ CABANG PUSAT │
│ 000000000002 │ Jane Smith   │ Jl. XYZ 456  │ CABANG SOKO  │
│ ...          │ ...          │ ...          │ ...          │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

---

## 🔄 Workflow Reset Pemenang

### Flow Reset

```
┌─────────────────────────────────────────────────────────────┐
│                  RESET PEMENANG WORKFLOW                     │
└─────────────────────────────────────────────────────────────┘

1. ADMIN ACCESS RESET
   │
   ├─► Go to Dashboard
   └─► Click "Reset Pemenang" button
       │
       ▼
2. CONFIRMATION DIALOG
   │
   ├─► Enter Password: "RESET_CONFIRM"
   │
   ▼
3. SUBMIT RESET
   │
   ├─► POST /admin/reset-pemenang
   │   │
   │   ├─► Validate password
   │   │
   │   └─► DB Transaction:
   │       │
   │       ├─► BEGIN TRANSACTION
   │       │
   │       ├─► UPDATE pesertas SET
   │       │   status_menang = 0,
   │       │   hadiah_didapat = NULL,
   │       │   waktu_menang = NULL
   │       │
   │       └─► COMMIT TRANSACTION
   │
   ▼
4. CLEAR CACHE
   │
   └─► Cache::forget('undian_dummy_data')
```

**⚠️ WARNING**: Reset akan menghapus semua data pemenang!

---

## 👥 Workflow Manajemen User

### Flow CRUD User

#### Create User
```
Admin → /admin/users → Click "Tambah User" → 
Fill Form (name, email, password, password_confirmation) → 
POST /admin/users → 
Validation → 
Hash Password → 
User::create() → 
Redirect with success message
```

#### Read Users
```
Admin → /admin/users → 
UserController@index → 
Query: User::paginate(10) → 
Display table with pagination
```

#### Update User
```
Admin → /admin/users → Click "Edit" → 
Fill Form (name, email, password optional) → 
POST /admin/users/{id} → 
Validation → 
If password filled: Hash password → 
User::update() → 
Redirect with success message
```

#### Delete User
```
Admin → /admin/users → Click "Hapus" → 
Confirm → 
Check: User count > 1? → 
  YES: User::delete() → 
  NO: Show error (cannot delete last user)
```

---

## ⚙️ Workflow Technical (Backend)

### Livewire Component Lifecycle

```
┌─────────────────────────────────────────┐
│  LIVEWIRE COMPONENT LIFECYCLE           │
└─────────────────────────────────────────┘

1. mount() - First render
   │
   ├─► Initialize public properties
   └─► Load initial data

2. render() - Every update
   │
   ├─► Get dummyData from cache/DB
   └─► Return view

3. Property Update
   │
   ├─► User action → Update property
   ├─► Livewire detects change
   └─► Re-render component

4. Method Call
   │
   ├─► User action → Call method
   ├─► Execute method logic
   ├─► Update properties
   └─► Re-render component

5. Event Dispatch
   │
   ├─► $this->dispatch('event-name', data)
   └─► Alpine.js listens via x-on:event-name.window
```

### Database Transaction Flow

```
┌─────────────────────────────────────────┐
│  TRANSACTION FLOW (pickWinner)          │
└─────────────────────────────────────────┘

BEGIN TRANSACTION
│
├─► Query eligible participants
│   └─► WHERE status_menang = 0
│
├─► Random selection
│   └─► lockForUpdate() on selected row
│
├─► Update selected participant
│   ├─► status_menang = 1
│   ├─► waktu_menang = NOW()
│   └─► save()
│
├─► Verify update (before commit)
│
└─► COMMIT TRANSACTION
    │
    ├─► Wait 100ms (usleep)
    │
    ├─► Verify update (after commit)
    │   ├─► Raw query check
    │   └─► Eloquent query check
    │
    └─► Return success
```

### Cache Strategy

```
┌─────────────────────────────────────────┐
│  CACHE STRATEGY                         │
└─────────────────────────────────────────┘

1. Dummy Data Cache
   ├─► Key: 'undian_dummy_data'
   ├─► TTL: 60 seconds
   ├─► Content: 50 random non-winners
   └─► Clear: After winner selected

2. Cache Usage
   ├─► Get: Cache::remember()
   ├─► Clear: Cache::forget()
   └─► Purpose: Reduce DB queries
```

---

## 🎨 Workflow Frontend (Alpine.js & Livewire)

### Alpine.js Component Lifecycle

```
┌─────────────────────────────────────────┐
│  ALPINE.JS COMPONENT LIFECYCLE          │
└─────────────────────────────────────────┘

1. init()
   │
   ├─► Get componentId from wire:id
   ├─► Cleanup existing intervals
   ├─► Parse dummyData from data-dummy
   ├─► Setup event listeners
   └─► Setup cleanup on destroy

2. startRolling()
   │
   ├─► Cleanup previous interval
   ├─► Reset flags
   ├─► Create setInterval
   └─► Store intervalId globally

3. stopRolling()
   │
   ├─► Set isStopped = true
   ├─► Set rolling = false
   ├─► Clear local interval
   └─► Clear global interval

4. Event Listeners
   │
   ├─► 'stop-rolling-animation' → stopRolling()
   └─► 'winner-selected' → updateDisplayFromPemenang()
```

### Livewire-Alpine.js Communication

```
┌─────────────────────────────────────────┐
│  LIVEWIRE ↔ ALPINE.JS                   │
└─────────────────────────────────────────┘

1. Livewire → Alpine.js
   │
   ├─► Event Dispatch:
   │   └─► $this->dispatch('event-name', data)
   │
   └─► Alpine.js Listen:
       └─► x-on:event-name.window="handler($event)"

2. Alpine.js → Livewire
   │
   ├─► Method Call:
   │   └─► window.Livewire.find(id).call('method')
   │
   └─► Property Access:
       └─► @js($property) in Alpine.js
```

### Global Storage Pattern

```
┌─────────────────────────────────────────┐
│  GLOBAL STORAGE (Window Object)         │
└─────────────────────────────────────────┘

Purpose: Persist data across Livewire re-renders

1. window.undianIntervals
   ├─► Key: componentId
   ├─► Value: intervalId
   └─► Purpose: Track intervals for cleanup

2. window.undianEventHandlers
   ├─► Key: componentId
   ├─► Value: event handler function
   └─► Purpose: Remove event listeners on cleanup
```

---

## 🔧 Troubleshooting Workflow

### Common Issues & Solutions

#### 1. Interval Masih Berjalan Setelah STOP
```
Problem: Spinner masih berputar setelah klik STOP
Solution:
  ├─► Check: Global storage cleanup
  ├─► Check: isStopped flag
  ├─► Check: Event listener cleanup
  └─► Check: Component destroy handler
```

#### 2. Data Pemenang Tidak Tersimpan
```
Problem: Pemenang tidak muncul di admin dashboard
Solution:
  ├─► Check: Transaction commit
  ├─► Check: status_menang = 1 (not true)
  ├─► Check: waktu_menang is set
  └─► Check: Database connection
```

#### 3. Import Gagal
```
Problem: File Excel tidak bisa diimport
Solution:
  ├─► Check: File format (.xlsx or .xls)
  ├─► Check: File size < 10MB
  ├─► Check: Column names match
  └─► Check: no_rekening is unique
```

#### 4. Login Redirect Loop
```
Problem: Redirect ke login terus menerus
Solution:
  ├─► Check: Session configuration
  ├─► Check: url.intended cleanup
  └─► Check: Middleware order
```

#### 5. Alpine.js Error
```
Problem: displayNo is not defined
Solution:
  ├─► Check: x-data initialization
  ├─► Check: JSON parsing in init()
  └─► Check: Component re-render
```

---

## 📝 Best Practices

### 1. Database
- ✅ Always use transactions for critical operations
- ✅ Use lockForUpdate() to prevent race conditions
- ✅ Verify updates after commit
- ✅ Use indexes for frequently queried columns

### 2. Frontend
- ✅ Cleanup intervals on component destroy
- ✅ Use global storage for cross-render persistence
- ✅ Prevent double clicks with flags
- ✅ Handle errors gracefully

### 3. Security
- ✅ Validate all user inputs
- ✅ Use password protection for destructive actions
- ✅ Protect admin routes with middleware
- ✅ Escape output in Blade templates

### 4. Performance
- ✅ Cache frequently accessed data
- ✅ Use efficient queries (avoid N+1)
- ✅ Limit dummy data size
- ✅ Use pagination for large datasets

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

---

**Last Updated**: December 2024
**Version**: 1.0.0

