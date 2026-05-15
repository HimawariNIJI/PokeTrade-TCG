# Admin Auction Bidding Feature Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an admin UI to create and manage card auctions, and re-skin the public auction detail page into a flashy "Neon Arena", as a frontend-only deliverable on top of the existing auction system.

**Architecture:** Server-rendered Laravel 11 + Blade + Alpine.js 3. We add nine routes under the existing `admin/` group pointing at a new **stub** `Admin\AuctionController` that returns hardcoded in-memory `Auction`/`Bid`/`Card` model objects (so views are clickable without a database). We add three admin views, one reusable card-picker component, two registered Alpine components, neon CSS utilities, and we replace the public `pages/auctions/show.blade.php` view. Backend wiring (persistence, validation, the `highlight_mode` migration, real-time) is explicitly out of scope and marked with `// TODO(backend)`.

**Tech Stack:** Laravel 11, Blade, Alpine.js 3.4, Tailwind CSS 4 (Prismatic theme), Pest (tests).

**Spec:** `docs/superpowers/specs/2026-05-16-admin-auction-bidding-design.md`

---

## Conventions & Deviations from Spec

These refinements were decided while mapping the codebase. They do not change scope:

1. **Explicit routes, not `Route::resource`.** The spec §5.1 illustrated `Route::resource`. We use explicit route declarations instead so controller method names/params are obvious. Crucially, the controller stub methods take an **untyped** `$auction` param (e.g. `edit($auction)`, not `edit(Auction $auction)`). Laravel only performs implicit route-model binding when the param is type-hinted with an Eloquent model — leaving it untyped means `/admin/auctions/1/edit` resolves with no DB record present. Backend dev adds the `Auction $auction` type-hint when wiring real data.
2. **Per-bid highlight confirmation** uses a single Alpine-driven confirmation modal (one modal, action bound dynamically) rather than N `<x-modal>` instances — honoring the spec's "confirmation modal" requirement without modal-per-row bloat.
3. **No `_form` partial.** The shop module shares a `_form.blade.php` between create/edit. Auction create and edit differ enough (card picker vs. read-only card, plus the bid panel) that each view is self-contained — matching the spec's unit list (`create.blade.php`, `edit.blade.php` as separate units). The minor duplication of four config inputs keeps each file independently readable.

## File Structure

| File | Create/Modify | Responsibility |
|---|---|---|
| `routes/web.php` | Modify | Register nine `admin.auctions.*` routes inside the existing `auth`+`admin` group. |
| `app/Http/Controllers/Admin/AuctionController.php` | Create | Stub controller: returns in-memory sample data; `// TODO(backend)` handoff block. |
| `resources/css/app.css` | Modify | Add `[x-cloak]` rule + Neon Arena keyframes/utilities. |
| `resources/js/app.js` | Modify | Register `auctionCountdown` and `cardPicker` Alpine components. |
| `resources/views/components/card-picker.blade.php` | Create | Reusable card-search modal; writes `card_id` into the host form. |
| `resources/views/admin/auctions/index.blade.php` | Create | Auction list table. |
| `resources/views/admin/auctions/create.blade.php` | Create | New-auction form + publish-confirmation modal. |
| `resources/views/admin/auctions/edit.blade.php` | Create | Edit form + bid-management (highlight) panel. |
| `resources/views/pages/auctions/show.blade.php` | Modify (replace) | Public auction detail — Neon Arena re-skin. |
| `tests/Feature/AdminAuctionTest.php` | Create | Feature test: all routes render; search returns JSON; admin gate. |

---

## Chunk 1: Plumbing, assets, card-picker component

### Task 1: Routes + stub controller

**Files:**
- Modify: `routes/web.php` (admin group, ~line 96)
- Create: `app/Http/Controllers/Admin/AuctionController.php`

- [ ] **Step 1: Add the routes**

In `routes/web.php`, inside the existing `Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(...)` closure, after the `shop` resource line, add:

```php
        // Auctions — admin bidding console.
        // Custom/literal routes registered BEFORE the {auction} routes so the
        // literal `create` and `cards/search` segments are not shadowed.
        Route::get('auctions', [Admin\AuctionController::class, 'index'])->name('auctions.index');
        Route::get('auctions/create', [Admin\AuctionController::class, 'create'])->name('auctions.create');
        Route::post('auctions', [Admin\AuctionController::class, 'store'])->name('auctions.store');
        Route::get('auctions/cards/search', [Admin\AuctionController::class, 'cardSearch'])->name('auctions.cards.search');
        Route::get('auctions/{auction}/edit', [Admin\AuctionController::class, 'edit'])->name('auctions.edit');
        Route::put('auctions/{auction}', [Admin\AuctionController::class, 'update'])->name('auctions.update');
        Route::delete('auctions/{auction}', [Admin\AuctionController::class, 'destroy'])->name('auctions.destroy');
        Route::post('auctions/{auction}/highlight', [Admin\AuctionController::class, 'highlight'])->name('auctions.highlight');
        Route::post('auctions/{auction}/highlight/reset', [Admin\AuctionController::class, 'resetHighlight'])->name('auctions.highlight.reset');
```

