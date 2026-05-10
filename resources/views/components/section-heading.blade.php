@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'left'])

<div class="{{ $align === 'center' ? 'text-center mx-auto max-w-2xl' : '' }}">
    @if($eyebrow)
        <div class="mb-3 inline-flex items-center gap-2">
            <span class="h-px w-8 prism-bg"></span>
            <span class="text-xs font-bold uppercase tracking-[0.25em] text-ink-700">{{ $eyebrow }}</span>
            <span class="h-px w-8 prism-bg"></span>
        </div>
    @endif
    <h2 class="font-display text-3xl font-black tracking-tight text-ink-900 md:text-4xl">
        {!! $title !!}
    </h2>
    @if($subtitle)
        <p class="mt-3 text-sm text-ink-500 md:text-base">{{ $subtitle }}</p>
    @endif
</div>
