<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded as pending.',
            'data' => $payment
        ]);
    }

    public function markPaid($id)
    {
        $payment = Payment::findOrFail($id);

        // Only receiver can mark as paid
        if ($payment->receiver_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the receiver can confirm the payment.'
            ], 403);
        }

        $payment->status = 'paid';
        $payment->save();

        // Update balances in memberships
        $senderMembership = Membership::where('user_id', $payment->sender_id)->first();
        $receiverMembership = Membership::where('user_id', $payment->receiver_id)->first();

        if ($senderMembership && $receiverMembership) {
            $senderMembership->balance += $payment->amount; // Debt decreased
            $receiverMembership->balance -= $payment->amount; // Credit decreased
            $senderMembership->save();
            $receiverMembership->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid and balances updated.'
        ]);
    }
}
