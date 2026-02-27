<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\Invitation;
use Illuminate\Support\Str;

class InvitationService
{
    public function generateInvitation(Collocation $collocation, string $email, int $sendById)
    {
        return Invitation::create([
            'collocation_id' => $collocation->id,
            'sendBy_id' => $sendById,
            'email' => $email,
            'token' => Str::random(32),
            'status' => 'pending',
        ]);
    }

    public function acceptInvitation(string $token, $user)
    {
        $invitation = Invitation::where('token', $token)->where('status', 'pending')->first();

        if (!$invitation) {
            return null;
        }

        $invitation->status = 'accepted';
        $invitation->acceptedBy_id = $user->id;
        $invitation->save();

        return $invitation->collocation;
    }

    public function declineInvitation(string $token)
    {
        $invitation = Invitation::where('token', $token)->where('status', 'pending')->first();

        if ($invitation) {
            $invitation->status = 'declined';
            $invitation->save();
            return true;
        }

        return false;
    }
}
