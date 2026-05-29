@props([
    'type',          // thread | post | comment
    'id',
    'label' => 'Report',
])

@auth
    <div x-data="{ open: false, submitting: false }" class="inline-block">
        <button type="button" @click="open = true"
                class="inline-flex items-center gap-1 text-xs font-semibold text-ink-400 transition hover:text-rose-600">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 22V4a1 1 0 0 1 1-1h11l-2 4 2 4H5"/>
            </svg>
            {{ $label }}
        </button>

        <div x-show="open" x-cloak
             @keydown.escape.window="open = false"
             class="fixed inset-0 z-[90] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div @click.outside="open = false"
                 class="w-full max-w-md rounded-3xl border border-ink-200 bg-white p-6 shadow-2xl">
                <h3 class="font-display text-lg font-bold text-ink-900">Report this {{ $type }}</h3>
                <p class="mt-1 text-sm text-ink-500">Tell us what's wrong. A moderator will review it.</p>

                <form method="POST" action="{{ route('reports.store') }}" class="mt-4 space-y-3" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="id" value="{{ $id }}">

                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Reason</span>
                        <select name="reason" required aria-label="Report reason"
                                class="mt-1.5 block w-full rounded-xl border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
                            <option value="spam">Spam or advertising</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="inappropriate">Inappropriate content</option>
                            <option value="scam">Scam or fraud</option>
                            <option value="off_topic">Off topic</option>
                            <option value="other">Something else</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Details <span class="font-normal normal-case text-ink-400">(optional)</span></span>
                        <textarea name="details" rows="3" maxlength="1000"
                                  placeholder="Add anything that helps us understand…"
                                  class="mt-1.5 block w-full resize-none rounded-xl border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet"></textarea>
                    </label>

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="open = false"
                                class="rounded-full border border-ink-200 px-4 py-2 text-sm font-bold text-ink-700 transition hover:bg-ink-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting"
                                class="rounded-full bg-rose-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-700 disabled:opacity-60">
                            Submit report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth
