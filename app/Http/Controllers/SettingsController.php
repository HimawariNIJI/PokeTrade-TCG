<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Trainer profile Settings.
 *
 * This covers the *public* side of a trainer's identity — bio,
 * location, social links and the privacy toggles that drive what
 * the public profile (/u/{user}) renders. Name / email / password
 * still live in the classic ProfileController ("Account & password").
 */
class SettingsController extends Controller
{
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
     * Show the settings form.
     */
    public function edit(Request $request): View
    {
        return view('pages.settings.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Persist the trainer's public profile + privacy settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'bio'      => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:120'],
        ];

        foreach (self::SOCIAL_KEYS as $key) {
            $rules[$key] = ['nullable', 'url', 'max:255'];
        }

        foreach (self::VISIBILITY_KEYS as $key) {
            // Hidden field guarantees a 0 when the box is unchecked.
            $rules[$key] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);

        $user = $request->user();

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
        $user->save();

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Settings saved');
    }
}
