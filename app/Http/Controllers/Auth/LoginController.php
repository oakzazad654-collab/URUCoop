<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $user = Auth::user();

        switch ($user->role) {
            case 'student':
                $dashboard = '/student/dashboard';
                $allowedPrefixes = ['/student/', '/account/', '/notifications'];
                break;
            case 'advisor':
                $dashboard = '/staff/dashboard';
                $allowedPrefixes = ['/advisor/', '/staff/', '/account/', '/notifications'];
                break;
            case 'officer':
                $dashboard = '/staff/dashboard';
                $allowedPrefixes = ['/officer/', '/staff/', '/account/', '/notifications'];
                break;
            case 'supervisor':
                $dashboard = '/supervisor/dashboard';
                $allowedPrefixes = ['/supervisor/', '/workflow/', '/account/', '/notifications'];
                break;
            case 'executive':
                $dashboard = '/executive/dashboard';
                $allowedPrefixes = ['/executive/', '/workflow/', '/account/', '/notifications'];
                break;
            case 'company':
                $dashboard = '/company/dashboard';
                $allowedPrefixes = ['/company/', '/account/', '/notifications'];
                break;
            case 'mentor':
                $dashboard = '/mentor/dashboard';
                $allowedPrefixes = ['/mentor/', '/workflow/', '/account/', '/notifications'];
                break;
            case 'admin':
                $dashboard = '/admin/dashboard';
                $allowedPrefixes = ['/admin/', '/account/', '/notifications'];
                break;
            default:
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => 'ไม่พบบทบาทผู้ใช้งานที่ถูกต้อง']);
        }

        if ($user->status !== 'ACTIVE') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', $user->status === 'PENDING'
                ? 'บัญชีของคุณกำลังรอ Admin ตรวจสอบและอนุมัติ'
                : 'บัญชีนี้ยังไม่ได้รับอนุมัติให้เข้าใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
        }

        $intendedPath = parse_url($request->session()->get('url.intended', ''), PHP_URL_PATH);
        if ($intendedPath && !collect($allowedPrefixes)->contains(function ($prefix) use ($intendedPath) {
            return strpos($intendedPath, $prefix) === 0;
        })) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended($dashboard);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}