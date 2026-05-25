<x-app-layout>

<section class="mx-auto max-w-[1000px] px-4 py-16 md:px-8">
    <div class="mb-10">
        <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
            Inbox
        </span>
        <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
            Your <span class="prism-text">notifications</span>.
        </h1>
    </div>

    @if($notifications->isEmpty())
        <x-empty-state
            icon="◇"
            title="Nothing here yet"
            message="When a card on your wishlist hits the auction block, you'll see it here." />
    @else
        <div class="space-y-3">
            @foreach($notifications as $note)
                @php
                    $data = $note->data;
                    $isUnread = $note->read_at === null;
                @endphp
                <div class="flex items-center gap-4 rounded-2xl border border-ink-200 bg-white p-4 transition hover:border-prism-violet
                            {{ $isUnread ? 'bg-prism-violet/5' : '' }}">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full prism-bg text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-ink-900">{{ $data['message'] ?? 'New activity' }}</p>
                        <p class="text-xs text-ink-500 mt-0.5">{{ $note->created_at->diffForHumans() }}</p>
                    </div>
                    @if(isset($data['auction_id']))
                        <a href="{{ route('auctions.show', $data['auction_id']) }}"
                           class="rounded-full bg-ink-900 px-4 py-1.5 text-xs font-bold text-white hover:bg-ink-700">
                            View auction
                        </a>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $note->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-bold text-ink-400 hover:text-rose-600">
                            Dismiss
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $notifications->links() }}</div>
    @endif
</section>

</x-app-layout>
