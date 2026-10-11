<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'studentProfile.program.faculty',
            'staffProfile.faculty',
            'staffProfile.program',
            'company',
            'companyContact.company',
        ]);
        $botBasicId = ltrim((string) config('services.line.bot_basic_id'), '@');
        $lineAddUrl = $botBasicId ? 'https://line.me/R/ti/p/@'.$botBasicId : null;
        $qrCodeUrl = $lineAddUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='.urlencode($lineAddUrl) : null;
        return view('account.settings', compact('user', 'lineAddUrl', 'qrCodeUrl'));
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed', 'different:current_password'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()->route('account.settings')->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }

    public function createLineBindingCode(Request $request)
    {
        $code = strtoupper(bin2hex(random_bytes(12)));
        $request->user()->forceFill([
            'line_link_code_hash' => hash('sha256', $code),
            'line_link_expires_at' => now()->addMinutes(10),
        ])->save();

        return redirect()->route('account.settings')->with('line_link_code', $code)->with('success', 'สร้างรหัสเชื่อมต่อ LINE แล้ว รหัสนี้ใช้ได้ 10 นาที');
    }

    public function disconnectLine(Request $request)
    {
        $request->user()->forceFill([
            'line_user_id' => null,
            'line_link_code_hash' => null,
            'line_link_expires_at' => null,
        ])->save();

        return redirect()->route('account.settings')->with('success', 'ยกเลิกการเชื่อมต่อ LINE แล้ว');
    }
}
