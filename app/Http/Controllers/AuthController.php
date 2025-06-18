<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        }
        return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
    }

    public function changePassword(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Đổi mật khẩu thành công.',
        ]);
    }
}
