<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Collocation;
use App\Models\Expense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users_count' => User::count(),
            'collocations_count' => Collocation::count(),
            'expenses_total' => Expense::sum('amount'),
            'recent_users' => User::latest()->limit(5)->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function viewDashboard(Request $request)
    {
        $query = User::withCount('memberships')->orderBy('created_at', 'desc');

        if ($request->has('filter') && $request->filter === 'banned') {
            $query->where('is_banned', true);
        }

        $users = $query->paginate(15)->appends($request->query());
        $totalColocs = Collocation::count();
        $totalExpenses = Expense::sum('amount');
        $bannedCount = User::where('is_banned', true)->count();

        return view('admin.dashboard', compact('users', 'totalColocs', 'totalExpenses', 'bannedCount'));
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot ban an administrator.'
            ], 403);
        }

        $user->is_banned = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User banned successfully.'
        ]);
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_banned = false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User unbanned successfully.'
        ]);
    }
}
