<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ForumPostController extends Controller
{
    public function update(Request $request, ForumPost $post)
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $post->update($data);

        return redirect()
            ->route('forums.thread', $post->forum_thread_id)
            ->with('status', 'Reply updated.');
    }

    public function destroy(ForumPost $post)
    {
        Gate::authorize('delete', $post);

        $threadId = $post->forum_thread_id;
        $post->delete();

        return redirect()
            ->route('forums.thread', $threadId)
            ->with('status', 'Reply deleted.');
    }
}
