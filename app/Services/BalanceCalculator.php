<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\User;

class BalanceCalculator
{
    /**
     * Calculate balances for a collocation.
     * Returns an array of who owes whom.
     */
    public function calculate(Collocation $collocation)
    {
        $memberships = $collocation->memberships()->with('user')->get();
        $count = $memberships->count();
        if ($count <= 1)
            return [];

        // 1. Get each member's balance
        $balances = [];
        foreach ($memberships as $membership) {
            $balances[$membership->user_id] = (float) $membership->balance;
        }

        // 2. Separate into debtors (negative balance) and creditors (positive balance)
        $debtors = [];
        $creditors = [];

        foreach ($balances as $userId => $balance) {
            if ($balance < 0) {
                $debtors[] = ['id' => $userId, 'amount' => abs($balance)];
            } elseif ($balance > 0) {
                $creditors[] = ['id' => $userId, 'amount' => $balance];
            }
        }

        // 3. Simplify debts (Greedy approach)
        $transactions = [];
        $i = 0;
        $j = 0;

        while ($i < count($debtors) && $j < count($creditors)) {
            $debtor = &$debtors[$i];
            $creditor = &$creditors[$j];

            $amount = min($debtor['amount'], $creditor['amount']);

            $transactions[] = [
                'from_id' => $debtor['id'],
                'to_id' => $creditor['id'],
                'amount' => round($amount, 2),
                'from_name' => User::find($debtor['id'])->name,
                'to_name' => User::find($creditor['id'])->name,
            ];

            $debtor['amount'] -= $amount;
            $creditor['amount'] -= $amount;

            if ($debtor['amount'] < 0.01)
                $i++;
            if ($creditor['amount'] < 0.01)
                $j++;
        }

        return $transactions;
    }

    /**
     * Update balances after a new expense is added.
     */
    public function updateBalancesAfterExpense($expense)
    {
        $category = $expense->category;
        $collocation = $category->collocation;
        $payer = $expense->user;
        $amount = $expense->amount;
        $members = $collocation->memberships;
        $count = $members->count();

        if ($count == 0)
            return;

        $splitAmount = $amount / $count;

        foreach ($members as $membership) {
            if ($membership->user_id == $payer->id) {
                // Payer gets credit for (total - their share)
                $membership->balance += ($amount - $splitAmount);
            } else {
                // Others get debt for their share
                $membership->balance -= $splitAmount;
            }
            $membership->save();
        }
    }

    /**
     * Update balances after an expense is deleted.
     */
    public function updateBalancesAfterDelete($expense)
    {
        $category = $expense->category;
        $collocation = $category->collocation;
        $payer = $expense->user;
        $amount = $expense->amount;
        $members = $collocation->memberships;
        $count = $members->count();

        if ($count == 0)
            return;

        $splitAmount = $amount / $count;

        foreach ($members as $membership) {
            if ($membership->user_id == $payer->id) {
                $membership->balance -= ($amount - $splitAmount);
            } else {
                $membership->balance += $splitAmount;
            }
            $membership->save();
        }
    }
}
