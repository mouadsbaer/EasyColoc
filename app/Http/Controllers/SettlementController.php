<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Services\BalanceCalculator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SettlementController extends Controller
{
    use AuthorizesRequests;

    protected $balanceCalculator;

    public function __construct(BalanceCalculator $balanceCalculator)
    {
        $this->balanceCalculator = $balanceCalculator;
    }

    public function index($id)
    {
        $collocation = Collocation::with('users')->findOrFail($id);

        $this->authorize('view', $collocation);

        $transactions = $this->balanceCalculator->calculate($collocation);

        return view('settlements.index', compact('collocation', 'transactions'));
    }
}
