/**
 * Scroll reveal + parallax engine.
 *
 * Markup contract:
 *   [data-reveal="VARIANT"]          one-shot reveal when scrolled into view
 *   [data-reveal-i="N"] OR style="--reveal-i:N"   stagger index
 *   [data-reveal-delay="ms"]         extra static delay
 *   [data-parallax="0.15"]           rAF parallax, value is speed
 *   [data-parallax-axis="x|y"]       axis (default y)
 *
 * Variants live in app.css under "Scroll-triggered reveal system".
 * "letters" variant: child text gets wrapped in <span class="rl" style="--i:N">.
 */

const REDUCED = matchMedia('(prefers-reduced-motion: reduce)').matches;

function splitLetters(el) {
    let i = 0;
    const walk = (node) => {
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent;
            if (!text.trim()) return;
            const frag = document.createDocumentFragment();
            // split on whitespace runs but keep them so spacing is preserved
            const tokens = text.split(/(\s+)/);
            for (const token of tokens) {
                if (!token) continue;
                if (/^\s+$/.test(token)) {
                    frag.appendChild(document.createTextNode(' '));
                    continue;
                }
                // wrap each word in an inline-block w/ nowrap so the browser
                // never breaks a line *inside* a word's letter spans.
                const word = document.createElement('span');
                word.className = 'rw';
                for (const ch of token) {
                    const span = document.createElement('span');
                    span.className = 'rl';
                    span.style.setProperty('--i', i++);
                    span.textContent = ch;
                    word.appendChild(span);
                }
                frag.appendChild(word);
            }
            node.parentNode.replaceChild(frag, node);
            return;
        }
        if (node.nodeType !== Node.ELEMENT_NODE) return;
        // skip elements we don't want to dice into letters
        if (node.matches('br, svg, img, .prism-text, [data-letter-skip]')) return;
        // Italic / bold inline elements — treat their text as one atomic
        // .rl so the browser keeps the natural italic kerning + slant overhang
        // intact. Splitting italic glyphs into separate inline-blocks clips
        // the right-leaning portion of each letter against the next box.
        if (node.matches('em, i, strong, b')) {
            const text = node.textContent;
            if (!text.trim()) return;
            node.textContent = '';
            const word = document.createElement('span');
            word.className = 'rw';
            const atom = document.createElement('span');
            atom.className = 'rl';
            atom.style.setProperty('--i', i++);
            atom.textContent = text;
            word.appendChild(atom);
            node.appendChild(word);
            return;
        }
        // iterate over a static copy because we mutate during walk
        for (const child of [...node.childNodes]) walk(child);
    };
    for (const child of [...el.childNodes]) walk(child);
}

function setupReveal() {
    const letterTargets = document.querySelectorAll('[data-reveal="letters"]');
    letterTargets.forEach(splitLetters);

    const revealEls = document.querySelectorAll('[data-reveal]');

    // honor stagger index from attr if --reveal-i isn't already inline
    revealEls.forEach((el) => {
        const idx = el.dataset.revealI;
        if (idx && !el.style.getPropertyValue('--reveal-i')) {
            el.style.setProperty('--reveal-i', idx);
        }
        const delay = el.dataset.revealDelay;
        if (delay) el.style.setProperty('--reveal-delay', `${delay}ms`);
    });

    if (REDUCED) {
        revealEls.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const io = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        }
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    revealEls.forEach((el) => io.observe(el));
}

function setupParallax() {
    if (REDUCED) return;
    const targets = [...document.querySelectorAll('[data-parallax]')].map((el) => ({
        el,
        speed: parseFloat(el.dataset.parallax) || 0.1,
        axis: el.dataset.parallaxAxis || 'y',
    }));
    if (!targets.length) return;

    let pending = false;
    const update = () => {
        const center = window.innerHeight / 2;
        for (const t of targets) {
            const r = t.el.getBoundingClientRect();
            const elCenter = r.top + r.height / 2;
            const offset = -(elCenter - center) * t.speed;
            const prop = t.axis === 'x' ? '--parallax-x' : '--parallax-y';
            t.el.style.setProperty(prop, `${offset.toFixed(2)}px`);
        }
        pending = false;
    };
    const onScroll = () => {
        if (pending) return;
        pending = true;
        requestAnimationFrame(update);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
}

function init() {
    setupReveal();
    setupParallax();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
