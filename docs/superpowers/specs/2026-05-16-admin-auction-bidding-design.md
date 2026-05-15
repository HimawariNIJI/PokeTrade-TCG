# Admin Auction Bidding Feature — Design

**Date:** 2026-05-16
**Status:** Approved (design); ready for implementation planning
**Scope:** Frontend only (Blade views + Alpine.js) with stub routes/controllers. Backend dev owns persistence, validation, real-time, and the migration.

## 1. Problem & Goal

PokeTrade already has an auction system — `Auction` and `Bid` models, a `bids` table, and public pages at `/auctions` and `/auctions/{auction}`. What is missing is an **admin-facing UI** to create and manage auctions, and the public auction detail page is plain.

Admins need to take a physical card from the catalog and open it for public bidding. Users place multiple bids; exactly one bid is "highlighted" (the current winner). The public auction page should feel hype-y to drive engagement.

This feature **extends the existing auction system**. It does not create a parallel one.

## 2. Decisions (from brainstorming)

| Decision | Choice |
|---|---|
| Deliverable scope | Blade views + Alpine.js **plus** stub routes & controller methods returning hardcoded sample data with `// TODO(backend)` markers. Everything renders in a browser immediately. |
| Existing system | Extend the existing `Auction`/`Bid` models, tables, and public pages. No duplicate data models. |
| Highlighted bid | Default = highest bid auto-highlighted. Admin can set a specific bid as the highlight. A **sticky override** model: mode is `auto` or `manual`; manual sticks to the chosen bidder even if a higher bid arrives, until the admin clicks "Reset to auto". In **both** modes the highlighted bidder is the single value `auctions.current_leader_id`; `highlight_mode` only records *how* that value is chosen. The admin edit panel and the public page both read `current_leader_id` as the source of truth. |
| Public page style | Direction A — "Neon Arena": loud, gamified, prism neon gradients, pulsing bid counter, top-bidder leaderboard, live bid feed. |

## 3. Out of Scope (backend dev owns)

- Real database persistence (create/update/delete auctions, store bids).
- The `highlight_mode` migration (see §7).
- Validation rules, authorization, status transitions (`scheduled` → `live` → `ended`).
- Real-time updates / websockets. The frontend provides an optional polling hook stub only.
- The real card-search query against the `Card` model.

## 4. Architecture

Server-rendered Laravel 11 + Blade + Alpine.js 3, styled with Tailwind CSS 4 and the existing Prismatic theme. No JavaScript framework. The feature is three view groups plus thin stub plumbing:

```
routes/web.php
  admin group  ──> Admin\AuctionController (stub)  ──> resources/views/admin/auctions/*
  public route ──> existing AuctionController       ──> resources/views/pages/auctions/show.blade.php (re-skinned)
```

### Components

Each unit below has one clear purpose, a defined interface, and can be understood independently.

| Unit | Purpose | Depends on |
|---|---|---|
| `Admin\AuctionController` (stub) | Routes admin requests to views; returns hardcoded sample data. Each method carries a `// TODO(backend)` note describing the real behavior. | `Auction` model (types only), sample data |
| `admin/auctions/index.blade.php` | Lists all auctions in a table with status, current bid, highlighted bidder, actions. | `<x-admin-layout>`, status badge partial |
| `admin/auctions/create.blade.php` | New-auction form with the card picker and a publish-confirmation modal. | card-picker component, `<x-modal>`, `<x-prism-button>` |
| `admin/auctions/edit.blade.php` | Edit auction fields + bid-management panel (highlight controls). | card-picker component, `<x-modal>`, bid-row partial |
| `components/card-picker.blade.php` | Reusable Alpine modal: search the 2k-card catalog by name, pick one, write its id/name/image into the host form. | stubbed JSON search endpoint |
| `pages/auctions/show.blade.php` (re-skin) | Public auction detail in the Neon Arena style: hero, pulsing counter, leaderboard, live feed, bid button. | `auctionLive` Alpine component, neon CSS |
| `auctionLive` Alpine component | Drives the countdown timer; exposes a commented optional polling hook for live bid refresh. | `resources/js/app.js` |
| Neon CSS additions | `pulse-glow` and related keyframes/utilities in `resources/css/app.css`. | existing `@theme` tokens |

## 5. Admin UI Detail

### 5.1 Routes (`routes/web.php`, inside the existing `auth`+`admin` group)

```php
// Custom routes MUST be registered BEFORE Route::resource so the literal
// `cards/search` segment is not captured by the resource `{auction}` param.
Route::get('auctions/cards/search', [Admin\AuctionController::class, 'cardSearch'])->name('auctions.cards.search');
Route::post('auctions/{auction}/highlight', [Admin\AuctionController::class, 'highlight'])->name('auctions.highlight');
Route::post('auctions/{auction}/highlight/reset', [Admin\AuctionController::class, 'resetHighlight'])->name('auctions.highlight.reset');
Route::resource('auctions', Admin\AuctionController::class)->except('show');
```

