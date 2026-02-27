<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSingleCollocation
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->memberships()->count() > 0) {
            // If the user already has a collocation, redirect or return error
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already in a collocation.'
                ], 403);
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
