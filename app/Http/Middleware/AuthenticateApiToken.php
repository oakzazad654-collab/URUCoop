<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateApiToken
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::where('api_token_hash', hash('sha256', $token))->first();
        if (!$user || $user->status !== 'ACTIVE') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        Auth::setUser($user);

        return $next($request);
    }
}
