<?php

namespace App\Policies;

use App\Models\Collocation;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollocationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Collocation $collocation): bool
    {
        return Membership::where('user_id', $user->id)
            ->where('collocation_id', $collocation->id)
            ->exists();
    }

    public function manage(User $user, Collocation $collocation): bool
    {
        $membership = Membership::where('user_id', $user->id)
            ->where('collocation_id', $collocation->id)
            ->first();
        return $membership && $membership->role === 'owner';
    }
}
