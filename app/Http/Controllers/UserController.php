<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function index(Request $request)
    {
        $filters = $request->validate([
            'phone' => ['sometimes']
        ]);

        $query = User::query()->filter($filters);
        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
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

    public function resetPassword(Request $request, User $user)
    {
        $password = Str::random(6);
        $user->update([
            'password' => $password
        ]);

        return response()->json(['password' => $password]);
    }
}
