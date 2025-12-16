# Code Style Guide - BMT Lucky Draw System

Panduan ini menjelaskan standar coding dan best practices yang harus diikuti dalam pengembangan project ini.

## 📋 Daftar Isi

1. [PHP/Laravel Standards](#php-laravel-standards)
2. [JavaScript Standards](#javascript-standards)
3. [Blade Templates](#blade-templates)
4. [Database & Migrations](#database--migrations)
5. [Git Workflow](#git-workflow)
6. [Naming Conventions](#naming-conventions)
7. [Security Best Practices](#security-best-practices)

---

## PHP/Laravel Standards

### Code Style

- Gunakan **Laravel Pint** untuk formatting otomatis
- Jalankan sebelum commit: `./vendor/bin/pint`
- Ikuti **PSR-12** coding standard
- Gunakan **4 spaces** untuk indentation (bukan tabs)

### Naming Conventions

```php
// Classes: PascalCase
class PesertaController extends Controller {}

// Methods: camelCase
public function importPeserta() {}

// Variables: camelCase
$totalPeserta = 0;

// Constants: UPPER_SNAKE_CASE
const MAX_FILE_SIZE = 10240;

// Database tables: snake_case, plural
pesertas, users, password_reset_tokens

// Database columns: snake_case
no_rekening, status_menang, waktu_menang
```

### Controller Best Practices

```php
// ✅ GOOD: Single Responsibility, Thin Controllers
public function index()
{
    $pesertas = Peserta::paginate(20);
    return view('admin.dashboard', compact('pesertas'));
}

// ❌ BAD: Fat Controllers dengan logic kompleks
public function index()
{
    // Jangan taruh business logic di controller
    // Pindahkan ke Service/Repository
}
```

### Model Best Practices

```php
// ✅ GOOD: Gunakan fillable, casts, relationships
class Peserta extends Model
{
    protected $fillable = ['no_rekening', 'nama'];
    
    protected $casts = [
        'status_menang' => 'boolean',
        'waktu_menang' => 'datetime',
    ];
}

// ✅ GOOD: Gunakan Scopes untuk query yang sering digunakan
public function scopeBelumMenang($query)
{
    return $query->where('status_menang', 0);
}
```

### Database Transactions

```php
// ✅ GOOD: Selalu gunakan transaction untuk operasi kritis
DB::transaction(function () {
    $peserta = Peserta::lockForUpdate()->find($id);
    if ($peserta->status_menang == 0) {
        $peserta->update(['status_menang' => 1]);
    }
});

// ❌ BAD: Tanpa transaction untuk operasi penting
$peserta->update(['status_menang' => 1]);
```

### Error Handling

```php
// ✅ GOOD: Gunakan try-catch dengan pesan yang jelas
try {
    Excel::import(new PesertaImport, $file);
    return redirect()->back()->with('success', 'Import berhasil!');
} catch (\Exception $e) {
    \Log::error('Import error: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
}

// ❌ BAD: Silent failures
Excel::import(new PesertaImport, $file);
```

---

## JavaScript Standards

### Code Style

- Gunakan **ESLint** untuk linting
- Gunakan **Prettier** untuk formatting
- Gunakan **2 spaces** untuk indentation
- Gunakan **single quotes** untuk strings

### Alpine.js Best Practices

```javascript
// ✅ GOOD: Struktur yang jelas dan readable
<div x-data="{
    rolling: false,
    displayNo: '---',
    
    startRolling() {
        this.rolling = true;
        // logic here
    }
}">

// ❌ BAD: Logic terlalu kompleks di inline
<div x-data="{ rolling: false, startRolling() { /* 50 lines of code */ } }">
```

### Livewire Integration

```javascript
// ✅ GOOD: Gunakan @this untuk komunikasi dengan Livewire
@this.pickWinner()

// ✅ GOOD: Gunakan @js untuk passing data dari PHP ke JS
x-effect="if (@js($pemenang)) { displayNo = @js($pemenang->no_rekening); }"
```

---

## Blade Templates

### Best Practices

```blade
{{-- ✅ GOOD: Gunakan komentar Blade --}}
@if ($pemenang)
    <div>{{ $pemenang->nama }}</div>
@endif

{{-- ❌ BAD: HTML comments yang tidak perlu --}}
<!-- @if ($pemenang) -->
```

### Component Usage

```blade
{{-- ✅ GOOD: Gunakan components untuk reusability --}}
<x-app-layout>
    <x-slot name="header">
        <h2>Dashboard</h2>
    </x-slot>
</x-app-layout>
```

### Security

```blade
{{-- ✅ GOOD: Escape output otomatis --}}
{{ $user->name }}

{{-- ✅ GOOD: Raw output hanya jika benar-benar perlu --}}
{!! $htmlContent !!}

{{-- ❌ BAD: Jangan gunakan {!! !!} untuk user input --}}
{!! $userInput !!}
```

---

## Database & Migrations

### Migration Best Practices

```php
// ✅ GOOD: Nama migration yang deskriptif
2024_01_01_000001_create_pesertas_table.php

// ✅ GOOD: Gunakan indexes untuk kolom yang sering di-query
$table->string('no_rekening')->unique()->index();
$table->boolean('status_menang')->default(0)->index();

// ✅ GOOD: Tambahkan foreign keys jika perlu
$table->foreignId('user_id')->constrained()->onDelete('cascade');
```

### Query Optimization

```php
// ✅ GOOD: Gunakan eager loading untuk menghindari N+1
$pesertas = Peserta::with('user')->get();

// ❌ BAD: N+1 query problem
$pesertas = Peserta::all();
foreach ($pesertas as $peserta) {
    echo $peserta->user->name; // Query di setiap iterasi
}
```

---

## Git Workflow

### Commit Messages

Gunakan format conventional commits:

```
feat: add Excel import functionality
fix: resolve race condition in winner selection
docs: update installation guide
refactor: simplify PesertaController
test: add unit tests for Undian component
chore: update dependencies
```

### Branch Naming

```
feature/excel-import
bugfix/race-condition-fix
hotfix/critical-security-patch
refactor/controller-cleanup
```

### Pre-commit Checklist

Sebelum commit, pastikan:

1. ✅ Code sudah di-format dengan Pint: `./vendor/bin/pint`
2. ✅ JavaScript sudah di-lint: `npm run lint` (jika ada)
3. ✅ Tidak ada error PHP: `php artisan test`
4. ✅ Migration berjalan: `php artisan migrate:fresh`
5. ✅ Tidak ada sensitive data di commit (cek `.env`, credentials)

---

## Naming Conventions

### Files & Directories

```
Controllers:    PesertaController.php
Models:         Peserta.php
Livewire:       Undian.php
Migrations:     2024_01_01_000001_create_pesertas_table.php
Views:          admin/dashboard.blade.php
Components:     app-layout.blade.php
```

### Routes

```php
// ✅ GOOD: Gunakan route names yang deskriptif
Route::get('/admin/dashboard', [PesertaController::class, 'index'])
    ->name('admin.dashboard');

// ✅ GOOD: Group routes yang related
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // routes here
});
```

---

## Security Best Practices

### Input Validation

```php
// ✅ GOOD: Selalu validate input
$request->validate([
    'file' => 'required|mimes:xlsx,xls|max:10240',
    'email' => 'required|email|unique:users',
]);

// ❌ BAD: Tanpa validation
$file = $request->file('file');
```

### SQL Injection Prevention

```php
// ✅ GOOD: Gunakan Eloquent/Query Builder (sudah aman)
Peserta::where('no_rekening', $noRekening)->first();

// ❌ BAD: Raw queries tanpa binding
DB::select("SELECT * FROM pesertas WHERE no_rekening = '$noRekening'");
```

### XSS Prevention

```blade
{{-- ✅ GOOD: Blade auto-escape --}}
{{ $userInput }}

{{-- ❌ BAD: Raw output user input --}}
{!! $userInput !!}
```

### CSRF Protection

```blade
{{-- ✅ GOOD: Laravel auto-include CSRF token --}}
<form method="POST" action="{{ route('admin.import') }}">
    @csrf
    <!-- form fields -->
</form>
```

### Authentication & Authorization

```php
// ✅ GOOD: Protect routes dengan middleware
Route::middleware('auth')->group(function () {
    // protected routes
});

// ✅ GOOD: Check permissions di controller
if (!auth()->user()->can('import-peserta')) {
    abort(403);
}
```

---

## Performance Best Practices

### Caching

```php
// ✅ GOOD: Cache expensive queries
$totalPeserta = Cache::remember('total_peserta', 3600, function () {
    return Peserta::count();
});
```

### Database Indexing

```php
// ✅ GOOD: Index kolom yang sering di-query
$table->string('no_rekening')->index();
$table->boolean('status_menang')->index();
```

### Eager Loading

```php
// ✅ GOOD: Eager load relationships
$pesertas = Peserta::with('user')->get();
```

---

## Testing

### Unit Tests

```php
// ✅ GOOD: Test business logic
public function test_pick_winner_excludes_already_won()
{
    $peserta = Peserta::factory()->create(['status_menang' => 1]);
    
    $winner = Peserta::where('status_menang', 0)->first();
    
    $this->assertNotEquals($peserta->id, $winner->id);
}
```

---

## Tools & Commands

### Code Formatting

```bash
# Format PHP code
./vendor/bin/pint

# Format specific file
./vendor/bin/pint app/Http/Controllers/PesertaController.php

# Check without fixing
./vendor/bin/pint --test
```

### Static Analysis

```bash
# Run PHPStan
./vendor/bin/phpstan analyse
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter test_pick_winner
```

---

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Alpine.js Documentation](https://alpinejs.dev/)

---

**Last Updated:** December 2024