The `use App\Http\Controllers\Admin;` import already exists at the top of the file — no new import needed.

- [ ] **Step 2: Create the stub controller**

Create `app/Http/Controllers/Admin/AuctionController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Admin auction management — FRONTEND STUB.
 *
 * Every method returns hardcoded in-memory sample data so the Blade views
 * are fully clickable without a database. Method params are intentionally
 * NOT type-hinted as `Auction`, so route-model binding does not run while
 * there are no DB records.
 *
 * TODO(backend): replace each method body:
 *   index()          -> Auction::with('card','currentLeader')->latest()->paginate()
 *   create()         -> unchanged (renders an empty form)
 *   store()          -> validate (see rules below), create Auction, set status
 *   edit()           -> type-hint `Auction $auction`; load bids.user
 *   update()         -> validate + persist edits
 *   destroy()        -> delete or cancel the auction
 *   cardSearch()     -> Card::where('name','like',"%{$q}%")->limit(24)->get(...)
 *   highlight()      -> set current_leader_id = chosen bid's user; highlight_mode='manual'
 *   resetHighlight() -> highlight_mode='auto'; current_leader_id = highest bid's user
 *
 * TODO(backend): add a migration adding `highlight_mode` (string, default
 * 'auto') to the `auctions` table. The admin views read $auction->highlight_mode.
 *
 * TODO(backend): suggested validation rules for store()/update():
 *   'card_id'       => 'required|exists:cards,id'
 *   'starting_bid'  => 'required|numeric|min:0'
 *   'bid_increment' => 'required|numeric|min:1'
 *   'buy_now_price' => 'nullable|numeric|gt:starting_bid'
 *   'starts_at'     => 'required|date'
 *   'ends_at'       => 'required|date|after:starts_at'
 */
class AuctionController extends Controller
{
    public function index()
    {
        return view('admin.auctions.index', ['auctions' => $this->sampleAuctions()]);
    }

    public function create()
    {
        return view('admin.auctions.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction published (stub) — backend wiring pending.');
    }

    public function edit($auction)
    {
        return view('admin.auctions.edit', ['auction' => $this->sampleAuction((int) $auction)]);
    }

    public function update(Request $request, $auction)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction updated (stub) — backend wiring pending.');
    }

    public function destroy($auction)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction removed (stub) — backend wiring pending.');
    }

    public function cardSearch(Request $request)
    {
        $q = strtolower(trim((string) $request->query('q', '')));
        $cards = $this->sampleCards();

        if ($q !== '') {
            $cards = array_values(array_filter(
                $cards,
                fn ($c) => str_contains(strtolower($c['name']), $q)
            ));
        }

        return response()->json(['data' => array_slice($cards, 0, 24)]);
    }

    public function highlight(Request $request, $auction)
    {
        return back()->with('status', 'Highlighted bidder updated (stub) — backend wiring pending.');
    }

    public function resetHighlight(Request $request, $auction)
    {
        return back()->with('status', 'Highlight reset to auto (stub) — backend wiring pending.');
    }

    // ================================================================
    // Sample data — TODO(backend): delete this entire section.
    // ================================================================

    private function sampleAuctions(): Collection
    {
        return collect([1, 2, 3])->map(fn ($id) => $this->sampleAuction($id));
    }

    private function sampleAuction(int $id): Auction
    {
        $statuses = [1 => 'live', 2 => 'scheduled', 3 => 'ended'];
        $names    = [1 => 'Charizard ex', 2 => 'Pikachu VMAX', 3 => 'Mewtwo GX'];

        $card = new Card([
            'name'        => $names[$id] ?? 'Eevee ex',
            'set_name'    => 'Obsidian Flames',
            'rarity'      => 'Illustration Rare',
            'image_small' => 'https://images.pokemontcg.io/sv3/6.png',
            'image_large' => 'https://images.pokemontcg.io/sv3/6_hires.png',
        ]);
        $card->id = 100 + $id;

        $auction = new Auction([
            'card_id'       => $card->id,
            'starting_bid'  => 500000,
            'current_bid'   => 4250000,
            'bid_increment' => 50000,
            'buy_now_price' => 9000000,
            'starts_at'     => Carbon::now()->subHours(3),
            'ends_at'       => Carbon::now()->addHours(2)->addMinutes(14),
            'status'        => $statuses[$id] ?? 'live',
        ]);
        $auction->id                = $id;
        $auction->current_leader_id = 901;
        // highlight_mode is not a real column yet (see TODO above); setting it
        // directly on the in-memory model makes it readable in the views.
        $auction->highlight_mode = $id === 1 ? 'manual' : 'auto';
        $auction->setRelation('card', $card);

        $leader = (new User(['name' => 'ashketchum_id']));
        $leader->id = 901;
        $auction->setRelation('currentLeader', $leader);

        $bidders = [901 => 'ashketchum_id', 902 => 'misty_water', 903 => 'brock_rock', 904 => 'gary_oak'];
        $amounts = [4250000, 4100000, 3900000, 3500000];

        $bids = collect();
        $i = 0;
        foreach ($bidders as $uid => $name) {
            $user = new User(['name' => $name]);
            $user->id = $uid;

            $bid = new Bid(['auction_id' => $id, 'user_id' => $uid, 'amount' => $amounts[$i]]);
            $bid->id = $id * 10 + $i;
            $bid->created_at = Carbon::now()->subMinutes(($i + 1) * 7);
            $bid->setRelation('user', $user);

            $bids->push($bid);
            $i++;
        }
        $auction->setRelation('bids', $bids);

        return $auction;
    }

    private function sampleCards(): array
    {
        $sets = ['Obsidian Flames', 'Paldea Evolved', '151', 'Paradox Rift'];
        $rarities = ['Illustration Rare', 'Special Illustration Rare', 'Ultra Rare', 'Double Rare'];
        $names = [
            'Charizard ex', 'Pikachu VMAX', 'Mewtwo GX', 'Eevee ex', 'Gardevoir ex',
            'Gengar VMAX', 'Lugia V', 'Rayquaza VMAX', 'Umbreon ex', 'Snorlax V',
            'Greninja ex', 'Tyranitar ex', 'Sylveon VMAX', 'Lucario VSTAR', 'Arceus V',
        ];

        $cards = [];
        foreach ($names as $idx => $name) {
            $cards[] = [
                'id'          => 101 + $idx,
                'name'        => $name,
                'number'      => str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                'set_name'    => $sets[$idx % count($sets)],
                'rarity'      => $rarities[$idx % count($rarities)],
                'image_small' => 'https://images.pokemontcg.io/sv3/' . (($idx % 9) + 1) . '.png',
            ];
        }

        return $cards;
    }
}
```

