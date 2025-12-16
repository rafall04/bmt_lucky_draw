<?php

namespace App\Http\Controllers;

use App\Exports\PesertaExport;
use App\Exports\PesertaTemplateExport;
use App\Imports\PesertaImport;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PesertaController extends Controller
{
    /**
     * Display a listing of the resource with statistics.
     */
    public function index()
    {
        $totalPeserta = Peserta::count();
        $totalPemenang = Peserta::where('status_menang', 1)->count();
        $remainingCandidates = Peserta::where('status_menang', 0)->count();
        $totalOperators = \App\Models\User::where('role', 'operator')->count();

        $recentWinners = Peserta::where('status_menang', 1)
            ->orderBy('waktu_menang', 'desc')
            ->limit(10)
            ->get();

        $prizeStats = Peserta::where('status_menang', 1)
            ->whereNotNull('hadiah_didapat')
            ->selectRaw('hadiah_didapat, COUNT(*) as count')
            ->groupBy('hadiah_didapat')
            ->orderBy('count', 'desc')
            ->get();

        $recentLogs = \App\Models\ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPeserta',
            'totalPemenang',
            'remainingCandidates',
            'totalOperators',
            'recentWinners',
            'prizeStats',
            'recentLogs'
        ));
    }

    /**
     * Display list of winners.
     */
    public function winners(Request $request)
    {
        $query = Peserta::where('status_menang', 1);

        if ($request->filled('hadiah')) {
            $query->where('hadiah_didapat', $request->hadiah);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_rekening', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $winners = $query->orderBy('waktu_menang', 'desc')->paginate(20);

        $prizes = Peserta::where('status_menang', 1)
            ->whereNotNull('hadiah_didapat')
            ->distinct()
            ->pluck('hadiah_didapat')
            ->filter()
            ->sort();

        return view('admin.winners', compact('winners', 'prizes'));
    }

    /**
     * Import peserta from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        try {
            Excel::import(new PesertaImport, $request->file('file'));

            return redirect()->route('admin.dashboard')
                ->with('success', 'Data peserta berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Display list of all participants with filters.
     */
    public function list(Request $request)
    {
        $query = Peserta::query();

        if ($request->filled('status')) {
            if ($request->status === 'menang') {
                $query->where('status_menang', 1);
            } elseif ($request->status === 'belum_menang') {
                $query->where('status_menang', 0);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_rekening', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('cabang', 'like', "%{$search}%");
            });
        }

        $pesertas = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.pesertas.index', compact('pesertas'));
    }

    /**
     * Show the form for creating a new participant.
     */
    public function create()
    {
        return view('admin.pesertas.create');
    }

    /**
     * Store a newly created participant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_rekening' => ['required', 'string', 'max:255', 'unique:pesertas,no_rekening'],
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'cabang' => ['nullable', 'string', 'max:255'],
        ]);

        Peserta::create([
            'no_rekening' => $request->no_rekening,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'cabang' => $request->cabang,
            'status_menang' => 0,
        ]);

        return redirect()->route('admin.pesertas.index')
            ->with('success', 'Peserta berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified participant.
     */
    public function edit(Peserta $peserta)
    {
        return view('admin.pesertas.edit', compact('peserta'));
    }

    /**
     * Update the specified participant.
     */
    public function update(Request $request, Peserta $peserta)
    {
        $request->validate([
            'no_rekening' => ['required', 'string', 'max:255', 'unique:pesertas,no_rekening,' . $peserta->id],
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'cabang' => ['nullable', 'string', 'max:255'],
        ]);

        $peserta->update([
            'no_rekening' => $request->no_rekening,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'cabang' => $request->cabang,
        ]);

        return redirect()->route('admin.pesertas.index')
            ->with('success', 'Data peserta berhasil diperbarui!');
    }

    /**
     * Remove the specified participant (soft delete).
     */
    public function destroy(Peserta $peserta)
    {
        $peserta->delete();

        return redirect()->route('admin.pesertas.index')
            ->with('success', 'Peserta berhasil dihapus!');
    }

    /**
     * Force delete participant (permanent).
     */
    public function forceDelete($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan force delete.');
        }

        $peserta = Peserta::withTrashed()->findOrFail($id);
        $peserta->forceDelete();

        return redirect()->route('admin.pesertas.trash')
            ->with('success', 'Peserta berhasil dihapus permanen!');
    }

    /**
     * Restore soft deleted participant.
     */
    public function restore($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan restore.');
        }

        $peserta = Peserta::withTrashed()->findOrFail($id);
        $peserta->restore();

        return redirect()->route('admin.pesertas.trash')
            ->with('success', 'Peserta berhasil dikembalikan!');
    }

    /**
     * Display trash bin (soft deleted participants).
     */
    public function trash(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses Trash Bin.');
        }

        $query = Peserta::onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_rekening', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $pesertas = $query->orderBy('deleted_at', 'desc')->paginate(20);

        return view('admin.pesertas.trash', compact('pesertas'));
    }

    /**
     * Bulk delete participants.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'string'],
        ]);

        $idsArray = json_decode($request->ids, true);
        
        if (!is_array($idsArray) || empty($idsArray)) {
            return redirect()->route('admin.pesertas.index')
                ->with('error', 'Tidak ada peserta yang dipilih!');
        }

        $validIds = Peserta::whereIn('id', $idsArray)->pluck('id')->toArray();
        
        if (empty($validIds)) {
            return redirect()->route('admin.pesertas.index')
                ->with('error', 'Tidak ada peserta yang valid untuk dihapus!');
        }

        Peserta::whereIn('id', $validIds)->delete();

        return redirect()->route('admin.pesertas.index')
            ->with('success', count($validIds) . ' peserta berhasil dihapus!');
    }

    /**
     * Export winners to Excel.
     */
    public function exportWinners()
    {
        $filename = 'pemenang_undian_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new PesertaExport, $filename);
    }

    /**
     * Reset all pemenang status.
     */
    public function resetPemenang(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mereset pemenang.');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verify user's current password for security
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Password konfirmasi salah!');
        }

        DB::transaction(function () {
            Peserta::query()->update([
                'status_menang' => 0,
                'hadiah_didapat' => null,
                'waktu_menang' => null,
            ]);
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Semua status pemenang telah direset!');
    }

    /**
     * Show system reset page (admin only).
     */
    public function showReset()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }

        return view('admin.reset');
    }

    /**
     * Truncate all participants (dangerous action).
     */
    public function truncateAll(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }

        $request->validate([
            'password' => ['required', 'string'],
            'confirm' => ['required', 'accepted'],
        ]);

        // Verify user's current password for security
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->route('admin.reset')
                ->with('error', 'Password konfirmasi salah!');
        }

        DB::transaction(function () {
            Peserta::truncate();
        });

        // Log activity
        log_activity('truncate', auth()->user()->name . ' menghapus semua data peserta (TRUNCATE)');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Semua data peserta telah dihapus!');
    }

    /**
     * Test Telegram connection.
     */
    public function testTelegram()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }

        $telegramService = app(\App\Services\TelegramService::class);
        $result = $telegramService->sendTest();

        if ($result) {
            return redirect()->route('admin.reset')
                ->with('success', 'Test notifikasi Telegram berhasil dikirim!');
        } else {
            return redirect()->route('admin.reset')
                ->with('error', 'Test notifikasi Telegram gagal. Pastikan TELEGRAM_BOT_TOKEN dan TELEGRAM_CHAT_ID sudah dikonfigurasi di .env');
        }
    }

    /**
     * Download Excel template for importing peserta.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate()
    {
        $filename = 'Template_Import_Peserta_' . now()->setTimezone('Asia/Jakarta')->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new PesertaTemplateExport(), $filename);
    }
}