### 5.2 Index (`admin/auctions`)
Table columns: card thumbnail + name, status badge (`scheduled` / `live` / `ended` / `cancelled`), current bid (IDR), highlighted bidder name, end time, actions (Edit, Delete). A "New Auction" button in the layout actions slot. Uses the existing admin table styling. Empty state via `<x-empty-state>`.

### 5.3 Create (`admin/auctions/create`)
Form fields:
- **Card** — read-only display populated by the card picker. A "Choose Card" button opens the picker modal. Stores `card_id` in a hidden input; shows the chosen card's thumbnail + name.
- **Starting bid** (IDR, required), **Bid increment** (IDR, required), **Buy-now price** (IDR, optional).
- **Starts at**, **Ends at** (datetime-local, required).

A **confirmation modal** (`<x-modal name="confirm-publish">`) intercepts submit: "Publish this auction? It will go live to all users." with Cancel / Publish buttons. The form only POSTs when Publish is clicked.

### 5.4 Edit (`admin/auctions/{id}/edit`)
Same fields as Create (card not changeable — display only), plus the **bid management panel**:
- A list of all bids (bidder name, amount, time), highest first.
- Each row has a "Highlight" button. The currently highlighted row is visually marked.
- A mode badge: `Auto` (highest wins) or `Manual override` (admin pinned a bidder).
- Clicking "Highlight" on a row POSTs to `auctions.highlight` and sets mode to `manual`.
- A "Reset to auto" button POSTs to `auctions.highlight.reset` and returns to highest-wins.
- Highlight actions are also gated by a small confirmation modal to prevent misclicks.

### 5.5 Card picker (`components/card-picker.blade.php`)
An Alpine-driven modal. A search input debounces and fetches `route('admin.auctions.cards.search', {q})`, which the stub controller answers with sample card JSON (`id`, `name`, `image_small`, `set_name`, `rarity`). Results render as a scrollable grid; clicking one closes the modal and writes the selection back into the host form. Backend dev replaces the stub query with a real paginated `Card` lookup.

## 6. Public Auction Page — Neon Arena

Re-skin `resources/views/pages/auctions/show.blade.php`:

- **Hero**: dark radial prism-gradient panel; large card image with glow; a `LIVE NOW` pill.
- **Current bid**: large gradient (mint→sky) number with a `pulse-glow` animation.
- **Countdown**: neon-pink timer driven by the `auctionLive` Alpine component.
- **🔥 Top Bidders leaderboard**: ranked rows. Rank 1 = crown icon + glowing pink-bordered row (this is the highlighted bidder, i.e. `current_leader_id`). Ranks 2–3 styled but calmer.
- **⚡ Live bid feed**: recent bids as a styled list. Rendered from server data on load.
- **Place Your Bid**: glowing gradient button; preserves the existing `@auth` bid form and `min` validation.

The `auctionLive` Alpine component owns the countdown tick and exposes a commented-out polling hook (`// TODO(backend): poll a bids endpoint every Ns to refresh feed + leaderboard`). Neon keyframes added to `resources/css/app.css` alongside the existing `@theme` block.

Scope note: only the auction **detail** page (`show.blade.php`) is re-skinned. The auction index/list page is unchanged.

## 7. Backend Handoff

`Admin\AuctionController` opens with a `// TODO(backend)` block enumerating, per method, the real behavior required:

- `index` — list real auctions, paginated, eager-load card + leader.
- `create` — just renders the form (no data).
- `store` — validate inputs, create the `Auction`, set initial `status`.
- `edit` / `update` — load auction + bids; validate and persist edits.
- `destroy` — delete/cancel the auction.
- `cardSearch` — replace sample JSON with a real paginated `Card` name search.
- `highlight` / `resetHighlight` — set `current_leader_id` + `highlight_mode`.

**New column required (backend dev):** add `highlight_mode` (enum/string `auto|manual`, default `auto`) to the `auctions` table via a migration. The frontend assumes this attribute exists and references it; until the migration lands the stub sample data supplies it.

## 8. Testing

A Laravel feature test (`tests/Feature/`) that, acting as an admin user:

- GETs `admin/auctions`, `admin/auctions/create`, and `admin/auctions/{id}/edit` and asserts HTTP 200.
- GETs the public `auctions/{auction}` page and asserts HTTP 200.
- GETs the stub `admin/auctions/cards/search?q=...` endpoint and asserts a 200 JSON response with the expected card-result shape.
- Asserts key text/markers are present in each response (e.g. "New Auction", "Choose Card", "Top Bidders").

Because controller logic is stubbed, this test verifies the **views render without Blade errors** against sample data — the meaningful guarantee for a frontend-only deliverable. Manual verification: open each page in a browser and exercise the card picker, confirmation modal, and highlight controls.

## 9. Risks & Notes

- **Stub/real divergence**: sample data shapes must match the real `Auction`/`Bid`/`Card` attributes so the backend swap is mechanical. Mitigation: sample data mirrors the documented model `$fillable`/`$casts`.
- **`highlight_mode` dependency**: the frontend references a column that does not exist yet. Mitigation: §7 calls it out explicitly; stub data provides it so views render before the migration.
- **Card search performance**: 2k cards — the real query must be paginated/limited. The stub already shapes results as a capped list so the UI is built for it.
