<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Models\Membership;
use App\Services\ReputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CollocationController extends Controller
{
    protected $reputationService;

    public function __construct(ReputationService $reputationService)
    {
        $this->reputationService = $reputationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $collocations = $user->collocations;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $collocations
            ]);
        }

        return view('collocations.index', compact('collocations'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->memberships()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà dans une colocation active.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $collocation = Collocation::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        Membership::create([
            'user_id' => Auth::id(),
            'collocation_id' => $collocation->id,
            'role' => 'owner',
        ]);

        session()->flash('success', 'Collocation created successfully');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Collocation created successfully',
                'redirect' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function show($id)
    {
        $collocation = Collocation::with(['users', 'categories.expenses.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $collocation
        ]);
    }

    public function leave($id)
    {
        $user = Auth::user();
        $membership = Membership::where('user_id', $user->id)
            ->where('collocation_id', $id)
            ->firstOrFail();

        if ($membership->role === 'owner' && $membership->collocation->users()->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Owner cannot leave while there are other members. Transfer ownership first.'
            ], 403);
        }

        $balance = $membership->balance;

        if ($balance < 0) {
            $this->reputationService->subtractPoints($user, 1, "Left collocation with debt");
        } else {
            $this->reputationService->addPoints($user, 1, "Left collocation without debt");
        }

        $membership->delete();

        // If no members left, delete collocation
        if (Collocation::find($id)->memberships()->count() === 0) {
            Collocation::find($id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Left collocation successfully',
            'redirect' => route('dashboard')
        ]);
    }

    public function removeMember(Request $request, $collocationId, $userId)
    {
        $collocation = Collocation::findOrFail($collocationId);
        $currentUser = Auth::user();

        // 1. Policy Authorization Check
        if ($currentUser->cannot('manage', $collocation)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée. Seul le propriétaire peut retirer un membre.'], 403);
        }

        $targetMembership = $collocation->memberships()->where('user_id', $userId)->firstOrFail();

        // 2. Prevent Owner Mutiny
        if ($targetMembership->role === 'owner') {
            return response()->json(['success' => false, 'message' => 'Impossible de retirer un autre propriétaire.'], 403);
        }

        $balance = $targetMembership->balance;

        // 3. Reputation and Debt Logic
        if ($balance < 0) {
            $this->reputationService->subtractPoints($targetMembership->user, 1, "Kicked out with debt");
            $ownerMembership = $collocation->memberships()->where('user_id', $currentUser->id)->first();
            $ownerMembership->balance += $balance;
            $ownerMembership->save();
        } else {
            $this->reputationService->addPoints($targetMembership->user, 1, "Removed without debt");
        }

        // 4. Soft Delete equivalent via delete
        $targetMembership->delete();

        return response()->json(['success' => true, 'message' => 'Membre retiré avec succès']);
    }

    public function cancel(Request $request, $id)
    {
        $collocation = Collocation::with('memberships.user')->findOrFail($id);
        $currentUser = Auth::user();

        // 1. Policy Authorization Check (Only Owner can cancel)
        if ($currentUser->cannot('manage', $collocation)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée. Seul le propriétaire peut annuler la colocation.'], 403);
        }

        // 2. Pre-process reputation changes for ALL members (including owner) before destroying the matrix
        foreach ($collocation->memberships as $membership) {
            if ($membership->balance < 0) {
                // Member has debt
                $this->reputationService->subtractPoints($membership->user, 1, "Colocation cancelled with debt");
            } else {
                // Member has zero or positive balance
                $this->reputationService->addPoints($membership->user, 1, "Colocation cancelled without debt");
            }
            // Sever the membership so users can physically join another colocation later
            $membership->delete();
        }

        // 3. Mark the collocation as purely cancelled
        $collocation->status = 'cancelled';
        $collocation->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'La colocation a été annulée avec succès. Vous êtes maintenant libre.',
                'redirect' => route('dashboard')
            ]);
        }

        session()->flash('success', 'Colocation annulée avec succès.');
        return redirect()->route('dashboard');
    }
}
