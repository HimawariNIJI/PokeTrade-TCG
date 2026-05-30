<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Trainer profile Settings.
 *
 * This covers the *public* side of a trainer's identity — avatar,
 * banner, bio, location, social links, the privacy toggles that
 * drive what the public profile (/u/{user}) renders, and the
 * "pinned" cards the trainer wants to show off from their digital
 * collection. Name / email / password still live in the classic
 * ProfileController ("Account & password").
 */
class SettingsController extends Controller
{
    /** Maximum number of cards a trainer can pin to their profile. */
    public const MAX_PINNED = 6;

    /** Avatar / banner upload size cap (KB). */
    private const MAX_UPLOAD_KB = 4096;

    /** The visibility toggles a trainer controls. */
    private const VISIBILITY_KEYS = [
        'show_collection',
        'show_chase',
        'show_socials',
        'show_bio',
        'allow_comments',
    ];

    /** The social platforms we accept a link for. */
    private const SOCIAL_KEYS = [
        'twitter',
        'instagram',
        'tiktok',
        'youtube',
        'discord',
        'website',
    ];

    /**
     * Show the settings form. Eager-loads the trainer's collection
     * cards so the "pin to profile" grid has something to render.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Distinct digital cards owned, ordered alphabetically for the
        // pinned-cards picker. Duplicates from the gacha pivot are
        // collapsed via DISTINCT on card_id.
        $ownedCards = $user->digitalCards()
            ->select('cards.*')
            ->distinct()
            ->orderBy('cards.name')
            ->get()
            ->unique('id')
            ->values();

        return view('pages.settings.edit', [
            'user'       => $user,
            'ownedCards' => $ownedCards,
            'maxPinned'  => self::MAX_PINNED,
        ]);
    }

    /**
     * Persist the trainer's public profile + privacy settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Card IDs eligible to pin = whatever the trainer actually
        // owns in their digital collection. We pass this to the
        // validator so users can't pin cards they haven't pulled.
        $ownedCardIds = $user->collectionCards()
            ->pluck('card_id')
            ->unique()
            ->values()
            ->all();

        $rules = [
            'bio'           => ['nullable', 'string', 'max:1000'],
            'location'      => ['nullable', 'string', 'max:120'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . self::MAX_UPLOAD_KB],
            'banner'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_UPLOAD_KB],
            'remove_avatar' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
            'pinned_cards'  => ['nullable', 'array', 'max:' . self::MAX_PINNED],
            'pinned_cards.*' => ['integer', Rule::in($ownedCardIds)],
        ];

        foreach (self::SOCIAL_KEYS as $key) {
            $rules[$key] = ['nullable', 'url', 'max:255'];
        }

        foreach (self::VISIBILITY_KEYS as $key) {
            $rules[$key] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);

        // Assemble the social_links array from the individual inputs.
        $socialLinks = [];
        foreach (self::SOCIAL_KEYS as $key) {
            $socialLinks[$key] = $validated[$key] ?? null;
        }

        // Assemble the 5 visibility booleans — unchecked box = false.
        $profileSettings = [];
        foreach (self::VISIBILITY_KEYS as $key) {
            $profileSettings[$key] = $request->boolean($key);
        }

        $user->bio              = $validated['bio'] ?? null;
        $user->location         = $validated['location'] ?? null;
        $user->social_links     = $socialLinks;
        $user->profile_settings = $profileSettings;

        // Pinned cards — store as plain integers, capped + de-duped.
        // Empty selection wipes the array (returns to the default
        // "show latest pulls" behaviour on the profile).
        $pinned = collect($validated['pinned_cards'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->take(self::MAX_PINNED)
            ->values()
            ->all();
        $user->pinned_cards = $pinned;

        // --- Avatar -----------------------------------------------------
        if ($request->boolean('remove_avatar')) {
            $this->deleteStoredImage($user->avatar);
            $user->avatar = null;
        }
        if ($request->hasFile('avatar')) {
            $this->deleteStoredImage($user->avatar);
            $user->avatar = $request->file('avatar')->store("avatars/{$user->id}", 'public');
        }

        // --- Banner -----------------------------------------------------
        if ($request->boolean('remove_banner')) {
            $this->deleteStoredImage($user->banner);
            $user->banner = null;
        }
        if ($request->hasFile('banner')) {
            $this->deleteStoredImage($user->banner);
            $user->banner = $request->file('banner')->store("banners/{$user->id}", 'public');
        }

        $user->save();

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Settings saved');
    }

    /**
     * Toggle whether a card is pinned to the trainer's profile.
     * Called from the collection page's per-card pin button.
     */
    public function togglePin(Request $request, int $card): RedirectResponse
    {
        $user = $request->user();

        // Only cards the trainer owns may be pinned.
        $owns = $user->collectionCards()->where('card_id', $card)->exists();
        if (! $owns) {
            return back()->with('status', 'You can only pin cards from your own collection.');
        }

        $current = collect($user->pinned_cards ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($current->contains($card)) {
            $next = $current->reject(fn ($id) => $id === $card)->values();
            $flash = 'Card unpinned from your profile.';
        } else {
            if ($current->count() >= self::MAX_PINNED) {
                return back()->with('status', "You can pin at most " . self::MAX_PINNED . " cards. Unpin one first.");
            }
            $next = $current->push($card)->values();
            $flash = 'Card pinned to your profile.';
        }

        $user->pinned_cards = $next->all();
        $user->save();

        return back()->with('status', $flash);
    }

    /**
     * Best-effort cleanup of a previously-stored avatar/banner. Skips
     * remote URLs (Google OAuth avatars) and missing files.
     */
    private function deleteStoredImage(?string $path): void
    {
        if (! $path) {
            return;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }
        Storage::disk('public')->delete($path);
    }
}
