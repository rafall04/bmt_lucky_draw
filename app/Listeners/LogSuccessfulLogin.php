<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Services\TelegramService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $ipAddress = Request::ip();
        $userAgent = Request::userAgent();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => "{$user->name} melakukan login (Role: {$user->role})",
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        $message = "🔑 **LOGIN ALERT**\n";
        $message .= "User: *{$user->name}*\n";
        $message .= "Role: *{$user->role}*\n";
        $message .= "IP: `{$ipAddress}`\n";
        $message .= "Waktu: " . now()->format('d/m/Y H:i:s');

        $this->telegramService->send($message);
    }
}
