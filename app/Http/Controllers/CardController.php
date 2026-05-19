<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CardController extends Controller
{
    /**
     * Price tracker — browse every Standard-legal Prismatic Evolutions
     * card and track its market value. Search, filter by
     * type/supertype/rarity/set/regulation, sort, paginate. Cards are
     * not sold here; this is a value-tracking catalogue.
     */
    public function index(Request $request)
    {
        $query = Card::query();

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->string('type')->toString()) {
            // JSON contains works on SQLite + MySQL
            $query->whereJsonContains('types', $type);
        }

        if ($supertype = $request->string('supertype')->toString()) {
            $query->where('supertype', $supertype);
        }

        if ($rarity = $request->string('rarity')->toString()) {
            $query->where('rarity', $rarity);
        }

        if ($setId = $request->string('set')->toString()) {
            $query->where('set_id', $setId);
        }

        if ($regMark = $request->string('regulation')->toString()) {
            $query->where('regulation_mark', $regMark);
        }

        $sort = $request->string('sort', 'number')->toString();
        match ($sort) {
            'price_asc'  => $query->orderBy('market_price'),
            'price_desc' => $query->orderByDesc('market_price'),
            'name'       => $query->orderBy('name'),
            'rarity'     => $query->orderByRaw("CASE WHEN rarity LIKE '%Special Illustration%' THEN 1 WHEN rarity LIKE '%Hyper%' THEN 2 WHEN rarity LIKE '%Illustration%' THEN 3 WHEN rarity LIKE '%Ultra%' THEN 4 ELSE 5 END"),
            default      => $query->orderBy('set_id')->orderByRaw('CAST(number AS UNSIGNED) ASC'),
        };

        $cards = $query->paginate(24)->withQueryString();

        $allTypes = Card::query()
            ->whereNotNull('types')
            ->pluck('types')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values();

        $allRarities = Card::query()
            ->whereNotNull('rarity')
            ->pluck('rarity')
            ->unique()
            ->filter()
            ->sort()
            ->values();

        $allSets = Card::query()
            ->select('set_id', 'set_name')
            ->whereNotNull('set_id')
            ->groupBy('set_id', 'set_name')
            ->orderBy('set_name')
            ->get();

        $allRegMarks = Card::query()
            ->whereNotNull('regulation_mark')
            ->distinct()
            ->orderBy('regulation_mark')
            ->pluck('regulation_mark');

        return view('pages.cards.index', [
            'cards' => $cards,
            'allTypes' => $allTypes,
            'allRarities' => $allRarities,
            'allSets' => $allSets,
            'allRegMarks' => $allRegMarks,
        ]);
    }

    public function show(Card $card)
    {
        $related = Card::query()
            ->where('id', '!=', $card->id)
            ->when($card->types, fn ($q) => $q->whereJsonContains('types', $card->types[0] ?? ''))
            ->limit(4)
            ->get();

        // Price-tracker history + derived change stats.
        $history = $card->priceHistory()->get();
        $priceStats = $this->priceStats($history);

        // Where this card sits in its set.
        $setTotal = Card::where('set_id', $card->set_id)->count();
        $setPosition = Card::where('set_id', $card->set_id)
            ->whereRaw('CAST(number AS UNSIGNED) <= ?', [(int) $card->number])
            ->count();

        // Community & market activity around this card.
        $chaserCount = $card->wishlistedBy()->count();

        $activeAuctions = $card->auctions()
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('ends_at')
            ->get();

        $forumThreads = ForumThread::query()
            ->where(fn ($q) => $q
                ->where('title', 'like', '%' . $card->name . '%')
                ->orWhere('body', 'like', '%' . $card->name . '%'))
            ->withCount('posts')
            ->latest('last_posted_at')
            ->limit(4)
            ->get();

        // Evolution chain — resolve names to catalogue cards when possible
        // so the stages can link through.
        $evolvesFrom = $card->evolves_from
            ? Card::where('name', $card->evolves_from)->first()
            : null;

        $evolvesTo = collect($card->evolves_to ?? [])
            ->map(fn ($name) => [
                'name' => $name,
                'card' => Card::where('name', $name)->first(),
            ]);

        return view('pages.cards.show', [
            'card' => $card,
            'related' => $related,
            'history' => $history,
            'priceStats' => $priceStats,
            'setTotal' => $setTotal,
            'setPosition' => $setPosition,
            'chaserCount' => $chaserCount,
            'activeAuctions' => $activeAuctions,
            'forumThreads' => $forumThreads,
            'evolvesFrom' => $evolvesFrom,
            'evolvesTo' => $evolvesTo,
        ]);
    }

    /**
     * Derive 7d/30d percentage change plus the all-time high/low
     * from a card's daily price-history snapshots.
     */
    private function priceStats(Collection $history): array
    {
        if ($history->count() < 2) {
            return ['change7d' => null, 'change30d' => null, 'high' => null, 'low' => null];
        }

        $current = (float) $history->last()->market_price;
        $prices = $history->map(fn ($p) => (float) $p->market_price);

        $pctSince = function (int $days) use ($history, $current): ?float {
            $cutoff = today()->subDays($days);
            $base = $history->first(fn ($p) => $p->recorded_at->greaterThanOrEqualTo($cutoff))
                ?? $history->first();
            $basePrice = (float) $base->market_price;

            return $basePrice > 0
                ? round((($current - $basePrice) / $basePrice) * 100, 1)
                : null;
        };

        return [
            'change7d' => $pctSince(7),
            'change30d' => $pctSince(30),
            'high' => $prices->max(),
            'low' => $prices->min(),
        ];
    }
}
