@props(['class' => '-z-10'])

{{-- Slow drifting prismatic glow for dark hero banners.
     Pure decoration: aria-hidden, no pointer events.
     Sits above the banner image + tint layers via blend mode;
     stays below the section's content (content sits in a
     positive/auto z-index relative div). --}}
<div aria-hidden="true" class="prism-aurora {{ $class }}">
    <div class="prism-aurora-orb prism-aurora-orb-pink"></div>
    <div class="prism-aurora-orb prism-aurora-orb-violet"></div>
    <div class="prism-aurora-orb prism-aurora-orb-sky"></div>
    <div class="prism-aurora-orb prism-aurora-orb-mint"></div>
</div>
