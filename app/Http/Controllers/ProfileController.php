<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'firstName' => 'sometimes|string|max:255',
            'lastName' => 'sometimes|string|max:255',
            'about' => 'sometimes|nullable|string|max:500',
            'datOfBirth' => 'sometimes|nullable|date',
            'linkedin' => 'sometimes|nullable|url|max:255',
        ]);

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour !',
                'data' => $user,
            ]);
        }

        return redirect()->route('profile')->with('status', 'profile-updated');
    }

    public function updateLinkedin(Request $request)
    {
        $request->validate([
            'linkedin' => 'required|url|max:255',
        ]);

        $request->user()->update(['linkedin' => $request->linkedin]);

        return response()->json([
            'success' => true,
            'message' => 'LinkedIn enregistré !',
            'linkedin' => $request->linkedin,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