- [ ] **Step 3: Verify routes register without error**

Run: `php artisan route:list --name=admin.auctions`
Expected: nine rows listed (`admin.auctions.index`, `.create`, `.store`, `.cards.search`, `.edit`, `.update`, `.destroy`, `.highlight`, `.highlight.reset`).

- [ ] **Step 4: Commit**

```bash
git add routes/web.php app/Http/Controllers/Admin/AuctionController.php
git commit -m "feat(admin): add auction routes and stub controller"
```

---

### Task 2: Neon Arena CSS

**Files:**
- Modify: `resources/css/app.css` (append after the existing `@utility animate-pulse-glow` block, before the "Brand utilities" section)

- [ ] **Step 1: Add the `x-cloak` rule and Neon Arena utilities**

Append this block to `resources/css/app.css` immediately after the `@utility animate-pulse-glow { ... }` block:

```css
/* Hide Alpine elements until the component initialises. */
[x-cloak] { display: none !important; }

/* ============================================================
   Auction — Neon Arena (public auction detail page)
   ============================================================ */
@keyframes bid-counter-glow {
    0%, 100% { filter: drop-shadow(0 0 6px rgba(106,255,193,.35)); }
    50%      { filter: drop-shadow(0 0 16px rgba(106,215,255,.7)); }
}

@utility animate-bid-counter {
    animation: bid-counter-glow 2.4s ease-in-out infinite;
}

/* Dark radial backdrop for the auction "arena" panel. */
@utility arena-surface {
    background: radial-gradient(120% 90% at 20% 0%, #2a1840 0%, #0d1220 62%);
}
```

- [ ] **Step 2: Verify the stylesheet compiles**

Run: `npm run build`
Expected: build completes with no CSS errors; `public/build/` is regenerated.

- [ ] **Step 3: Commit**

```bash
git add resources/css/app.css
git commit -m "feat(auctions): add neon arena css utilities"
```

---

### Task 3: Alpine components + card-picker

**Files:**
- Modify: `resources/js/app.js`
- Create: `resources/views/components/card-picker.blade.php`

- [ ] **Step 1: Register the Alpine components**

The file currently contains only the import/`Alpine.start()` boilerplate (6 lines). Replace its entire contents with:

```js
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
```

- [ ] **Step 2: Create the card-picker component**

Create `resources/views/components/card-picker.blade.php`:

