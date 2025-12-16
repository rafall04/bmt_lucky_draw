<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Services\TelegramService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogout
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;
        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => "{$user->name} melakukan logout",
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        $message = "👋 **LOGOUT**\n";
        $message .= "User: *{$user->name}*\n";
        $message .= "Waktu: " . now()->format('d/m/Y H:i:s');

        $this->telegramService->send($message);
    }
}
