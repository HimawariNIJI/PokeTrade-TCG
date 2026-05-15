import './bootstrap';

import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
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