```blade
@props(['selected' => null])

{{--
  Card picker — a search-driven modal for choosing a catalogue card.
  Renders a hidden <input name="card_id"> consumed by the host <form>.
  `selected` (optional) pre-fills the picker in edit mode; pass an object
  exposing id, name, image_small, set_name.
--}}
<div
    x-data="cardPicker(@js($selected ? [
        'id' => $selected->id,
        'name' => $selected->name,
        'image_small' => $selected->image_small,
        'set_name' => $selected->set_name ?? null,
    ] : null))"
    class="space-y-3"
>
    <input type="hidden" name="card_id" :value="picked?.id ?? ''">

    {{-- Chosen-card display --}}
    <template x-if="picked">
        <div class="flex items-center gap-3 rounded-2xl border border-ink-200 bg-white p-3">
            <img :src="picked.image_small" alt="" class="h-16 w-12 rounded object-cover bg-ink-100">
            <div class="min-w-0">
                <p class="truncate text-sm font-bold" x-text="picked.name"></p>
                <p class="truncate text-[11px] text-ink-500" x-text="picked.set_name"></p>
            </div>
            <button type="button" @click="open()" class="ml-auto text-xs font-bold text-prism-violet hover:underline">
                Change
            </button>
        </div>
    </template>

    {{-- Empty state / trigger --}}
    <template x-if="!picked">
        <button
            type="button"
            @click="open()"
            class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-ink-300 px-4 py-6 text-sm font-bold text-ink-500 transition hover:border-prism-violet hover:text-prism-violet"
        >
            ◆ Choose Card from catalogue
        </button>
    </template>

    {{-- Picker modal --}}
    <div
        x-show="modal"
        x-cloak
        @keydown.escape.window="modal = false"
        class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-900/70 px-4 py-10"
    >
        <div @click.outside="modal = false" class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-black">Choose a card</h3>
                <button type="button" @click="modal = false" class="text-2xl leading-none text-ink-400 hover:text-ink-900">&times;</button>
            </div>

            <input
                type="search"
                x-model="q"
                @input.debounce.300ms="search()"
                placeholder="Search 2,000+ cards by name…"
                class="mt-4 w-full rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet"
            >

            <div class="mt-4 grid max-h-[55vh] grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-3">
                <template x-for="card in results" :key="card.id">
                    <button
                        type="button"
                        @click="choose(card)"
                        class="rounded-2xl border border-ink-200 p-2 text-left transition hover:border-prism-violet hover:shadow-lg"
                    >
                        <img :src="card.image_small" alt="" class="aspect-[3/4] w-full rounded-lg object-cover bg-ink-100">
                        <p class="mt-1.5 truncate text-xs font-bold" x-text="card.name"></p>
                        <p class="truncate text-[10px] text-ink-500" x-text="card.rarity"></p>
                    </button>
                </template>

                <template x-if="!loading && results.length === 0">
                    <p class="col-span-full py-10 text-center text-sm text-ink-500">No cards found.</p>
                </template>
            </div>

            <p x-show="loading" class="mt-3 text-center text-xs text-ink-500">Searching…</p>
        </div>
    </div>
</div>
```

- [ ] **Step 3: Verify the JS bundle builds**

Run: `npm run build`
Expected: build completes with no errors.

- [ ] **Step 4: Commit**

```bash
git add resources/js/app.js resources/views/components/card-picker.blade.php
git commit -m "feat(auctions): add alpine components and card-picker"
```

---

## Chunk 2: Admin views, public re-skin, tests

### Task 4: Admin auctions index view

**Files:**
- Create: `resources/views/admin/auctions/index.blade.php`

- [ ] **Step 1: Create the index view**

Create `resources/views/admin/auctions/index.blade.php`:

```blade
<x-admin-layout heading="Auctions" eyebrow="Bidding console">
    <x-slot:actions>
        <x-prism-button :href="route('admin.auctions.create')" size="sm">+ New Auction</x-prism-button>
    </x-slot:actions>

    @if($auctions->isEmpty())
        <x-empty-state icon="⬢" title="No auctions yet" message="Open a card for bidding to get the hype started.">
            <x-prism-button :href="route('admin.auctions.create')" size="sm">+ New Auction</x-prism-button>
        </x-empty-state>
    @else
        <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                    <tr>
                        <th class="px-4 py-3">Card</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Current bid</th>
                        <th class="px-4 py-3">Highlighted bidder</th>
                        <th class="px-4 py-3">Ends</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach($auctions as $auction)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-9 overflow-hidden rounded bg-ink-100">
                                        @if($auction->card?->image_small)
                                            <img src="{{ $auction->card->image_small }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <p class="font-bold">{{ $auction->card?->name ?? '—' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusCls = [
                                        'live'      => 'bg-rose-100 text-rose-700',
                                        'scheduled' => 'bg-amber-100 text-amber-700',
                                        'ended'     => 'bg-ink-200 text-ink-700',
                                        'cancelled' => 'bg-ink-100 text-ink-500',
                                    ][$auction->status] ?? 'bg-ink-100 text-ink-500';
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest {{ $statusCls }}">
                                    {{ $auction->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono">@idr($auction->current_bid)</td>
                            <td class="px-4 py-3">
                                <span class="font-semibold">{{ $auction->currentLeader?->name ?? '—' }}</span>
                                <span class="ml-1 text-[10px] uppercase tracking-wide text-ink-400">
                                    ({{ $auction->highlight_mode ?? 'auto' }})
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-500">{{ $auction->ends_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('admin.auctions.edit', $auction) }}"
                                   class="text-xs font-semibold hover:text-prism-violet">Manage</a>
                                <form method="POST" action="{{ route('admin.auctions.destroy', $auction) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Remove this auction?')"
                                            class="text-xs font-semibold text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
```

- [ ] **Step 2: Verify the page renders**

