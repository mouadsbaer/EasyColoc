<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Models\Membership;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    protected $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'collocation_id' => 'required|exists:collocations,id',
            'customMessage' => 'nullable|string|max:500' // Ensure this matches UI if added
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $collocation = Collocation::findOrFail($request->collocation_id);
        $user = Auth::user();

        // Pass sendBy_id
        $invitation = $this->invitationService->generateInvitation($collocation, $request->email, $user->id);

        // Send actual email
        \Illuminate\Support\Facades\Mail::to($request->email)->send(
            new \App\Mail\ColocationInvitation($collocation, $user, $invitation->token, $request->customMessage ?? null)
        );

        return response()->json([
            'success' => true,
            'message' => 'Invitation envoyée avec succès à ' . $request->email,
        ]);
    }

    public function accept(Request $request, $token)
    {
        $invitation = \App\Models\Invitation::where('token', $token)->where('status', 'pending')->first();

        if (!$invitation) {
            return redirect('/')->with('error', 'Invitation invalide ou expirée.');
        }

        if (Auth::check()) {
            $user = Auth::user();

            // Prevent user from accepting an invitation sent to another email while logged in
            if ($user->email !== $invitation->email) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                session(['invitation_token' => $token]);
                return redirect('/?action=register')->with('info', "L'invitation est pour {$invitation->email}. Veuillez vous déconnecter du compte actuel et créer un nouveau compte (ou vous connecter) avec cet email.");
            }

            if ($user->memberships()->count() > 0) {
                return redirect()->route('dashboard')->with('error', 'Vous êtes déjà dans une colocation.');
            }

            $collocation = $this->invitationService->acceptInvitation($token, $user);

            Membership::create([
                'user_id' => $user->id,
                'collocation_id' => $collocation->id,
                'role' => 'member',
            ]);

            return redirect()->route('collocation', $collocation->id)->with('success', 'Bienvenue dans la colocation !');
        }

        // Not logged in -> Save token in session, go to register
        session(['invitation_token' => $token]);
        return redirect('/?action=register')->with('info', 'Créez un compte avec ' . $invitation->email . ' pour rejoindre la colocation.');
    }

    public function decline($token)
    {
        $declined = $this->invitationService->declineInvitation($token);

        if (!$declined) {
            return redirect('/')->with('error', 'Invitation invalide ou déjà traitée.');
        }

        return redirect('/')->with('success', 'L\'invitation a été refusée.');
    }
}
