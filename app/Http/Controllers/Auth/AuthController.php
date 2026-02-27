<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('welcome');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if ($user->is_banned) {
                Auth::logout();
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Your account has been banned.'], 403);
                }
                return back()->withErrors(['email' => 'Your account has been banned.']);
            }

            $redirectUrl = $user->is_admin ? route('admin.dashboard') : route('dashboard');
            if (session()->has('invitation_token')) {
                $token = session()->get('invitation_token');
                $invitation = \App\Models\Invitation::where('token', $token)->first();

                if ($invitation && $invitation->email === $user->email) {
                    session()->pull('invitation_token');
                    $redirectUrl = route('invitations.accept', ['token' => $token]);
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $redirectUrl
                ]);
            }
            return redirect()->intended($redirectUrl);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $isFirstUser = User::count() === 0;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_admin' => $isFirstUser,
        ]);

        Auth::login($user);

        $redirectUrl = $user->is_admin ? route('admin.dashboard') : route('dashboard');
        if (session()->has('invitation_token')) {
            $token = session()->get('invitation_token');
            $invitation = \App\Models\Invitation::where('token', $token)->first();

            if ($invitation && $invitation->email === $user->email) {
                session()->pull('invitation_token');
                $redirectUrl = route('invitations.accept', ['token' => $token]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'redirect' => $redirectUrl
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => '/'
        ]);
    }
}