Run: `php artisan route:list --name=admin.auctions.index` to confirm the route exists, then (with `php artisan serve` running and logged in as an admin) open `/admin/auctions` in a browser.
Expected: a table with three sample auctions (Charizard ex / Pikachu VMAX / Mewtwo GX), each with a status badge, current bid, highlighted bidder + mode, and Manage/Delete actions. (This is covered automatically by the Task 8 test.)

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/auctions/index.blade.php
git commit -m "feat(admin): add auction list view"
```

---

### Task 5: Admin auction create view

**Files:**
- Create: `resources/views/admin/auctions/create.blade.php`

- [ ] **Step 1: Create the create view**

Create `resources/views/admin/auctions/create.blade.php`:

```blade
<x-admin-layout heading="New auction" eyebrow="Open a card for bidding">
    <form id="auction-create-form" method="POST" action="{{ route('admin.auctions.store') }}" class="space-y-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Card selection --}}
            <div class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-7">
                <h2 class="font-display text-base font-black">Auction card</h2>
                <p class="text-xs text-ink-500">Pick the physical card you want to open for bidding.</p>
                <x-card-picker />
                @error('card_id')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bid configuration --}}
            <aside class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-5">
                <h2 class="font-display text-base font-black">Bid settings</h2>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starting bid (Rp)</span>
                    <input type="number" step="500" min="0" name="starting_bid" required
                           value="{{ old('starting_bid', 0) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Bid increment (Rp)</span>
                    <input type="number" step="500" min="1" name="bid_increment" required
                           value="{{ old('bid_increment', 50000) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Buy-now price (Rp) — optional</span>
                    <input type="number" step="500" min="0" name="buy_now_price"
                           value="{{ old('buy_now_price') }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starts at</span>
                        <input type="datetime-local" name="starts_at" required
                               value="{{ old('starts_at') }}"
                               class="mt-1.5 w-full rounded-xl border-ink-200">
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Ends at</span>
                        <input type="datetime-local" name="ends_at" required
                               value="{{ old('ends_at') }}"
                               class="mt-1.5 w-full rounded-xl border-ink-200">
                    </label>
                </div>
            </aside>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.auctions.index') }}"
               class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
            {{-- Opens the confirmation modal instead of submitting directly. --}}
            <button type="button"
                    @click="$dispatch('open-modal', 'confirm-publish')"
                    class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                Publish auction
            </button>
        </div>
    </form>

    {{-- Publish confirmation --}}
    <x-modal name="confirm-publish" maxWidth="md">
        <div class="p-6">
            <h3 class="font-display text-lg font-black">Publish this auction?</h3>
            <p class="mt-2 text-sm text-ink-500">
                It will become visible to all users. Double-check the card, starting bid, increment and timing before publishing.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        @click="$dispatch('close-modal', 'confirm-publish')"
                        class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</button>
                {{-- The form="" attribute submits the create form from outside it. --}}
                <button type="submit" form="auction-create-form"
                        class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                    Publish
                </button>
            </div>
        </div>
    </x-modal>
</x-admin-layout>
```

Note: the `@click="$dispatch(...)"` on the plain `<button>` works because `<x-admin-layout>` loads Alpine globally (via `@vite`), and `$dispatch` is available on any element without an `x-data` wrapper.

- [ ] **Step 2: Verify the page renders**

With `php artisan serve` running and logged in as admin, open `/admin/auctions/create`.
Expected: the "Choose Card from catalogue" dashed button, the four bid-setting inputs, and a "Publish auction" button that opens the confirmation modal. Clicking the card button opens the search modal with sample cards. (Covered by the Task 8 test.)

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/auctions/create.blade.php
git commit -m "feat(admin): add auction create form with card picker and publish confirmation"
```

---

### Task 6: Admin auction edit view (bid management)

**Files:**
- Create: `resources/views/admin/auctions/edit.blade.php`

- [ ] **Step 1: Create the edit view**

Create `resources/views/admin/auctions/edit.blade.php`:

