<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReputationLog;

class ReputationService
{
    public function addPoints(User $user, int $points, string $reason)
    {
        $user->reputation_points += $points;
        $user->save();

        ReputationLog::create([
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
        ]);
    }

    public function subtractPoints(User $user, int $points, string $reason)
    {
        $user->reputation_points -= $points;
        $user->save();

        ReputationLog::create([
            'user_id' => $user->id,
            'points' => -$points,
            'reason' => $reason,
        ]);
    }
}
