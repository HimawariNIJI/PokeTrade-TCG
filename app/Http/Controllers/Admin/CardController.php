<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = Card::query()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderByRaw('CAST(number AS UNSIGNED) ASC')
            ->paginate(20)->withQueryString();

        return view('admin.cards.index', compact('cards'));
    }

    public function create()
    {
        return view('admin.cards.create');
    }

    /**
     * TODO(team-backend): validate via Form Request, persist, attach uploaded image.
     * Card is sourced from API but admin can override price/stock/featured/description.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.cards.index')->with('status', 'Card saved (stub).');
    }

    public function edit(Card $card)
    {
        return view('admin.cards.edit', compact('card'));
    }

    /**
     * TODO(team-backend): validate via Form Request, update price/stock/featured/etc.
     */
    public function update(Request $request, Card $card)
    {
        return redirect()->route('admin.cards.index')->with('status', 'Card updated (stub).');
    }

    public function destroy(Card $card)
    {
        // TODO(team-backend): soft-delete or hard-delete with confirm.
        return back()->with('status', 'Card deleted (stub).');
    }
}