```blade
<x-admin-layout heading="Manage auction" eyebrow="Edit & highlight">
    @php
        $rankedBids = $auction->bids->sortByDesc('amount')->values();
    @endphp

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Auction settings --}}
        <form method="POST" action="{{ route('admin.auctions.update', $auction) }}"
              class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-7">
            @csrf
            @method('PATCH')

            <h2 class="font-display text-base font-black">Auction settings</h2>

            {{-- Card is fixed once an auction exists — display only. --}}
            <div class="flex items-center gap-3 rounded-2xl border border-ink-200 bg-ink-50 p-3">
                <span class="inline-flex h-16 w-12 overflow-hidden rounded bg-ink-100">
                    @if($auction->card?->image_small)
                        <img src="{{ $auction->card->image_small }}" alt="" class="h-full w-full object-cover">
                    @endif
                </span>
                <div>
                    <p class="text-sm font-bold">{{ $auction->card?->name ?? '—' }}</p>
                    <p class="text-[11px] text-ink-500">Card cannot be changed after creation</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starting bid (Rp)</span>
                    <input type="number" step="500" min="0" name="starting_bid"
                           value="{{ old('starting_bid', $auction->starting_bid) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Bid increment (Rp)</span>
                    <input type="number" step="500" min="1" name="bid_increment"
                           value="{{ old('bid_increment', $auction->bid_increment) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Buy-now price (Rp)</span>
                    <input type="number" step="500" min="0" name="buy_now_price"
                           value="{{ old('buy_now_price', $auction->buy_now_price) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Status</span>
                    <select name="status" class="mt-1.5 w-full rounded-xl border-ink-200">
                        @foreach(\App\Models\Auction::STATUSES as $s)
                            <option value="{{ $s }}" @selected(old('status', $auction->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starts at</span>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', $auction->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Ends at</span>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', $auction->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.auctions.index') }}"
                   class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
                <x-prism-button type="submit" size="md">Save changes</x-prism-button>
            </div>
        </form>

        {{-- Bid management / highlight panel --}}
        <aside class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-5"
               x-data="{ pending: null }">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-base font-black">Highlighted bid</h2>
                @php $mode = $auction->highlight_mode ?? 'auto'; @endphp
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest
                    {{ $mode === 'manual' ? 'bg-prism-violet/15 text-prism-violet' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $mode === 'manual' ? 'Manual override' : 'Auto (highest)' }}
                </span>
            </div>
            <p class="text-xs text-ink-500">
                The highlighted bidder is shown as the winner on the public page. By default the highest bid is highlighted automatically; pick a bid below to override.
            </p>

            {{-- Reset to auto --}}
            @if($mode === 'manual')
                <form method="POST" action="{{ route('admin.auctions.highlight.reset', $auction) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Reset the highlight to the highest bid?')"
                            class="w-full rounded-full border border-ink-200 px-4 py-2 text-xs font-bold hover:bg-ink-50">
                        ↺ Reset to auto (highest wins)
                    </button>
                </form>
            @endif

            {{-- Bid list --}}
            <div class="space-y-1.5">
                @forelse($rankedBids as $i => $bid)
                    @php $isLeader = $bid->user_id === $auction->current_leader_id; @endphp
                    <div class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm
                        {{ $isLeader ? 'border border-prism-violet bg-prism-violet/5' : 'bg-ink-50' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white text-[11px] font-black">
                            {{ $isLeader ? '👑' : $i + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</p>
                            <p class="text-[10px] text-ink-400">{{ $bid->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="ml-auto font-mono font-bold">@idr($bid->amount)</span>
                        @if($isLeader)
                            <span class="rounded-full bg-prism-violet/15 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-prism-violet">
                                Highlighted
                            </span>
                        @else
                            {{-- @js() emits a correctly JS- and HTML-attribute-escaped string,
                                 so usernames containing quotes are handled safely. --}}
                            <button type="button"
                                    @click="pending = {
                                        url: '{{ route('admin.auctions.highlight', $auction) }}',
                                        label: @js('Highlight ' . ($bid->user?->name ?? 'this bidder') . ' as the winner?')
                                    }"
                                    class="rounded-full bg-ink-900 px-3 py-1 text-[10px] font-bold text-white hover:bg-ink-700">
                                Highlight
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="rounded-xl bg-ink-50 px-3 py-6 text-center text-sm text-ink-500">No bids yet.</p>
                @endforelse
            </div>

            {{-- Shared highlight confirmation modal (one modal, action bound at click time) --}}
            <div x-show="pending" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/70 px-4"
                 @keydown.escape.window="pending = null">
                <div @click.outside="pending = null" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                    <h3 class="font-display text-lg font-black">Change highlighted bid?</h3>
                    <p class="mt-2 text-sm text-ink-500" x-text="pending?.label"></p>
                    <p class="mt-1 text-xs text-ink-400">This switches the highlight to Manual override.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="pending = null"
                                class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</button>
                        <form method="POST" :action="pending?.url">
                            @csrf
                            <button type="submit"
                                    class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                                Highlight bidder
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-admin-layout>
```

- [ ] **Step 2: Verify the page renders**

With `php artisan serve` running and logged in as admin, open `/admin/auctions/1/edit`.
Expected: the settings form (card shown read-only), and a "Highlighted bid" panel with the four sample bids — the leader row crowned and badged "Highlighted", the others showing a "Highlight" button that opens the confirmation modal. Because sample auction `1` has `highlight_mode = manual`, the "Reset to auto" button is visible. (Covered by the Task 8 test.)

- [ ] **Step 3: Commit**

```bash
git add resources/views/admin/auctions/edit.blade.php
git commit -m "feat(admin): add auction edit view with bid highlight panel"
```

---

### Task 7: Public auction page — Neon Arena re-skin

**Files:**
- Modify (replace whole file): `resources/views/pages/auctions/show.blade.php`

- [ ] **Step 1: Replace the public auction detail view**

Replace the entire contents of `resources/views/pages/auctions/show.blade.php` with:

