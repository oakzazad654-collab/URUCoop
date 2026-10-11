<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $role = $user->role ?? null;

                switch ($role) {
                    case 'student':
                        $redirectPath = '/student/dashboard';
                        break;
                    case 'advisor':
                    case 'officer':
                        $redirectPath = '/staff/dashboard';
                        break;
                    case 'supervisor':
                        $redirectPath = '/supervisor/dashboard';
                        break;
                    case 'executive':
                        $redirectPath = '/executive/dashboard';
                        break;
                    case 'company':
                        $redirectPath = '/company/dashboard';
                        break;
                    case 'admin':
                        $redirectPath = '/admin/dashboard';
                        break;
                    default:
                        Auth::guard($guard)->logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()->route('login');
                }

                return redirect($redirectPath);
            }
        }

        return $next($request);
    }
}
