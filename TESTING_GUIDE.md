# 🧪 Testing Guide - BMT Lucky Draw System

**Tanggal:** 15 Desember 2025  
**Versi:** 1.0

---

## 📋 Overview

Dokumen ini menjelaskan test suite yang dibuat untuk memastikan project siap untuk production. Test suite mencakup semua critical paths dan edge cases.

---

## ✅ Test Coverage

### Test Suites

1. **Unit Tests** (`tests/Unit/`)
   - User model tests
   - Peserta model tests

2. **Feature Tests** (`tests/Feature/`)
   - Authentication tests
   - Winner Selection tests (CRITICAL - race condition)
   - Import/Export tests
   - Backup/Restore tests
   - Reset Pemenang tests (security)
   - Livewire component tests

---

## 🎯 Critical Tests

### 1. Winner Selection Tests (`WinnerSelectionTest.php`)

**Status:** ✅ **COMPLETE**

Test ini sangat critical karena menguji core functionality sistem undian:

#### Test Cases:
1. ✅ `test_can_pick_winner_when_participants_available` - Basic winner selection
2. ✅ `test_cannot_pick_winner_when_no_participants_available` - Edge case handling
3. ✅ `test_cannot_pick_same_winner_twice` - Double winner prevention
4. ✅ `test_race_condition_concurrent_winner_selection` - **CRITICAL** - Race condition test
5. ✅ `test_winner_selection_uses_lock_for_update` - Database locking verification
6. ✅ `test_can_save_prize_for_winner` - Prize assignment
7. ✅ `test_cannot_save_prize_without_selecting_prize` - Validation
8. ✅ `test_cannot_save_prize_without_winner` - Validation
9. ✅ `test_reset_display_clears_winner_display` - UI state management
10. ✅ `test_start_rolling_sets_rolling_state` - UI state management
11. ✅ `test_pick_winner_rolls_back_on_error` - Transaction integrity
12. ✅ `test_cache_cleared_after_winner_selection` - Cache management
13. ✅ `test_is_processing_flag_prevents_concurrent_calls` - Race condition prevention

**Total:** 13 test cases

**Critical Test: Race Condition**
```php
test_race_condition_concurrent_winner_selection()
```
Test ini mensimulasikan 10 concurrent requests untuk memastikan:
- Tidak ada duplicate winners
- Database consistency terjaga
- `lockForUpdate()` bekerja dengan benar

---

### 2. Import/Export Tests (`ImportExportTest.php`)

**Status:** ✅ **COMPLETE**

Test untuk fitur import dan export Excel:

#### Test Cases:
1. ✅ `test_can_import_excel_file_with_valid_data` - Basic import
2. ✅ `test_cannot_import_invalid_file_format` - File validation
3. ✅ `test_cannot_import_file_larger_than_max_size` - Size validation
4. ✅ `test_can_export_winners_to_excel` - Export functionality
5. ✅ `test_can_download_import_template` - Template download
6. ✅ `test_import_handles_duplicate_no_rekening` - Duplicate handling
7. ✅ `test_import_skips_empty_rows` - Data validation
8. ✅ `test_export_includes_only_winners` - Export filtering

**Total:** 8 test cases

---

### 3. Backup/Restore Tests (`BackupRestoreTest.php`)

**Status:** ✅ **COMPLETE**

Test untuk sistem backup dan restore:

#### Test Cases:
1. ✅ `test_admin_can_create_backup` - Full backup creation
2. ✅ `test_admin_can_create_peserta_backup` - Peserta-only backup
3. ✅ `test_admin_can_restore_backup_with_correct_password` - Restore with auth
4. ✅ `test_admin_cannot_restore_backup_with_incorrect_password` - Security check
5. ✅ `test_admin_can_download_backup` - Download functionality
6. ✅ `test_admin_can_delete_backup` - Delete functionality
7. ✅ `test_operator_cannot_access_backup_management` - Authorization
8. ✅ `test_backup_service_creates_valid_sql_file` - File integrity
9. ✅ `test_backup_cleanup_removes_old_backups` - Cleanup functionality

**Total:** 9 test cases

---

### 4. Authentication Tests (`AuthenticationTest.php`)

**Status:** ✅ **COMPLETE**

Test untuk authentication flow:

#### Test Cases:
1. ✅ `test_user_can_login_with_correct_credentials`
2. ✅ `test_user_cannot_login_with_incorrect_credentials`
3. ✅ `test_authenticated_user_can_access_dashboard`
4. ✅ `test_unauthenticated_user_cannot_access_dashboard`
5. ✅ `test_user_can_logout`

**Total:** 5 test cases

---

### 5. Reset Pemenang Tests (`ResetPemenangTest.php`)

**Status:** ✅ **COMPLETE**

Test untuk security-critical reset operations:

#### Test Cases:
1. ✅ `test_admin_can_reset_pemenang_with_correct_password`
2. ✅ `test_admin_cannot_reset_pemenang_with_incorrect_password`
3. ✅ `test_operator_cannot_reset_pemenang`

**Total:** 3 test cases

---

### 6. Livewire Component Tests (`LivewireUndianTest.php`)

**Status:** ✅ **COMPLETE**

Test untuk Livewire component:

#### Test Cases:
1. ✅ `test_undian_component_can_be_rendered`
2. ✅ `test_dummy_data_is_loaded_from_eligible_participants`
3. ✅ `test_dummy_data_is_cached`
4. ✅ `test_component_handles_settings_correctly`
5. ✅ `test_component_initializes_with_correct_defaults`
6. ✅ `test_component_state_changes_correctly`
7. ✅ `test_component_handles_empty_participants_gracefully`
8. ✅ `test_component_renders_with_layout`

