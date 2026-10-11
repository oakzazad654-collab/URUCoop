<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, $roles, true), 403, 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');

        return $next($request);
    }
}