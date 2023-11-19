<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function toggleTelegramNotification(Request $request)
    {
        $user = $request->user();
        $user->update(['notification_active' => $user->notification_active ? false : true]);
        return response()->json([
            'user' => $user
        ]);
    }

    public function setTelegramId(Request $request)
    {
        $data = $request->validate([
            'telegram_chat_id' => ['required']
        ]);
        $user = $request->user();
        $user->update(['telegram_chat_id' => $data['telegram_chat_id']]);
        return response()->json([
            'user' => $user
        ]);
    }
}