```blade
<x-app-layout>

<section class="mx-auto max-w-[1200px] px-4 py-10 md:px-8">
    <a href="{{ route('auctions.index') }}" class="text-xs font-semibold text-ink-500 hover:text-ink-900">← Back to auctions</a>

    @php
        $rankedBids = $auction->bids->sortByDesc('amount')->values();
        $feedBids   = $auction->bids->sortByDesc('created_at')->take(20);
    @endphp

    <div
        x-data="auctionCountdown('{{ $auction->ends_at?->toIso8601String() }}')"
        class="arena-surface relative mt-5 overflow-hidden rounded-[2rem] border border-prism-violet/40 p-6 text-ink-50 shadow-2xl md:p-10"
    >
        {{-- ambient glow blobs --}}
        <div class="pointer-events-none absolute -left-24 -top-24 h-64 w-64 rounded-full bg-prism-pink/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-20 h-72 w-72 rounded-full bg-prism-sky/20 blur-3xl"></div>

        <div class="relative grid gap-8 lg:grid-cols-12">
            {{-- Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    <div class="absolute -inset-3 -z-10 rounded-3xl prism-bg opacity-60 blur-2xl"></div>
                    <div class="overflow-hidden rounded-3xl bg-ink-900/60 p-3 ring-1 ring-white/10">
                        @if($auction->card?->image_large)
                            <img src="{{ $auction->card->image_large }}" alt="{{ $auction->card?->name }}" class="rounded-2xl">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bid arena --}}
            <div class="lg:col-span-7">
                @if($auction->is_live)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-prism-mint px-3 py-1 text-[11px] font-black uppercase tracking-widest text-ink-900">
                        <span class="h-2 w-2 animate-ping rounded-full bg-ink-900"></span> Live Now
                    </span>
                @else
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest">{{ $auction->status }}</span>
                @endif

                <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">{{ $auction->card?->name }}</h1>
                <p class="mt-1 text-sm text-white/50">Listed by {{ $auction->seller?->name ?? 'PokeTrade' }}</p>

                {{-- Current bid + timer + buy now --}}
                <div class="mt-6 flex flex-wrap items-end gap-x-10 gap-y-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Current Bid</p>
                        <p class="animate-bid-counter mt-1 bg-gradient-to-r from-prism-mint to-prism-sky bg-clip-text font-display text-5xl font-black text-transparent">
                            @idr($auction->current_bid)
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Ends In</p>
                        <p class="mt-1 font-mono text-2xl font-bold text-prism-pink" x-text="display">—</p>
                    </div>
                    @if($auction->buy_now_price)
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Buy Now</p>
                            <p class="mt-1 font-display text-2xl font-bold text-prism-gold">@idr($auction->buy_now_price)</p>
                        </div>
                    @endif
                </div>

                {{-- Top bidders leaderboard --}}
                <div class="mt-7">
                    <p class="text-xs font-black uppercase tracking-widest text-prism-pink">🔥 Top Bidders</p>
                    <div class="mt-2 space-y-1.5">
                        @forelse($rankedBids->take(5) as $i => $bid)
                            @php $isLeader = $bid->user_id === $auction->current_leader_id; @endphp
                            <div class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm
                                {{ $isLeader
                                    ? 'border border-prism-pink bg-gradient-to-r from-prism-pink/25 to-prism-violet/15 shadow-[0_0_18px_-2px] shadow-prism-pink/50'
                                    : 'bg-white/5' }}">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-black
                                    {{ $i === 0 ? 'bg-gradient-to-br from-prism-gold to-prism-pink text-ink-900' : 'bg-white/10' }}">
                                    {{ $isLeader ? '👑' : $i + 1 }}
                                </span>
                                <span class="font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</span>
                                @if($isLeader)
                                    <span class="rounded-full bg-prism-pink/30 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider">Winning</span>
                                @endif
                                <span class="ml-auto font-mono font-bold">@idr($bid->amount)</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-white/5 px-3 py-4 text-center text-sm text-white/50">No bids yet — be the first to strike ⚡</p>
                        @endforelse
                    </div>
                </div>

                {{-- Bid form --}}
                @auth
                    <form method="POST" action="{{ route('auctions.bid', $auction) }}" class="mt-6 flex flex-wrap items-end gap-3">
                        @csrf
                        <label class="flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Your bid</span>
                            <input type="number" step="0.01" name="amount" min="{{ $auction->min_next_bid }}"
                                   placeholder="≥ @idr($auction->min_next_bid)"
                                   class="mt-1 w-full rounded-full border-white/15 bg-white/10 text-ink-50 placeholder-white/30 focus:border-prism-mint focus:ring-prism-mint">
                        </label>
                        <button type="submit"
                                class="rounded-full bg-gradient-to-r from-prism-pink to-prism-mint px-8 py-3 font-display font-black text-ink-900 shadow-[0_0_24px_-4px] shadow-prism-pink/70 transition hover:-translate-y-0.5">
                            Place Your Bid ⚡
                        </button>
                    </form>
                @else
                    <div class="mt-6 rounded-2xl bg-white/5 p-4 text-sm text-white/70">
                        <a href="{{ route('login') }}" class="font-bold text-prism-mint underline">Log in</a> to join the bidding war.
                    </div>
                @endauth
            </div>
        </div>

        {{-- Live bid feed --}}
        <div class="relative mt-8 border-t border-white/10 pt-5">
            <p class="text-xs font-black uppercase tracking-widest text-prism-sky">⚡ Live Bid Feed</p>
            <div class="mt-2 max-h-56 space-y-1 overflow-y-auto">
                @forelse($feedBids as $bid)
                    <div class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs">
                        <span class="text-prism-mint">⚡</span>
                        <span class="font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</span>
                        <span class="text-white/40">bid</span>
                        <span class="font-mono font-bold text-prism-sky">@idr($bid->amount)</span>
                        <span class="ml-auto text-white/30">{{ $bid->created_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="px-3 py-3 text-xs text-white/40">Bids will appear here as they happen.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

</x-app-layout>
```

