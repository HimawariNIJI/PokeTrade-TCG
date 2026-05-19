<x-app-layout>

@php
    // Pre-fill helpers — social links are stored as an array cast.
    $links = $user->social_links ?? [];

    // The privacy toggles, merged over defaults. friendly copy lives
    // alongside each key so the form reads like prose.
    $settings = $user->settings();

    $socialFields = [
        'twitter'   => ['label' => 'Twitter / X', 'placeholder' => 'https://twitter.com/yourhandle'],
        'instagram' => ['label' => 'Instagram',   'placeholder' => 'https://instagram.com/yourhandle'],
        'tiktok'    => ['label' => 'TikTok',      'placeholder' => 'https://tiktok.com/@yourhandle'],
        'youtube'   => ['label' => 'YouTube',     'placeholder' => 'https://youtube.com/@yourchannel'],
        'discord'   => ['label' => 'Discord',     'placeholder' => 'https://discord.gg/yourinvite'],
        'website'   => ['label' => 'Website',     'placeholder' => 'https://yoursite.com'],
    ];

    $visibilityFields = [
        'show_collection' => ['label' => 'Show my digital collection', 'desc' => 'Display the cards you\'ve pulled from packs on your public profile.'],
        'show_chase'      => ['label' => 'Show my chase cards',        'desc' => 'Let other trainers see the cards you\'re hunting for.'],
        'show_socials'    => ['label' => 'Show my social links',       'desc' => 'Display the links below on your public profile.'],
        'show_bio'        => ['label' => 'Show my bio',                'desc' => 'Display your bio text on your public profile.'],
        'allow_comments'  => ['label' => 'Allow comments on my wall',  'desc' => 'Let signed-in trainers leave comments on your profile.'],
    ];
@endphp

{{-- ============================================================
     Header.
     ============================================================ --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>

    <div class="mx-auto max-w-[1400px] px-4 py-16 md:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
            <span class="h-2 w-2 rounded-full bg-prism-gold"></span>
            Settings
        </span>
        <h1 class="mt-4 font-display text-4xl font-black leading-tight text-white md:text-5xl">
            Tune your <span class="prism-text">trainer profile</span>.
        </h1>
        <p class="mt-4 max-w-lg text-white/70">
            Control your bio, links and exactly what other trainers can see.
        </p>
        <a href="{{ route('profiles.show', $user) }}"
           class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-white/80 hover:text-white">
            View my public profile
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
            </svg>
        </a>
    </div>
</section>

<div class="mx-auto max-w-[860px] px-4 py-16 md:px-8">

    {{-- Flash confirmation after a save. --}}
    @if(session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
             class="mb-8 rounded-2xl border border-prism-mint/40 bg-prism-mint/10 px-4 py-3 text-sm font-semibold text-ink-900">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-12">
        @csrf
        @method('PATCH')

        {{-- ====================================================
             Public profile — bio + location.
             ==================================================== --}}
        <section class="rounded-3xl border border-ink-200 bg-white p-6 md:p-8">
            <x-section-heading eyebrow="Identity" title="Public profile" />

            <div class="mt-6 space-y-6">
                <div>
                    <x-input-label for="bio" value="Bio" />
                    <textarea id="bio" name="bio" rows="4" maxlength="1000"
                              placeholder="Tell other trainers about yourself…"
                              class="mt-1 block w-full resize-none rounded-xl border-ink-200 text-sm shadow-sm focus:border-prism-violet focus:ring-prism-violet">{{ old('bio', $user->bio) }}</textarea>
                    <p class="mt-1 text-xs text-ink-500">Up to 1000 characters.</p>
                    @error('bio')
                        <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="location" value="Location" />
                    <x-text-input id="location" name="location" type="text"
                                  class="mt-1 block w-full"
                                  placeholder="e.g. Jakarta, Indonesia"
                                  :value="old('location', $user->location)" />
                    @error('location')
                        <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ====================================================
             Social links — one input per platform.
             ==================================================== --}}
        <section class="rounded-3xl border border-ink-200 bg-white p-6 md:p-8">
            <x-section-heading eyebrow="Elsewhere" title="Social links"
                subtitle="Add full URLs. These appear on your profile when 'Show my social links' is on." />

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                @foreach($socialFields as $key => $meta)
                    <div>
                        <x-input-label :for="$key" :value="$meta['label']" />
                        <x-text-input :id="$key" :name="$key" type="url"
                                      class="mt-1 block w-full"
                                      :placeholder="$meta['placeholder']"
                                      :value="old($key, $links[$key] ?? '')" />
                        @error($key)
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ====================================================
             Privacy & visibility — the 5 toggles.
             Each checkbox is preceded by a hidden 0 so an
             unchecked box still submits a falsy value.
             ==================================================== --}}
        <section class="rounded-3xl border border-ink-200 bg-white p-6 md:p-8">
            <x-section-heading eyebrow="Privacy" title="Privacy & visibility"
                subtitle="Decide what other trainers can see on your public profile." />

            <div class="mt-6 space-y-3">
                @foreach($visibilityFields as $key => $meta)
                    @php $checked = (bool) old($key, $settings[$key] ?? true); @endphp
                    <label for="{{ $key }}"
                           class="flex cursor-pointer items-start gap-4 rounded-2xl border border-ink-200 p-4 transition hover:border-prism-violet">
                        {{-- Hidden 0 — submitted when the box is left unchecked. --}}
                        <input type="hidden" name="{{ $key }}" value="0">
                        <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1"
                               @checked($checked)
                               class="mt-0.5 h-5 w-5 rounded border-ink-300 text-prism-violet focus:ring-prism-violet">
                        <span class="min-w-0">
                            <span class="block font-display text-sm font-bold text-ink-900">{{ $meta['label'] }}</span>
                            <span class="mt-0.5 block text-xs text-ink-500">{{ $meta['desc'] }}</span>
                        </span>
                    </label>
                    @error($key)
                        <p class="text-sm font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>
        </section>

        {{-- Save + account link. --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700 hover:text-prism-violet">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                Account &amp; password
            </a>

            <x-prism-button type="submit" size="md">
                Save settings
            </x-prism-button>
        </div>
    </form>
</div>

</x-app-layout>
