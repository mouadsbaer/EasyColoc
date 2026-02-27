<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', function () {
    return redirect()->route('home');
})->name('login');
Route::post('/api/register', [AuthController::class, 'register'])->name('register');
Route::post('/api/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/login', [AuthController::class, 'login']); // Fallback for standard forms

// Public invitation links (from email)
Route::get('/invitations/{token}/accept', [\App\Http\Controllers\InvitationController::class, 'accept'])->name('invitations.accept');
Route::get('/invitations/{token}/decline', [\App\Http\Controllers\InvitationController::class, 'decline'])->name('invitations.decline');

Route::middleware(['auth', 'banned'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\CollocationController::class, 'index'])->name('dashboard');

    Route::get('/collocation/{id}', function ($id) {
        $collocation = \App\Models\Collocation::with(['users', 'categories.expenses.user'])->findOrFail($id);
        return view('collocations.show', compact('collocation'));
    })->name('collocation');

    Route::get('/collocation/{id}/settlements', [\App\Http\Controllers\SettlementController::class, 'index'])->name('collocations.settlements');

    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');

    Route::get('/members', function () {
        $user = auth()->user();

        $members = $user->collocations
            ->flatMap->users
            ->unique('id')
            ->filter(fn($m) => $m->id !== $user->id)
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->pivot->role ?? 'member',
                    'reputation' => $member->reputation_points ?? 0,
                    'collocations' => $member->memberships()->count(),
                ];
            })->values();

        $myRatings = \App\Models\Rating::where('rater_id', $user->id)
            ->pluck('stars', 'rated_id');

        return view('members', compact('members', 'myRatings'));
    })->name('members');

    Route::get('/payment', function () {
        return view('payment');
    })->name('payment');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/view', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile'); // Alias for compatibility
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/linkedin', [\App\Http\Controllers\ProfileController::class, 'updateLinkedin'])->name('profile.linkedin.update');

    // API-like routes for AJAX calls (prefixed with /api for JS compatibility)
    Route::prefix('api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Collocations
        Route::get('/collocations', [\App\Http\Controllers\CollocationController::class, 'index'])->name('collocations.index');
        Route::post('/collocations', [\App\Http\Controllers\CollocationController::class, 'store'])->name('collocations.store')->middleware('single.collocation');
        Route::get('/collocations/{id}', [\App\Http\Controllers\CollocationController::class, 'show'])->name('collocations.show');
        Route::post('/collocations/{id}/leave', [\App\Http\Controllers\CollocationController::class, 'leave'])->name('collocations.leave');
        Route::delete('/collocations/{id}/cancel', [\App\Http\Controllers\CollocationController::class, 'cancel'])->name('collocations.cancel');
        Route::delete('/collocations/{collocation}/members/{user}', [\App\Http\Controllers\CollocationController::class, 'removeMember']);

        // Expenses
        Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store']);
        Route::put('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'update']);
        Route::delete('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'destroy']);

        // Invitations
        Route::post('/invitations', [\App\Http\Controllers\InvitationController::class, 'store']);

        // Payments
        Route::post('/payments', [\App\Http\Controllers\PaymentController::class, 'store']);
        Route::post('/payments/{id}/mark-paid', [\App\Http\Controllers\PaymentController::class, 'markPaid']);

        // Ratings
        Route::post('/ratings', [\App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');

        // Admin Routes
        Route::middleware(['admin'])->group(function () {
            Route::get('/admin/statistics', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
            Route::post('/admin/users/{id}/ban', [\App\Http\Controllers\Admin\DashboardController::class, 'banUser']);
            Route::post('/admin/users/{id}/unban', [\App\Http\Controllers\Admin\DashboardController::class, 'unbanUser']);
        });
    });

    // Admin Web Route
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'viewDashboard'])->name('admin.dashboard');
    });
});

Route::get('/help', function () {
    return view('help');
})->name('help');

Route::get('/forgot-password', function () {
    return view('forgot-password');
})->name('password.request');

// Handle legacy/placeholder routes if needed
