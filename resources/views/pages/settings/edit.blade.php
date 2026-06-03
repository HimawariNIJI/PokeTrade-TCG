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

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-12" enctype="multipart/form-data"
          x-data="{ submitting: false }"
          x-on:submit="submitting = true">
        @csrf
        @method('PATCH')

        {{-- ====================================================
             Profile images — avatar + banner upload, each with a
             live preview, an upload control, and a "remove" toggle.
             Alpine drives the preview swap; the actual upload is
             validated and stored by SettingsController.
             ==================================================== --}}
        <section class="rounded-3xl border border-ink-200 bg-white p-6 md:p-8">
            <x-section-heading eyebrow="Images" title="Avatar & banner"
                subtitle="Drop in a profile picture and a banner strip. JPG / PNG / WebP, up to 4 MB each." />

            {{-- ---------------- AVATAR ---------------- --}}
            <div class="mt-6"
                 x-data="{
                     preview: @js($user->avatar_url),
                     remove: false,
                     pick(e) {
                         const f = e.target.files?.[0];
                         if (!f) return;
                         this.remove = false;
                         this.preview = URL.createObjectURL(f);
                     },
                     clear() {
                         this.preview = null;
                         this.remove = true;
                         this.$refs.input.value = '';
                     },
                 }">
                <x-input-label value="Profile picture" />

                <div class="mt-3 flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                    <div class="relative h-24 w-24 shrink-0">
                        <template x-if="preview">
                            <img :src="preview" alt="" class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-md" />
                        </template>
                        <template x-if="!preview">
                            <span class="inline-flex h-24 w-24 items-center justify-center rounded-full prism-bg text-3xl font-black text-white shadow-md ring-4 ring-white">
                                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                            </span>
                        </template>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-semibold text-ink-700 hover:border-prism-violet hover:text-prism-violet">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/>
                                </svg>
                                <span x-text="preview ? 'Change picture' : 'Upload picture'"></span>
                                <input x-ref="input" type="file" name="avatar" accept="image/*" class="sr-only" x-on:change="pick">
                            </label>
                            <button type="button" x-show="preview" x-on:click="clear"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 hover:text-rose-600">
                                Remove
                            </button>
                        </div>
                        <p class="text-xs text-ink-500">Square images look best. We'll crop to a circle.</p>
                    </div>
                </div>

                <input type="hidden" name="remove_avatar" x-bind:value="remove ? 1 : 0">
                @error('avatar')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ---------------- BANNER ---------------- --}}
            <div class="mt-10 border-t border-ink-100 pt-8"
                 x-data="{
                     preview: @js($user->banner_url),
                     remove: false,
                     pick(e) {
                         const f = e.target.files?.[0];
                         if (!f) return;
                         this.remove = false;
                         this.preview = URL.createObjectURL(f);
                     },
                     clear() {
                         this.preview = null;
                         this.remove = true;
                         this.$refs.input.value = '';
                     },
                 }">
                <x-input-label value="Profile banner" />

                <div class="mt-3">
                    <div class="relative h-44 w-full overflow-hidden rounded-2xl border border-ink-200 bg-gradient-to-br from-prism-violet/20 via-prism-rose/10 to-prism-sky/20 md:h-56">
                        <template x-if="preview">
                            <img :src="preview" alt="" class="h-full w-full object-cover" />
                        </template>
                        <template x-if="!preview">
                            <div class="flex h-full w-full items-center justify-center text-sm font-semibold text-ink-500">
                                Banner preview
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-semibold text-ink-700 hover:border-prism-violet hover:text-prism-violet">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/>
                            </svg>
                            <span x-text="preview ? 'Change banner' : 'Upload banner'"></span>
                            <input x-ref="input" type="file" name="banner" accept="image/*" class="sr-only" x-on:change="pick">
                        </label>
                        <button type="button" x-show="preview" x-on:click="clear"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 hover:text-rose-600">
                            Remove
                        </button>
                        <p class="text-xs text-ink-500">Wide images work best — roughly 1500 × 500.</p>
                    </div>
                </div>

                <input type="hidden" name="remove_banner" x-bind:value="remove ? 1 : 0">
                @error('banner')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

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

        {{-- ====================================================
             Pinned showcase — pick up to N cards from the trainer's
             digital collection to highlight on their public profile.
             Alpine tracks the selected IDs and enforces the cap; the
             server re-validates ownership and the cap on submit.
             ==================================================== --}}
        <section class="rounded-3xl border border-ink-200 bg-white p-6 md:p-8"
                 x-data="{
                     pinned: @js(collect($user->pinned_cards ?? [])->map(fn($id) => (int) $id)->values()),
                     max: {{ (int) $maxPinned }},
                     toggle(id) {
                         id = parseInt(id, 10);
                         const idx = this.pinned.indexOf(id);
                         if (idx > -1) {
                             this.pinned.splice(idx, 1);
                         } else if (this.pinned.length < this.max) {
                             this.pinned.push(id);
                         }
                     },
                     has(id) { return this.pinned.includes(parseInt(id, 10)); },
                     clear() { this.pinned = []; },
                 }">
            <x-section-heading eyebrow="Showcase" title="Pinned cards"
                subtitle="Highlight up to {{ $maxPinned }} cards from your digital collection at the top of your profile." />

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-semibold text-ink-700">
                    <span x-text="pinned.length"></span> / {{ $maxPinned }} pinned
                </p>
                <button type="button" x-on:click="clear" x-show="pinned.length"
                        class="text-xs font-semibold uppercase tracking-widest text-ink-500 hover:text-rose-600">
                    Clear all
                </button>
            </div>

            @if($ownedCards->isEmpty())
                <div class="mt-6">
                    <x-empty-state
                        icon="✦"
                        title="No cards to pin yet"
                        message="Pull a digital pack from the gacha to start your collection. Once you've got cards, you can pin a few here to show off.">
                        <x-prism-button :href="route('gacha.index')" size="sm">Pull your first pack</x-prism-button>
                    </x-empty-state>
                </div>
            @else
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach($ownedCards as $card)
                        @php $cardImg = $card->image_small ?: $card->image_large; @endphp
                        <label
                            x-bind:class="has({{ $card->id }})
                                ? 'border-prism-violet ring-2 ring-prism-violet shadow-md'
                                : (pinned.length >= max ? 'border-ink-100 opacity-50' : 'border-ink-200 hover:border-prism-violet')"
                            class="group relative flex cursor-pointer flex-col gap-2 rounded-2xl border bg-white p-2 transition">
                            <input type="checkbox" name="pinned_cards[]" value="{{ $card->id }}"
                                   x-bind:checked="has({{ $card->id }})"
                                   x-on:change="toggle({{ $card->id }})"
                                   x-bind:disabled="!has({{ $card->id }}) && pinned.length >= max"
                                   class="sr-only">

                            {{-- Pinned indicator. --}}
                            <span class="absolute right-2 top-2 z-10 inline-flex h-6 w-6 items-center justify-center rounded-full text-white shadow"
                                  x-bind:class="has({{ $card->id }}) ? 'bg-prism-violet' : 'bg-white/0'">
                                <svg x-show="has({{ $card->id }})" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                            </span>

                            <div class="aspect-[3/4] overflow-hidden rounded-xl bg-ink-100">
                                @if($cardImg)
                                    <img src="{{ $cardImg }}" alt="{{ $card->name }}"
                                         class="h-full w-full object-cover" />
                                @endif
                            </div>
                            <span class="line-clamp-1 px-1 text-xs font-bold text-ink-900">{{ $card->name }}</span>
                        </label>
                    @endforeach
                </div>

                @error('pinned_cards')
                    <p class="mt-3 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
                @error('pinned_cards.*')
                    <p class="mt-3 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            @endif
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

            <x-prism-button type="submit" size="md"
                x-bind:disabled="submitting"
                x-bind:class="{ 'opacity-70 cursor-wait pointer-events-none': submitting }">
                <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                </svg>
                <span x-text="submitting ? 'Saving…' : 'Save settings'">Save settings</span>
            </x-prism-button>
        </div>
    </form>
</div>

</x-app-layout>