- [ ] **Step 2: Verify the page renders**

This page uses the **real** `AuctionController@show`, so it needs a real auction record. Verification is handled by the Task 8 feature test (which seeds one). For a manual check, seed an auction via `php artisan tinker` and open `/auctions/{id}`.
Expected: dark "arena" panel, glowing gradient current-bid figure, a ticking `HH:MM:SS` countdown, the 🔥 Top Bidders leaderboard with the leader crowned/glowing, and the ⚡ Live Bid Feed.

- [ ] **Step 3: Commit**

```bash
git add resources/views/pages/auctions/show.blade.php
git commit -m "feat(auctions): re-skin public auction page as neon arena"
```

---

### Task 8: Feature test

**Files:**
- Create: `tests/Feature/AdminAuctionTest.php`

- [ ] **Step 1: Write the feature test**

Create `tests/Feature/AdminAuctionTest.php`:

```php
<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Card;
use App\Models\User;

/**
 * Returns a freshly-created admin user. `role` is force-filled to bypass
 * mass-assignment guarding in case it is not in the User model's $fillable.
 */
function adminUser(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    return $user;
}

test('admin auctions index renders the auction table', function () {
    $this->actingAs(adminUser())
        ->get('/admin/auctions')
        ->assertOk()
        ->assertSee('New Auction')
        ->assertSee('Charizard ex');
});

test('admin auction create page renders with the card picker', function () {
    $this->actingAs(adminUser())
        ->get('/admin/auctions/create')
        ->assertOk()
        ->assertSee('Choose Card')
        ->assertSee('Publish auction');
});

test('admin auction edit page renders the bid highlight panel', function () {
    $this->actingAs(adminUser())
        ->get('/admin/auctions/1/edit')
        ->assertOk()
        ->assertSee('Highlighted bid')
        ->assertSee('Save changes');
});

test('card search stub returns json results', function () {
    $this->actingAs(adminUser())
        ->getJson('/admin/auctions/cards/search?q=char')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'image_small', 'set_name', 'rarity']]])
        ->assertJsonFragment(['name' => 'Charizard ex']);
});

test('non-admin users cannot reach the auction admin', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/auctions')
        ->assertForbidden();
});

test('public auction page renders the neon arena', function () {
    $seller = User::factory()->create();
    $leader = User::factory()->create(['name' => 'ashketchum_id']);

    $card = Card::create([
        'api_id'      => 'test-arena-001',
        'name'        => 'Charizard ex',
        'slug'        => 'charizard-ex-arena-test',
        'supertype'   => 'Pokémon',
        'image_small' => 'https://images.pokemontcg.io/sv3/6.png',
        'image_large' => 'https://images.pokemontcg.io/sv3/6_hires.png',
    ]);

    $auction = Auction::create([
        'card_id'           => $card->id,
        'seller_id'         => $seller->id,
        'current_leader_id' => $leader->id,
        'starting_bid'      => 500000,
        'current_bid'       => 4250000,
        'bid_increment'     => 50000,
        'starts_at'         => now()->subHour(),
        'ends_at'           => now()->addHours(2),
        'status'            => 'live',
    ]);

    Bid::create(['auction_id' => $auction->id, 'user_id' => $leader->id, 'amount' => 4250000]);

    $this->get("/auctions/{$auction->id}")
        ->assertOk()
        ->assertSee('Top Bidders')
        ->assertSee('Live Bid Feed')
        ->assertSee('ashketchum_id');
});
```

Note: `User::factory()->create(['name' => ...])` is safe because `name` is in the User model's `$fillable`. If the test database is not auto-reset, confirm `tests/Pest.php` applies `RefreshDatabase` to the `Feature` directory (the existing `ProfileTest.php` relies on this, so it should already be configured).

- [ ] **Step 2: Run the test and verify it passes**

Run: `php artisan test --filter=AdminAuctionTest`
Expected: 6 passing tests. If the public-page test fails on a missing `Card` column, adjust the `Card::create([...])` payload to satisfy non-nullable columns (check `database/migrations/*create_cards_table*`).

- [ ] **Step 3: Run the full test suite for regressions**

Run: `php artisan test`
Expected: all tests pass (no regression in `ProfileTest`, `Auth`, `ExampleTest`).

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/AdminAuctionTest.php
git commit -m "test(auctions): cover admin auction routes and public arena page"
```

---

## Done — Definition of Done

- `php artisan test` passes (all suites, including the 6 new tests).
- `npm run build` compiles with no errors.
- Logged in as an admin, `/admin/auctions`, `/admin/auctions/create`, and `/admin/auctions/{id}/edit` all render; the card picker searches and selects; the publish-confirmation and highlight-confirmation modals work.
- The public `/auctions/{id}` page shows the Neon Arena with a ticking countdown, leaderboard, and live feed.
- Every place a backend dev must take over is marked with a `// TODO(backend)` comment (controller methods, sample-data section, `highlight_mode` migration, real-time polling hook).
