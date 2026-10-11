<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:150',
            'password' => 'required|string|max:255',
        ]);

        $user = User::where('email', $validated['identifier'])
            ->orWhere('username', $validated['identifier'])
            ->orWhere('student_number', $validated['identifier'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->getAuthPassword()) || $user->status !== 'ACTIVE') {
            return response()->json(['message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 422);
        }

        $token = Str::random(80);
        $user->forceFill([
            'api_token_hash' => hash('sha256', $token),
            'last_login_at' => now(),
        ])->save();

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->forceFill(['api_token_hash' => null])->save();

        return response()->json(['message' => 'ออกจากระบบแล้ว']);
    }
}
