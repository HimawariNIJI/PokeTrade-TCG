<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Database\Seeders\CardSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

    /**
     * Re-pull the Standard-legal card catalogue from pokemontcg.io and
     * refresh market prices. New cards are inserted; existing cards have
     * their market_price (and price-history snapshot) updated.
     *
     * Long-running (~60s on a cold cache) — runs synchronously since this
     * is an admin-only action and there is no queue worker.
     */
    public function refresh(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $countBefore = Card::count();

        try {
            (new CardSeeder())->run();
            Artisan::call('cards:refresh-prices');
        } catch (\Throwable $e) {
            return back()->with('status', 'Card refresh failed: ' . $e->getMessage());
        }

        $added = max(0, Card::count() - $countBefore);

        return back()->with(
            'status',
            "Card catalogue refreshed from pokemontcg.io. {$added} new card(s) added; prices updated."
        );
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
