<?php

namespace App\Http\Controllers;

use App\Models\ProfileComment;
use App\Models\User;
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

        return view('pages.profiles.show', [
            'user'         => $user,
            'digitalCards' => $user->digitalCards,
            'chaseCards'   => $user->wishlistedCards,
            'comments'     => $user->profileComments,
            'digitalCount' => $digitalCount,
            'chaseCount'   => $chaseCount,
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

        return redirect()
            ->route('profiles.show', $user)
            ->with('status', 'Comment posted');
    }
}
