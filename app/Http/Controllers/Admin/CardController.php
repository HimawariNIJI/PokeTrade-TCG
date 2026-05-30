<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Database\Seeders\CardSeeder;
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

    /**
     * Re-pull the Standard-legal card catalogue from pokemontcg.io and
     * refresh market prices + today's price-history snapshot. New cards
     * are inserted; existing cards have their API-sourced fields updated
     * while admin-managed columns (stock, featured) are preserved.
     *
     * Runs synchronously: ~5–10s with batched upserts. Must finish before
     * nginx's fastcgi_read_timeout (default 60s) or the request 504s.
     */
    public function refresh(Request $request)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $countBefore = Card::count();

        try {
            (new CardSeeder())->run();
        } catch (\Throwable $e) {
            return back()->with('status', 'Card refresh failed: ' . $e->getMessage());
        }

        $added = max(0, Card::count() - $countBefore);

        return back()->with(
            'status',
            "Card catalogue refreshed from pokemontcg.io. {$added} new card(s) added; prices updated."
        );
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
