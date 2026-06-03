@if (session('status'))
    <div x-data="{ open: true }"
         x-show="open"
         x-init="setTimeout(() => open = false, 5000)"
         x-transition.duration.300ms
         class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 w-[90%] sm:w-auto sm:max-w-md">
        <div class="flex items-start sm:items-center gap-3 rounded-xl sm:rounded-full border border-ink-200 bg-white px-4 py-3 shadow-2xl">
            <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full prism-bg text-sm font-bold text-white">
                ✓
            </span>
            <span class="text-sm font-medium text-ink-900 break-words whitespace-normal leading-snug">
                {{ session('status') }}
            </span>
            <button @click="open = false" class="ml-auto sm:ml-2 text-ink-300 hover:text-ink-700">
                &times;
            </button>
        </div>
    </div>
@endif