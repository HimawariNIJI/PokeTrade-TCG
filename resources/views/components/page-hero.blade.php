@props([
    'eyebrow' => null,
    'title',                 // HTML allowed (use a <span class="prism-text"> accent)
    'subtitle' => null,      // HTML allowed
    'compact' => false,      // shorter hero for listing pages
    'mon' => null,           // optional Eevee-line artwork slug, e.g. "sylveon"
])

{{-- Shared white-dominant prism hero: soft brand-colour washes, an
     optional real Eevee-evolution image floating on the right, and a
     staggered entrance. Drop `<x-slot:actions>` for CTAs/stat chips. --}}
<section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-white']) }}>
    <div class="pointer-events-none absolute -left-32 -top-16 -z-10 h-[28rem] w-[28rem] rounded-full bg-prism-pink/12 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-10 -top-10 -z-10 h-[26rem] w-[26rem] rounded-full bg-prism-sky/12 blur-3xl"></div>

    @if($mon)
        <img src="{{ asset('images/eevee/' . $mon . '.png') }}" alt="" aria-hidden="true"
             class="pointer-events-none absolute right-[2%] top-1/2 z-0 hidden w-40 -translate-y-1/2 animate-float drop-shadow-xl md:block lg:w-56" />
    @endif

    <div class="relative z-10 mx-auto max-w-[1400px] px-4 {{ $compact ? 'py-12 md:py-16' : 'py-16 md:py-20' }} md:px-8">
        <div class="max-w-3xl">
            @if($eyebrow)
                <div class="enter inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/80 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 shadow-sm backdrop-blur" style="--d:0">
                    <span class="h-2 w-2 rounded-full bg-prism-mint"></span>
                    {{ $eyebrow }}
                </div>
            @endif

            <h1 class="enter mt-4 font-display font-bold tracking-tight text-ink-900 {{ $compact ? 'text-5xl md:text-6xl' : 'text-6xl leading-[0.95] md:text-7xl' }}" style="--d:80">
                {!! $title !!}
            </h1>

            @if($subtitle)
                <p class="enter mt-4 max-w-2xl text-lg leading-relaxed text-ink-700" style="--d:160">{!! $subtitle !!}</p>
            @endif

            @isset($actions)
                <div class="enter mt-6 flex flex-wrap items-center gap-3" style="--d:240">{{ $actions }}</div>
            @endisset
        </div>

        {{ $slot }}
    </div>
</section>
