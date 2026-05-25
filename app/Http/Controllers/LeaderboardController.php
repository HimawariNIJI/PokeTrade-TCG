<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Public trainer rankings. Three boards:
     *   · Collectors — most unique cards in their digital collection
     *   · Bidders    — most live-auction wins (current_leader on ended auctions)
     *   · Big spenders — highest loyalty point balance
     */
    public function index()
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

        return view('pages.leaderboard.index', [
            'collectors'     => $collectors,
            'bidders'        => $bidders,
            'pointHoarders'  => $pointHoarders,
        ]);
    }
}
