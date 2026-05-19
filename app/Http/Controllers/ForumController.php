<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * Forum landing — category board, recent discussions, live chat panel.
     */
    public function index()
    {
        $categories = ForumCategory::query()
            ->withCount('threads')
            ->with(['threads' => fn ($q) => $q->latest('last_posted_at')->limit(1)])
            ->orderBy('position')
            ->get();

        $recentThreads = ForumThread::query()
            ->with('author', 'category')
            ->withCount('posts')
            ->orderByDesc('last_posted_at')
            ->limit(6)
            ->get();

        // TODO(team-backend): persist + broadcast live chat — these messages
        // are static demo data. Replace with a real chat store + WebSocket
        // broadcast so the panel updates for every connected member.
        $chatMessages = [
            ['name' => 'Mika',     'body' => 'Just pulled a Hyper Rare Eevee ex 😭 best day ever',           'minutes_ago' => 2],
            ['name' => 'Reza',     'body' => 'anyone trading the SIR Sylveon? have doubles',                  'minutes_ago' => 5],
            ['name' => 'Dewi',     'body' => 'the gacha rates feel generous this week ngl',                   'minutes_ago' => 9],
            ['name' => 'Owen',     'body' => 'bid war on that Umbreon auction is wild rn',                    'minutes_ago' => 14],
            ['name' => 'Sari',     'body' => 'finished my Prismatic Evolutions binder finally ✨',             'minutes_ago' => 21],
            ['name' => 'Bagus',    'body' => 'new merch drop when? need that playmat',                        'minutes_ago' => 33],
            ['name' => 'Lin',      'body' => 'grading question — anyone used the new submission flow?',       'minutes_ago' => 48],
            ['name' => 'Hana',     'body' => 'gm collectors ☀️ what are we chasing today',                    'minutes_ago' => 60],
        ];

        return view('pages.forums.index', compact('categories', 'recentThreads', 'chatMessages'));
    }

    /**
     * A single category — paginated thread list, pinned first.
     */
    public function category(ForumCategory $category)
    {
        $threads = $category->threads()
            ->with('author')
            ->withCount('posts')
            ->orderByDesc('pinned')
            ->orderByDesc('last_posted_at')
            ->paginate(15);

        return view('pages.forums.category', compact('category', 'threads'));
    }

    /**
     * A single thread — original post plus the reply timeline.
     */
    public function thread(ForumThread $thread)
    {
        $thread->increment('views');
        $thread->load('category', 'author', 'posts.author');

        return view('pages.forums.thread', compact('thread'));
    }

    /**
     * New thread form.
     */
    public function create()
    {
        $categories = ForumCategory::orderBy('position')->get();

        return view('pages.forums.create', compact('categories'));
    }

    /**
     * Persist a new thread, then jump straight into it.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'forum_category_id' => ['required', 'exists:forum_categories,id'],
            'title'             => ['required', 'string', 'max:160'],
            'body'              => ['required', 'string', 'max:10000'],
        ]);

        $thread = ForumThread::create([
            'forum_category_id' => $data['forum_category_id'],
            'user_id'           => auth()->id(),
            'title'             => $data['title'],
            'body'              => $data['body'],
            'last_posted_at'    => now(),
        ]);

        return redirect()
            ->route('forums.thread', $thread)
            ->with('status', 'Thread posted — welcome to the conversation.');
    }

    /**
     * Add a reply to a thread and bump its activity timestamp.
     */
    public function reply(Request $request, ForumThread $thread)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        ForumPost::create([
            'forum_thread_id' => $thread->id,
            'user_id'         => auth()->id(),
            'body'            => $data['body'],
        ]);

        $thread->update(['last_posted_at' => now()]);

        return redirect()
            ->route('forums.thread', $thread)
            ->with('status', 'Reply posted.');
    }
}
