<?php

namespace App\Http\Controllers;

use App\Models\ShoutboxMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShoutboxController extends Controller
{
    /**
     * Recent messages as JSON — polled by the forum sidebar for a
     * near-live feel without a websocket layer.
     */
    public function index(): JsonResponse
    {
        $messages = ShoutboxMessage::with('user:id,name,avatar') // 1. Tambahkan kolom avatar di sini (sesuaikan dengan nama kolom di tabel user kamu)
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn(ShoutboxMessage $m) => [
                'id'     => $m->id,
                'name'   => $m->user?->name ?? 'Trainer',
                'avatar' => $m->user?->avatar ? asset('storage/' . $m->user->avatar) : null, // 2. Tambahkan key avatar di sini
                'body'   => $m->body,
                'ago'    => $m->created_at->diffForHumans(null, true) . ' ago',
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:280'],
        ]);

        $message = ShoutboxMessage::create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ]);

        $message->load('user:id,name');

        return response()->json([
            'message' => [
                'id'   => $message->id,
                'name' => $message->user?->name ?? 'Trainer',
                'body' => $message->body,
                'ago'  => 'just now',
            ],
        ], 201);
    }
}
