<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCorrectRolePortal
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $normalizedRoles = $allowedRoles;
        if (count($normalizedRoles) === 1 && $normalizedRoles[0] === 'staff') {
            $normalizedRoles = ['advisor', 'officer'];
        }

        if (!in_array($user->role, $normalizedRoles, true)) {
            switch ($user->role) {
                case 'student':
                    $redirectRoute = 'student.dashboard';
                    break;
                case 'advisor':
                    $redirectRoute = 'advisor.approvals.dashboard';
                    break;
                case 'officer':
                    $redirectRoute = 'officer.approvals.dashboard';
                    break;
                case 'supervisor':
                    $redirectRoute = 'supervisor.dashboard';
                    break;
                case 'mentor':
                    $redirectRoute = 'mentor.dashboard';
                    break;
                case 'executive':
                    $redirectRoute = 'executive.dashboard';
                    break;
                case 'company':
                    $redirectRoute = 'company.dashboard';
                    break;
                case 'admin':
                    $redirectRoute = 'admin.dashboard';
                    break;
                default:
                    $redirectRoute = 'login';
                    break;
            }

            return redirect()->route($redirectRoute)->with('error', 'สิทธิ์การใช้งานไม่ถูกต้องสำหรับช่องทางนี้');
        }

        return $next($request);
    }
}
