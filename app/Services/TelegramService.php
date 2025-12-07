<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function sendMessage(string $message, ?string $chatId = null): bool
    {
        $chatId = $chatId ?? $this->chatId;
        
        if (!$this->token || !$chatId) {
            Log::warning('Telegram credentials not set');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        try {
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info("Telegram message sent to {$chatId}", ['message' => $message]);
                return true;
            } else {
                Log::error("Telegram API Error", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Telegram Connection Error: " . $e->getMessage());
            return false;
        }
    }
}