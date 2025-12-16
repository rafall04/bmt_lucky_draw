<?php

use App\Http\Controllers\PesertaController;
use App\Livewire\Undian;
use Illuminate\Support\Facades\Route;

Route::get('/', Undian::class)->name('home');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', [PesertaController::class, 'index'])->name('dashboard');
    
    // Profile Management - accessible by all authenticated users
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Peserta Management
    // List - accessible by admin and operator
    Route::get('/pesertas', [PesertaController::class, 'list'])->name('pesertas.index');
    Route::get('/pesertas/create', [PesertaController::class, 'create'])->name('pesertas.create');
    Route::post('/pesertas', [PesertaController::class, 'store'])->name('pesertas.store');
    Route::get('/pesertas/{peserta}/edit', [PesertaController::class, 'edit'])->name('pesertas.edit');
    Route::put('/pesertas/{peserta}', [PesertaController::class, 'update'])->name('pesertas.update');
    Route::delete('/pesertas/{peserta}', [PesertaController::class, 'destroy'])->name('pesertas.destroy');
    Route::post('/pesertas/bulk-delete', [PesertaController::class, 'bulkDelete'])->name('pesertas.bulk-delete');
    
    // Import - accessible by admin and operator
    Route::post('/import', [PesertaController::class, 'import'])->name('import');
    Route::get('/import/template', [PesertaController::class, 'downloadTemplate'])->name('import.template');
    
    // Export Winners - accessible by all authenticated users
    Route::get('/export-winners', [PesertaController::class, 'exportWinners'])->name('export-winners');
    
    // Reset Pemenang - only admin
    Route::post('/reset-pemenang', [PesertaController::class, 'resetPemenang'])
        ->middleware('role:admin')
        ->name('reset-pemenang');
    
    // System Reset - only admin
    Route::get('/reset', [PesertaController::class, 'showReset'])
        ->middleware('role:admin')
        ->name('reset');
    Route::post('/reset/truncate', [PesertaController::class, 'truncateAll'])
        ->middleware('role:admin')
        ->name('reset.truncate');
    Route::post('/reset/test-telegram', [PesertaController::class, 'testTelegram'])
        ->middleware('role:admin')
        ->name('reset.test-telegram');
    
    // Trash Bin - only admin
    Route::get('/pesertas/trash', [PesertaController::class, 'trash'])
        ->middleware('role:admin')
        ->name('pesertas.trash');
    Route::post('/pesertas/{id}/restore', [PesertaController::class, 'restore'])
        ->middleware('role:admin')
        ->name('pesertas.restore');
    Route::delete('/pesertas/{id}/force-delete', [PesertaController::class, 'forceDelete'])
        ->middleware('role:admin')
        ->name('pesertas.force-delete');
    
    // Winners - accessible by all authenticated users (read-only)
    Route::get('/winners', [PesertaController::class, 'winners'])->name('winners');
    
    // User Management - only admin
    Route::resource('users', \App\Http\Controllers\UserController::class)
        ->middleware('role:admin');
    
    // Import Info - accessible by all authenticated users
    Route::get('/import-info', function () {
        return view('admin.import-info');
    })->name('import-info');
    
    // Activity Logs - accessible by all authenticated users (read-only)
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs');
    
    // Settings - only admin
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'edit'])
        ->middleware('role:admin')
        ->name('settings.edit');
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])
        ->middleware('role:admin')
        ->name('settings.update');
    Route::delete('/settings/logo', [\App\Http\Controllers\SettingsController::class, 'deleteLogo'])
        ->middleware('role:admin')
        ->name('settings.delete-logo');
    Route::delete('/settings/doorprize-image', [\App\Http\Controllers\SettingsController::class, 'deleteDoorprizeImage'])
        ->middleware('role:admin')
        ->name('settings.delete-doorprize-image');
    Route::delete('/settings/undian-background-image', [\App\Http\Controllers\SettingsController::class, 'deleteUndianBackgroundImage'])
        ->middleware('role:admin')
        ->name('settings.delete-undian-background-image');
    
    // Backup & Restore - only admin
    Route::get('/backups', [\App\Http\Controllers\BackupController::class, 'index'])
        ->middleware('role:admin')
        ->name('backups.index');
    Route::get('/backups/create', [\App\Http\Controllers\BackupController::class, 'create'])
        ->middleware('role:admin')
        ->name('backups.create');
    Route::post('/backups', [\App\Http\Controllers\BackupController::class, 'store'])
        ->middleware('role:admin')
        ->name('backups.store');
    Route::get('/backups/{backup}/download', [\App\Http\Controllers\BackupController::class, 'download'])
        ->middleware('role:admin')
        ->name('backups.download');
    Route::get('/backups/{backup}/restore', [\App\Http\Controllers\BackupController::class, 'showRestore'])
        ->middleware('role:admin')
        ->name('backups.restore');
    Route::post('/backups/{backup}/restore', [\App\Http\Controllers\BackupController::class, 'restore'])
        ->middleware('role:admin')
        ->name('backups.restore');
    Route::delete('/backups/{backup}', [\App\Http\Controllers\BackupController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('backups.destroy');
    Route::post('/backups/cleanup', [\App\Http\Controllers\BackupController::class, 'cleanup'])
        ->middleware('role:admin')
        ->name('backups.cleanup');
});

require __DIR__.'/auth.php';

