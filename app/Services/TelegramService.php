<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $chatId;
    protected string $baseUrl;

    /**
     * NOTE: Credentials are stored directly in this class
     * due to limitations in accessing .env or config files
     * in the current environment. In a standard setup,
     * these should be moved to a configuration file.
     */
    public function __construct()
    {
        $this->token = '8428448753:AAG3WdRt8y-8dGvB198onUBK9ZU5dgSVNHc';
        $this->chatId = '-4930054057';
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    /**
     * Sends a message to the configured Telegram chat.
     *
     * @param string $message The message text to send.
     * @param bool $async Whether to send the request asynchronously.
     * @return void
     */
    public function sendMessage(string $message, bool $async = true): void
    {
        // Basic check to avoid sending empty messages
        if (empty($message)) {
            return;
        }

        try {
            $payload = [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ];

            if ($async) {
                // Asynchronous request to not block the main application flow
                Http::async()->post($this->baseUrl . 'sendMessage', $payload)->wait();
            } else {
                // Synchronous request
                $response = Http::post($this->baseUrl . 'sendMessage', $payload);
                if ($response->failed()) {
                    Log::error('Telegram API error: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            // Log any exception that occurs during the API call
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }
}
