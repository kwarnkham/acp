<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class Telegram
{

    public static function getMe()
    {
        $token = config('app.telegram_bot_token');
        if (!$token) return null;
        return Http::post("https://api.telegram.org/bot$token/getMe")->json();
    }

    public static function getupdates()
    {
        $token = config('app.telegram_bot_token');
        if (!$token) return null;
        return Http::post("https://api.telegram.org/bot$token/getupdates")->json();
    }

    public static function sendMessage($chatId, $text, $parseMode = 'HTML')
    {
        if (!$chatId || !$text) return;
        $token = config('app.telegram_bot_token');
        if (!$token) return null;
        return Http::post("https://api.telegram.org/bot$token/sendmessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode
        ])->json();
    }
}
