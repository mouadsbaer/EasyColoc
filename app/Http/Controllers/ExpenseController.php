<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Services\BalanceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    protected $balanceCalculator;

    public function __construct(BalanceCalculator $balanceCalculator)
    {
        $this->balanceCalculator = $balanceCalculator;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $expense = Expense::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
            'date' => $request->date,
        ]);

        $this->balanceCalculator->updateBalancesAfterExpense($expense);

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully',
            'data' => $expense
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $expense = Expense::findOrFail($id);
        $category = $expense->category;
        $collocation = $category->collocation;

        if (Auth::id() !== $expense->user_id && Auth::user()->cannot('manage', $collocation)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        // Reverse old balances
        $this->balanceCalculator->updateBalancesAfterDelete($expense);

        // Update expense (do not let them change category here without more complex logic, stick to basic string fields)
        $expense->update([
            'title' => $request->title,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);

        // Apply new balances
        $this->balanceCalculator->updateBalancesAfterExpense($expense);

        return response()->json([
            'success' => true,
            'message' => 'Dépense mise à jour avec succès.',
            'data' => $expense
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $category = $expense->category;
        $collocation = $category->collocation;

        if (Auth::id() !== $expense->user_id && Auth::user()->cannot('manage', $collocation)) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $this->balanceCalculator->updateBalancesAfterDelete($expense);
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dépense supprimée avec succès.'
        ]);
    }
}
