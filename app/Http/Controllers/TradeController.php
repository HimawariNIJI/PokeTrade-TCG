<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index(Request $request)
    {
        $sent = Trade::with('items.card', 'receiver')
            ->where('sender_id', $request->user()->id)
            ->latest()
            ->limit(10)->get();

        $received = Trade::with('items.card', 'sender')
            ->where('receiver_id', $request->user()->id)
            ->latest()
            ->limit(10)->get();

        return view('pages.trades.index', compact('sent', 'received'));
    }

    public function create()
    {
        return view('pages.trades.create');
    }

    /**
     * TODO(team-backend): validate sender owns offer cards, receiver
     * owns request cards, create Trade + TradeItems in a transaction.
     */
    public function store(Request $request)
    {
        return redirect()->route('trades.index')
            ->with('status', 'Trade proposed (stub).');
    }

    public function show(Trade $trade)
    {
        // TODO(team-backend): authorize sender or receiver only
        return view('pages.trades.show', [
            'trade' => $trade->load('items.card', 'sender', 'receiver'),
        ]);
    }

    public function respond(Request $request, Trade $trade)
    {
        // TODO(team-backend): accept/reject — move card ownership on accept.
        return back()->with('status', 'Trade response recorded (stub).');
    }
}
