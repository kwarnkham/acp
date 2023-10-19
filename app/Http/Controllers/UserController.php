<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'fb_id' => ['required'],
            'fb_name' => ['required']
        ]);

        $user  = User::query()->where('fb_id', $data['fb_id'])->first();
        if ($user) return response()->json(['user' => $user, 'token' => $user->generateToken()]);


        if (User::query()->where('name', $data['fb_name'])->exists()) $data['name'] = $data['fb_name'] . '<->' . $data['fb_id'];
        else $data['name'] = $data['fb_name'];

        $user = User::create($data);
        return response()->json(['user' => $user, 'token' => $user->generateToken()]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'fb_id' => ['required'],
        ]);

        $user  = User::query()->where('fb_id', $data['fb_id'])->first();
        if ($user) return response()->json(['user' => $user, 'token' => $user->generateToken()]);

        else abort(ResponseStatus::BAD_REQUEST->value, 'User does not exists');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->json(['message' => 'Success']);
    }
}
