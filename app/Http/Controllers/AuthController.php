<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'exists:users,name'],
            'password' => ['required']
        ]);

        $admin = User::query()->where('name', $data['name'])->first();
        if (Hash::check($data['password'], $admin->password)) {
            $admin->tokens()->delete();
            $token = $admin->createToken('user');
            return ['token' => $token->plainTextToken, 'user' => $admin];
        }

        abort(ResponseStatus::UNAUTHENTICATED->value, 'Incorrect Password');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'password' => ['required'],
            'new_password' => ['required', 'confirmed']
        ]);

        $admin = $request->user();

        if (Hash::check($data['password'], $admin->password)) {
            $admin->update(['password' => bcrypt($data['new_password'])]);
            return response()->json(['message' => 'ok']);
        }

        abort(ResponseStatus::UNAUTHENTICATED->value, 'Incorrect Password');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json(['message' => 'success']);
    }
}
