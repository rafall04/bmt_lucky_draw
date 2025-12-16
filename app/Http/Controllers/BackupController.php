<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display a listing of backups.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Backup::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $backups = $query->paginate(15);

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Show the form for creating a new backup.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.backups.create');
    }

    /**
     * Store a newly created backup.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,peserta',
            'format' => 'required_if:type,peserta|in:sql,excel',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            if ($request->type === 'peserta') {
                $backup = $this->backupService->createPesertaBackup(
                    auth()->id(),
                    $request->description,
                    $request->format ?? 'sql'
                );
                $message = 'Backup data peserta berhasil dibuat!';
            } else {
                $backup = $this->backupService->createBackup(
                    auth()->id(),
                    $request->description,
                    'full'
                );
                $message = 'Backup database berhasil dibuat!';
            }

            return redirect()->route('admin.backups.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file.
     *
     * @param Backup $backup
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Backup $backup)
    {
        try {
            return $this->backupService->downloadBackup($backup);
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal mengunduh backup: ' . $e->getMessage());
        }
    }

    /**
     * Show restore confirmation form.
     *
     * @param Backup $backup
     * @return \Illuminate\View\View
     */
    public function showRestore(Backup $backup)
    {
        return view('admin.backups.restore', compact('backup'));
    }

    /**
     * Restore database from backup.
     *
     * @param Request $request
     * @param Backup $backup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request, Backup $backup)
    {
        $request->validate([
            'confirm' => 'required|accepted',
            'password' => 'required|string',
        ]);

        if (!\Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'Password salah!']);
        }

        try {
            $this->backupService->restoreBackup($backup);

            $message = $backup->type === 'peserta' 
                ? 'Data peserta berhasil di-restore dari backup!' 
                : 'Database berhasil di-restore dari backup!';

            return redirect()->route('admin.backups.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal restore database: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified backup.
     *
     * @param Backup $backup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Backup $backup)
    {
        try {
            $this->backupService->deleteBackup($backup);

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Delete backup failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal menghapus backup: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup old backups.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $deleted = $this->backupService->cleanupOldBackups($request->days);

            return redirect()->route('admin.backups.index')
                ->with('success', "Berhasil menghapus {$deleted} backup lama!");
        } catch (\Exception $e) {
            Log::error('Cleanup backups failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal membersihkan backup lama: ' . $e->getMessage());
        }
    }
}
