import './bootstrap';

import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    /**
     * shoutbox — persisted community chat in the forums sidebar. Seeds
     * from server-rendered messages, posts via fetch, and polls every
     * 10s for new messages (near-live without websockets).
     */
    Alpine.data('shoutbox', (initial, canPost, pollUrl, postUrl) => ({
        messages: initial || [],
        canPost,
        draft: '',
        sending: false,
        timer: null,
        init() {
            this.scrollToEnd();
            this.timer = setInterval(() => this.refresh(), 10000);
        },
        destroy() {
            if (this.timer) clearInterval(this.timer);
        },
        scrollToEnd() {
            this.$nextTick(() => {
                const el = this.$refs.stream;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
        async refresh() {
            try {
                const res = await fetch(pollUrl, { headers: { Accept: 'application/json' } });
                if (!res.ok) return;
                const json = await res.json();
                // server returns newest-first; display oldest-first
                this.messages = (json.messages || []).slice().reverse();
            } catch (e) { /* offline / transient — keep current view */ }
        },
        async send() {
            const text = this.draft.trim();
            if (!text || this.sending) return;
            this.sending = true;
            try {
                const res = await fetch(postUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ body: text }),
                });
                if (res.ok) {
                    const json = await res.json();
                    if (json.message) this.messages.push(json.message);
                    this.draft = '';
                    this.scrollToEnd();
                }
            } catch (e) { /* keep the draft so the user can retry */ }
            this.sending = false;
        },
    }));

    /**
     * auctionCountdown — ticks the "ends in" display on the auction page.
     *
     * TODO(backend): to make the leaderboard + live feed update in real time,
     * poll a bids endpoint inside tick() (or, preferred, subscribe to a
     * broadcast channel). The frontend deliberately leaves this as a stub.
     */
    Alpine.data('auctionCountdown', (endsAtIso) => ({
        endsAt: endsAtIso ? new Date(endsAtIso).getTime() : 0,
        display: '—',
        timer: null,
        init() {
            if (!this.endsAt) { this.display = '—'; return; }
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },
        tick() {
            const diff = this.endsAt - Date.now();
            if (diff <= 0) {
                this.display = 'Ended';
                if (this.timer) clearInterval(this.timer);
                return;
            }
            const h = Math.floor(diff / 3.6e6);
            const m = Math.floor((diff % 3.6e6) / 6e4);
            const s = Math.floor((diff % 6e4) / 1000);
            const pad = (n) => String(n).padStart(2, '0');
            this.display = `${pad(h)}:${pad(m)}:${pad(s)}`;
        },
    }));

    /**
     * cardPicker — searches the card catalogue and stores the chosen card.
     * `preselected` is null or { id, name, image_small, set_name }.
     */
    Alpine.data('cardPicker', (preselected) => ({
        picked: preselected,
        modal: false,
        q: '',
        results: [],
        loading: false,
        open() {
            this.modal = true;
            if (this.results.length === 0) this.search();
        },
        choose(card) {
            this.picked = card;
            this.modal = false;
        },
        async search() {
            this.loading = true;
            try {
                const url = new URL('/admin/auctions/cards/search', window.location.origin);
                url.searchParams.set('q', this.q);
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                const json = await res.json();
                this.results = json.data ?? [];
            } catch (e) {
                this.results = [];
            }
            this.loading = false;
        },
    }));
});

window.Alpine = Alpine;

Alpine.start();
