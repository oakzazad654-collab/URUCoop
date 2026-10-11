<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));
        if (in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            return back()->with('status', 'หากอีเมลนี้ลงทะเบียนไว้ ระบบจะส่งลิงก์ตั้งรหัสผ่านใหม่ให้');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function resetForm(Request $request, $token)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'ตั้งรหัสผ่านใหม่แล้ว กรุณาเข้าสู่ระบบ');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
