<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rated_id' => 'required|exists:users,id',
            'stars' => 'required|integer|min:1|max:5',
        ]);

        $raterId = Auth::id();
        $ratedId = $request->rated_id;

        // Cannot rate yourself
        if ($raterId === $ratedId) {
            return response()->json(['message' => 'Vous ne pouvez pas vous noter vous-même.'], 403);
        }

        // Both users must share at least one collocation
        $rater = Auth::user();
        $raterCollocationIds = $rater->memberships()->pluck('collocation_id');
        $rated = User::findOrFail($ratedId);
        $ratedCollocationIds = $rated->memberships()->pluck('collocation_id');
        $shared = $raterCollocationIds->intersect($ratedCollocationIds);

        if ($shared->isEmpty()) {
            return response()->json(['message' => 'Vous devez partager une collocation pour noter ce membre.'], 403);
        }

        // Upsert: update if already rated, insert otherwise
        $existing = Rating::where('rater_id', $raterId)->where('rated_id', $ratedId)->first();
        $oldStars = $existing ? $existing->stars : null;

        Rating::updateOrCreate(
            ['rater_id' => $raterId, 'rated_id' => $ratedId],
            ['stars' => $request->stars]
        );

        // Recalculate reputation_points as average stars * 20 across all ratings
        $avgStars = Rating::where('rated_id', $ratedId)->avg('stars') ?? 0;
        $newReputation = (int) round($avgStars * 20); // max 100 pts (5 stars × 20)
        $rated->reputation_points = $newReputation;
        $rated->save();

        return response()->json([
            'success' => true,
            'message' => 'Note enregistrée !',
            'reputation' => $newReputation,
            'avg_stars' => round($avgStars, 1),
        ]);
    }
}
