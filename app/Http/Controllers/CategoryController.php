<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'collocation_id' => 'required|exists:collocations,id',
        ]);

        // Make sure the authenticated user belongs to this collocation
        $collocation = Collocation::findOrFail($request->collocation_id);
        $isMember = $collocation->users()->where('user_id', Auth::id())->exists();

        if (!$isMember) {
            return back()->withErrors(['error' => 'Accès refusé.']);
        }

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'collocation_id' => $request->collocation_id,
        ]);

        // Redirect back to the collocation show page (refresh)
        return redirect()->route('collocation', $request->collocation_id)
            ->with('success', 'Catégorie créée avec succès !');
    }
}
