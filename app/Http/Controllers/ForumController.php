<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\ShoutboxMessage;
use App\Notifications\ForumReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ForumController extends Controller
{
    /**
     * Forum landing — category board, recent discussions, search, shoutbox.
     */
    public function index(Request $request)
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

        // Thread search (title or body).
        $query = trim((string) $request->query('q', ''));
        $results = null;
        if ($query !== '') {
            $results = ForumThread::query()
                ->with('author', 'category')
                ->withCount('posts')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%'.$query.'%')
                      ->orWhere('body', 'like', '%'.$query.'%');
                })
                ->orderByDesc('last_posted_at')
                ->paginate(10)
                ->withQueryString();
        }

        // Persisted community shoutbox (newest first; the panel reverses it).
        $shouts = ShoutboxMessage::with('user')->latest()->limit(30)->get();

        return view('pages.forums.index', compact(
            'categories', 'recentThreads', 'query', 'results', 'shouts'
        ));
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
     * A single thread — original post plus the paginated reply timeline.
     */
    public function thread(ForumThread $thread)
    {
        $thread->increment('views');
        $thread->load('category', 'author');

        $posts = $thread->posts()->with('author')->paginate(20);

        return view('pages.forums.thread', compact('thread', 'posts'));
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
     * Edit the opening post (author or admin).
     */
    public function edit(ForumThread $thread)
    {
        Gate::authorize('update', $thread);

        $categories = ForumCategory::orderBy('position')->get();

        return view('pages.forums.edit', compact('thread', 'categories'));
    }

    public function update(Request $request, ForumThread $thread)
    {
        Gate::authorize('update', $thread);

        $data = $request->validate([
            'forum_category_id' => ['required', 'exists:forum_categories,id'],
            'title'             => ['required', 'string', 'max:160'],
            'body'              => ['required', 'string', 'max:10000'],
        ]);

        $thread->update($data);

        return redirect()
            ->route('forums.thread', $thread)
            ->with('status', 'Thread updated.');
    }

    public function destroy(ForumThread $thread)
    {
        Gate::authorize('delete', $thread);

        $category = $thread->category;
        $thread->delete();

        return redirect()
            ->route($category ? 'forums.category' : 'forums.index', $category ?? [])
            ->with('status', 'Thread deleted.');
    }

    /**
     * Reply to a thread (blocked when locked, unless admin).
     */
    public function reply(Request $request, ForumThread $thread)
    {
        if ($thread->locked && ! $request->user()->isAdmin()) {
            return redirect()
                ->route('forums.thread', $thread)
                ->with('status', 'This thread is locked — no new replies.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        ForumPost::create([
            'forum_thread_id' => $thread->id,
            'user_id'         => $request->user()->id,
            'body'            => $data['body'],
        ]);

        $thread->update(['last_posted_at' => now()]);

        // Tell the thread author someone replied (not for self-replies).
        if ($thread->author && $thread->user_id !== $request->user()->id) {
            $thread->author->notify(new ForumReplyNotification($thread, $request->user()));
        }

        return redirect()
            ->route('forums.thread', $thread)
            ->with('status', 'Reply posted.');
    }

    /**
     * Toggle pin (moderator only).
     */
    public function togglePin(ForumThread $thread)
    {
        Gate::authorize('moderate', $thread);
        $thread->update(['pinned' => ! $thread->pinned]);

        return back()->with('status', $thread->pinned ? 'Thread pinned.' : 'Thread unpinned.');
    }

    /**
     * Toggle lock (moderator only).
     */
    public function toggleLock(ForumThread $thread)
    {
        Gate::authorize('moderate', $thread);
        $thread->update(['locked' => ! $thread->locked]);

        return back()->with('status', $thread->locked ? 'Thread locked.' : 'Thread unlocked.');
    }
}