**Total:** 8 test cases

---

### 7. Unit Tests

#### UserTest (`tests/Unit/UserTest.php`)
1. ✅ `test_user_is_admin`
2. ✅ `test_user_is_operator`
3. ✅ `test_password_is_hashed`
4. ✅ `test_password_is_hidden`

#### PesertaTest (`tests/Unit/PesertaTest.php`)
1. ✅ `test_peserta_can_be_created`
2. ✅ `test_status_menang_is_boolean`
3. ✅ `test_waktu_menang_is_datetime`
4. ✅ `test_peserta_uses_soft_deletes`

---

## 📊 Test Statistics

| Category | Test Files | Test Cases | Status |
|----------|------------|------------|--------|
| Unit Tests | 2 | 8 | ✅ Complete |
| Feature Tests | 6 | 46 | ✅ Complete |
| **TOTAL** | **8** | **54** | ✅ **Complete** |

---

## 🚀 Running Tests

### Run All Tests

```bash
# Using Composer
composer test

# Using PHPUnit directly
php artisan test

# Using PHPUnit directly (alternative)
vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature
```

### Run Specific Test File

```bash
# Run winner selection tests (CRITICAL)
php artisan test tests/Feature/WinnerSelectionTest.php

# Run authentication tests
php artisan test tests/Feature/AuthenticationTest.php

# Run backup tests
php artisan test tests/Feature/BackupRestoreTest.php
```

### Run Specific Test Method

```bash
# Run race condition test (CRITICAL)
php artisan test --filter test_race_condition_concurrent_winner_selection

# Run specific test
php artisan test --filter test_can_pick_winner_when_participants_available
```

### Run Tests with Coverage (if configured)

```bash
php artisan test --coverage
```

---

## 🎯 Critical Test: Race Condition

Test paling penting adalah **Race Condition Test** yang memastikan tidak ada double winners saat concurrent requests:

```php
test_race_condition_concurrent_winner_selection()
```

**What it tests:**
- Simulates 10 concurrent winner selection requests
- Verifies no duplicate winners
- Verifies database consistency
- Ensures `lockForUpdate()` works correctly

**Why it's critical:**
- Race conditions can cause double winners
- Database integrity is essential
- Real-world scenario (multiple users clicking simultaneously)

**Expected Result:**
- ✅ Exactly 10 unique winners selected
- ✅ No duplicate IDs
- ✅ Database shows exactly 10 winners

---

## ⚠️ Test Requirements

### Database

Tests use SQLite in-memory database (configured in `phpunit.xml`):
- No setup required
- Fast execution
- Isolated from production database

### Dependencies

All dependencies are already in `composer.json`:
- PHPUnit 11.x
- Laravel testing utilities
- Livewire testing utilities

### Factories

Factories are required for tests:
- ✅ `UserFactory` - for creating test users
- ✅ `PesertaFactory` - for creating test participants

---

## 🔍 Test Coverage Analysis

### Well Tested ✅

1. **Winner Selection** - 13 test cases covering all scenarios
2. **Authentication** - 5 test cases covering login/logout
3. **Security Operations** - Password verification, authorization
4. **Import/Export** - File validation, data handling
5. **Backup/Restore** - Full backup lifecycle
6. **Models** - Basic CRUD and casts

### Could Be Improved ⚠️

1. **Load Testing** - Not included (requires external tools)
2. **Integration Tests** - End-to-end user flows
3. **Performance Tests** - Large dataset scenarios
4. **Error Scenarios** - More edge cases

---

## 📝 Writing New Tests

### Example: Adding a New Test

```php
public function test_new_feature_works_correctly(): void
{
    // Arrange
    $user = User::factory()->create();
    $peserta = Peserta::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->post('/admin/some-endpoint', [
            'data' => 'value',
        ]);

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('table', [
        'column' => 'value',
    ]);
}
```

### Best Practices

1. ✅ Use factories for test data
2. ✅ Use descriptive test names
3. ✅ Follow Arrange-Act-Assert pattern
4. ✅ Test both success and failure cases
5. ✅ Clean up after tests (RefreshDatabase trait)

---

## 🐛 Debugging Tests

### Common Issues

1. **Test fails with database error:**
   - Check migrations are up to date
   - Verify RefreshDatabase trait is used

2. **Test fails with authentication:**
   - Use `actingAs($user)` for authenticated requests
   - Create user with factory

3. **Test fails with Livewire:**
   - Use `Livewire::test()` instead of `$this->get()`
   - Check component state after actions

---

## ✅ Pre-Production Checklist

Before deploying to production, ensure:

- [ ] All tests pass: `composer test`
- [ ] No skipped tests
- [ ] Race condition test passes
- [ ] Security tests pass
- [ ] All critical paths tested
- [ ] Test coverage > 70% (if measured)

---

## 📚 Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Livewire Testing](https://laravel-livewire.com/docs/testing)

---

## 🎯 Test Results Interpretation

### All Tests Pass ✅
**Status:** Project is ready for production deployment

### Some Tests Fail ❌
**Action:** Fix failing tests before deployment

### Race Condition Test Fails ⚠️
**Action:** CRITICAL - Do not deploy until fixed

---

**Last Updated:** 15 Desember 2025  
**Test Suite Version:** 1.0

