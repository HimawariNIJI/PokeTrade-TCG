<?php

namespace App\Http\Controllers;

use App\Models\ProfileComment;
use App\Models\User;
use App\Notifications\ProfileCommentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public-facing Trainer Profiles.
 *
 * Every trainer gets a shareable wall at /u/{user}. What's actually
 * shown is gated by the owner's privacy toggles (see User::shows()),
 * so this controller hands the view everything and lets the Blade
 * decide what to render.
 */
class PublicProfileController extends Controller
{
    /**
     * Show a trainer's public profile.
     */
    public function show(User $user): View
    {
        // Digital (gacha) collection — cap the eager-loaded rows for
        // display, but keep an honest total count for the stat chip.
        $user->load([
            'digitalCards' => fn ($q) => $q->latest('collection_cards.obtained_at')->limit(12),
            'wishlistedCards',
            'profileComments.author',
        ]);

        $digitalCount = $user->digitalCards()->count();
        $chaseCount   = $user->wishlistedCards()->count();

        // Pinned showcase — the cards the trainer chose to highlight.
        // Falls back to the empty collection if they haven't pinned any.
        $pinnedCards = $user->pinnedShowcase();

        return view('pages.profiles.show', [
            'user'         => $user,
            'digitalCards' => $user->digitalCards,
            'chaseCards'   => $user->wishlistedCards,
            'comments'     => $user->profileComments,
            'digitalCount' => $digitalCount,
            'chaseCount'   => $chaseCount,
            'pinnedCards'  => $pinnedCards,
        ]);
    }

    /**
     * Leave a comment on a trainer's wall.
     */
    public function comment(Request $request, User $user): RedirectResponse
    {
        // Respect the owner's wall setting — comments can be turned off.
        abort_unless($user->shows('allow_comments'), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        ProfileComment::create([
            'profile_user_id' => $user->id,
            'author_id'       => $request->user()->id,
            'body'            => $validated['body'],
        ]);

        // Let the wall owner know (not when commenting on your own wall).
        if ($user->id !== $request->user()->id) {
            $user->notify(new ProfileCommentNotification($request->user()));
        }

        return redirect()
            ->route('profiles.show', $user)
            ->with('status', 'Comment posted');
    }

    /**
     * Delete a wall comment — allowed for the comment's author, the
     * wall owner, or an admin.
     */
    public function destroyComment(Request $request, User $user, ProfileComment $comment): RedirectResponse
    {
        abort_unless($comment->profile_user_id == $user->id, 404);

        $actor = $request->user();
        $canDelete = $actor->id == $comment->author_id
            || $actor->id == $user->id
            || $actor->isAdmin();

        abort_unless($canDelete, 403);

        $comment->delete();

        return redirect()
            ->route('profiles.show', $user)
            ->with('status', 'Comment removed.');
    }
}
