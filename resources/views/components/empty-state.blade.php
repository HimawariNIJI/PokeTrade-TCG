@props(['title' => 'Nothing here yet', 'message' => null, 'icon' => '◇'])

<div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-ink-200 bg-white px-8 py-16 text-center">
    <div class="relative mb-5 inline-flex h-16 w-16 items-center justify-center">
        <span class="absolute inset-0 rounded-2xl prism-bg opacity-30 blur"></span>
        <span class="relative inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl">
            {{ $icon }}
        </span>
    </div>
    <h3 class="font-display text-xl font-bold text-ink-900">{{ $title }}</h3>
    @if($message)
        <p class="mt-2 max-w-md text-sm text-ink-500">{{ $message }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
