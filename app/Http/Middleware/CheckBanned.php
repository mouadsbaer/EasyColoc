<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => false,
                'message' => 'Your account has been banned.',
                'redirect' => '/login'
            ], 403);
        }

        return $next($request);
    }
}
