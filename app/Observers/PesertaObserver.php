<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Peserta;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class PesertaObserver
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the Peserta "created" event.
     */
    public function created(Peserta $peserta): void
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        $description = "{$userName} menambah peserta {$peserta->nama} (No Rek: {$peserta->no_rekening})";

        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => 'create',
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        // Send Telegram notification
        $this->telegramService->sendAlert(
            '✅',
            'INFO',
            $userName,
            "menambah peserta baru: *{$peserta->nama}* (No Rek: {$peserta->no_rekening})"
        );
    }

    /**
     * Handle the Peserta "updated" event.
     */
    public function updated(Peserta $peserta): void
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        
        $dirty = $peserta->getDirty();
        $changedFields = array_keys($dirty);
        
        $description = "{$userName} mengubah data {$peserta->nama} (No Rek: {$peserta->no_rekening})";
        
        if (!empty($changedFields)) {
            $description .= " - Field yang diubah: " . implode(', ', $changedFields);
        }

        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => 'update',
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        // Send Telegram notification
        $this->telegramService->sendAlert(
            '✏️',
            'UPDATE',
            $userName,
            "mengubah data *{$peserta->nama}* (No Rek: {$peserta->no_rekening})"
        );
    }

    /**
     * Handle the Peserta "deleted" event.
     */
    public function deleted(Peserta $peserta): void
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        $description = "{$userName} MENGHAPUS peserta {$peserta->nama} (No Rek: {$peserta->no_rekening})";

        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => 'delete',
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        $this->telegramService->sendAlert(
            '🚨',
            'DELETE ALERT',
            $userName,
            "MENGHAPUS peserta *{$peserta->nama}* (No Rek: {$peserta->no_rekening})"
        );
    }

    /**
     * Handle the Peserta "restored" event.
     */
    public function restored(Peserta $peserta): void
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        $description = "{$userName} mengembalikan peserta {$peserta->nama} (No Rek: {$peserta->no_rekening}) dari trash";

        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => 'restore',
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        // Send Telegram notification
        $this->telegramService->sendAlert(
            '♻️',
            'RESTORE',
            $userName,
            "mengembalikan peserta *{$peserta->nama}* (No Rek: {$peserta->no_rekening}) dari trash"
        );
    }
}
