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
        $messages = ShoutboxMessage::with('user:id,name,avatar')
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn(ShoutboxMessage $m) => [
                'id'     => $m->id,
                'name'   => $m->user?->name ?? 'Trainer',
                'avatar' => $m->user?->avatar ? asset('storage/' . $m->user->avatar) : null,
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

        // 1. Pastikan Tambah ',avatar' di sini
        $message->load('user:id,name,avatar');

        return response()->json([
            'message' => [
                'id'     => $message->id,
                'name'   => $message->user?->name ?? 'Trainer',
                // 2. Tambahkan baris avatar ini agar saat kirim pesan baru, foto profil langsung ikut terkirim
                'avatar' => $message->user?->avatar ? asset('storage/' . $message->user->avatar) : null,
                'body'   => $message->body,
                'ago'    => 'just now',
            ],
        ], 201);
    }
}
