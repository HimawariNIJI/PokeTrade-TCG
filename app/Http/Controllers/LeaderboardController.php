<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaderboardController extends Controller
{
    /**
     * Public trainer rankings. Three boards:
     *   · Collectors   — most unique cards in their digital collection
     *   · Bidders      — most live-auction wins (current_leader on ended auctions)
     *   · Point hoarders — highest loyalty point balance
     */
    public function index()
    {
        return view('pages.leaderboard.index', [
            'boards' => $this->boards(),
        ]);
    }

    /**
     * JSON snapshot of the same three boards, polled by the page so the
     * rankings update live without a full reload.
     */
    public function data()
    {
        return response()->json(['boards' => $this->boards()]);
    }

    /**
     * Build the three ranked boards from live data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function boards(): array
    {
        $collectors = User::query()
            ->select('users.*')
            ->selectSub(
                DB::table('collection_cards')
                    ->selectRaw('COUNT(DISTINCT card_id)')
                    ->whereColumn('user_id', 'users.id'),
                'collection_count'
            )
            ->orderByDesc('collection_count')
            ->limit(10)
            ->get()
            ->filter(fn ($u) => ($u->collection_count ?? 0) > 0)
            ->values();

        $bidders = User::query()
            ->select('users.*')
            ->selectSub(
                Auction::query()
                    ->selectRaw('COUNT(*)')
                    ->where('status', 'ended')
                    ->whereColumn('current_leader_id', 'users.id'),
                'wins_count'
            )
            ->orderByDesc('wins_count')
            ->limit(10)
            ->get()
            ->filter(fn ($u) => ($u->wins_count ?? 0) > 0)
            ->values();

        $pointHoarders = User::query()
            ->orderByDesc('points')
            ->limit(10)
            ->get()
            ->filter(fn ($u) => ($u->points ?? 0) > 0)
            ->values();

        return [
            $this->board('collectors', 'Top collectors', 'Deepest vaults', 'cards', $collectors, 'collection_count'),
            $this->board('bidders', 'Bid kings', 'Auction wins', 'wins', $bidders, 'wins_count'),
            $this->board('points', 'Point hoarders', 'Loyalty points', 'pts', $pointHoarders, 'points'),
        ];
    }

    /**
     * Shape one board into a render-and-JSON-friendly array.
     */
    private function board(string $key, string $title, string $eyebrow, string $metricLabel, Collection $users, string $metricKey): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'eyebrow' => $eyebrow,
            'metricLabel' => $metricLabel,
            'entries' => $users->values()->map(fn (User $u, int $i) => [
                'rank' => $i + 1,
                'name' => $u->name,
                'avatar' => $u->avatar,
                'profileUrl' => route('profiles.show', $u),
                'metric' => (int) ($u->{$metricKey} ?? 0),
            ])->all(),
        ];
    }
}
