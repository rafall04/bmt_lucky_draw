<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatId;
    protected $enabled;

    public function __construct()
    {
        $this->botToken = Setting::get('telegram_bot_token') 
            ?: config('services.telegram.bot_token');
        $this->chatId = Setting::get('telegram_chat_id') 
            ?: config('services.telegram.chat_id');
        $this->enabled = !empty($this->botToken) && !empty($this->chatId);
    }

    /**
     * Send message to Telegram.
     * 
     * @param string $message
     * @return bool
     */
    public function send(string $message): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            
            $response = Http::timeout(5)->post($url, [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Telegram notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::warning('Telegram notification error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send formatted alert message.
     * 
     * @param string $emoji
     * @param string $type
     * @param string $userName
     * @param string $description
     * @return bool
     */
    public function sendAlert(string $emoji, string $type, string $userName, string $description): bool
    {
        $message = "{$emoji} **{$type}:**\n";
        $message .= "User: *{$userName}*\n";
        $message .= "Aksi: {$description}";

        return $this->send($message);
    }

    /**
     * Send test message.
     * 
     * @return bool
     */
    public function sendTest(): bool
    {
        $message = "🧪 **TEST NOTIFICATION**\n";
        $message .= "Sistem monitoring BMT Lucky Draw berfungsi dengan baik!\n";
        $message .= "Waktu: " . now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') . " WIB";

        return $this->send($message);
    }
}

