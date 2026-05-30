# PokeTrade-TCG — Manual QA Session

A checklist for a human tester to walk through the app, plus the exact info to send Claude when a bug is found.

---

## 0. Setup before testing

Make sure the app is fresh and seeded so test data exists:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run build          # or `npm run dev` if you want hot-reload while testing
```

Site URL (Herd): `http://poketrade-tcg.test`
Site URL (no Herd, `composer run dev`): `http://127.0.0.1:8000`

### Test accounts (from [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php))

| Role | Email | Password | Notes |
|---|---|---|---|
| Admin | `admin@poketrade.test` | `password` | Full admin panel access (`/admin`) |
| Customer | `trainer@poketrade.test` | `password` | Normal trainer — has seeded points/orders |
| E2E Customer | `e2e@poketrade.test` | `password123` | Only seeded in `local`/`testing` envs; 500 points pre-loaded |

### New account creation (test this fresh, not just the seeded users)

- Register at [/register](http://poketrade-tcg.test/register) with a brand-new email
- Test "Sign in with Google" at [/auth/google](http://poketrade-tcg.test/auth/google) (needs Google OAuth env vars in `.env`)
- Test password reset flow: [/forgot-password](http://poketrade-tcg.test/forgot-password) → OTP email → verify → reset

> ⚠️ Known issue: an OTP password-reset Playwright test currently fails pre-existing — re-confirm manually but don't be surprised if it's the same bug.

---

## 1. Manual test checklist — Normal user (Trainer)

### 1.1 Auth & onboarding
- [ ] `/register` — submit with valid + invalid emails, weak passwords; check error messages
- [ ] `/login` — wrong password, then correct password; "remember me" checkbox
- [ ] `/auth/google` — full OAuth round-trip; check duplicate-email handling
- [ ] `/forgot-password` → OTP email arrives → `/verify-otp` → `/reset-password` → login with new password
- [ ] `/verify-email` flow after a fresh register
- [ ] `/logout` redirects and clears session

### 1.2 Public browsing (no auth required)
- [ ] [/](http://poketrade-tcg.test/) — Home hero, featured sections, CTA buttons
- [ ] [/about](http://poketrade-tcg.test/about) — copy renders, no broken layout
- [ ] [/cards](http://poketrade-tcg.test/cards) — grid loads, filters/search work, pagination
- [ ] `/cards/{slug}` — card detail, price history chart, "Add to wishlist" CTA (prompts login)
- [ ] [/shop](http://poketrade-tcg.test/shop) — merch grid, filters
- [ ] `/shop/{slug}` — product detail page, "Add to cart" works
- [ ] [/auctions](http://poketrade-tcg.test/auctions) — list, countdown timers, hero banner backdrop visible
- [ ] `/auctions/{id}` — detail, bid history, countdown, refresh button
- [ ] [/gacha](http://poketrade-tcg.test/gacha) — banner backdrop renders, pull CTA visible
- [ ] [/forums](http://poketrade-tcg.test/forums) — categories list, recent threads, hero banner
- [ ] `/forums/c/{slug}` — category page, threads list
- [ ] `/forums/t/{id}` — thread view, replies, vote/reaction UI if any
- [ ] [/forums/shoutbox](http://poketrade-tcg.test/forums/shoutbox) — chat-like view
- [ ] [/leaderboard](http://poketrade-tcg.test/leaderboard) — table loads, whole row click → profile
- [ ] `/u/{username}` — public trainer profile (avatar, banner, bio, pinned cards, comment wall)
- [ ] [/sitemap.xml](http://poketrade-tcg.test/sitemap.xml) and [/robots.txt](http://poketrade-tcg.test/robots.txt) return 200

### 1.3 Cart & merch checkout (auth required)
- [ ] Add a shop item to cart from `/shop/{slug}`
- [ ] [/cart](http://poketrade-tcg.test/cart) — update qty, remove item, subtotals update
- [ ] [/checkout](http://poketrade-tcg.test/checkout) — form validates address/shipping fields
- [ ] Place order → Midtrans payment page loads
- [ ] After payment → `/payment/{code}` shows correct status
- [ ] [/orders](http://poketrade-tcg.test/orders) — order appears with correct status
- [ ] `/orders/{code}` — line items, totals, status timeline
- [ ] Cancel an unpaid order via [/orders/{code}](http://poketrade-tcg.test/orders) — status transitions correctly

### 1.4 Auctions (auth required)
- [ ] Place a bid below current — rejected
- [ ] Place a valid bid — leaderboard updates
- [ ] Get outbid — receive notification
- [ ] Win an auction → pay → status becomes "paid"
- [ ] Request a refund on a won auction
- [ ] Try to bid on an ended auction — blocked

### 1.5 Gacha & collection (auth required)
- [ ] Pull a pack — points deducted, animation plays, cards revealed
- [ ] Insufficient points — rejected with clear message
- [ ] [/collection](http://poketrade-tcg.test/collection) — pulled cards appear, duplicates counted
- [ ] [/collection/history](http://poketrade-tcg.test/collection/history) — pull log accurate

### 1.6 Wishlist / chase cards (auth required)
- [ ] Toggle wishlist from a card page — button state flips
- [ ] [/wishlist](http://poketrade-tcg.test/wishlist) — card appears
- [ ] Untoggle — removed

### 1.7 Forums (auth required)
- [ ] [/forums/new](http://poketrade-tcg.test/forums/new) — create a thread (title + body + category)
- [ ] Edit own thread
- [ ] Delete own thread
- [ ] Reply to a thread
- [ ] Edit own reply
- [ ] Delete own reply
- [ ] Try to edit/delete someone else's — blocked (403)
- [ ] Post to [/forums/shoutbox](http://poketrade-tcg.test/forums/shoutbox)
- [ ] Report a thread / reply / profile comment — admin sees it

### 1.8 Profile & settings (auth required)
- [ ] [/profile](http://poketrade-tcg.test/profile) — edit name/email, change password, delete account
- [ ] [/settings](http://poketrade-tcg.test/settings) — upload avatar + banner, edit bio, socials, visibility toggles
- [ ] Pin/unpin showcase cards
- [ ] Profile changes reflect on public `/u/{username}` immediately
- [ ] Leave a comment on another trainer's profile, then delete it

### 1.9 Notifications
- [ ] [/notifications](http://poketrade-tcg.test/notifications) — unread badge in nav, list page works
- [ ] Trigger one of each notification type (outbid, refund, forum reply, profile comment) and confirm it shows up
- [ ] Delete a notification

### 1.10 Cross-cutting / regression
- [ ] Mobile viewport (375px) — nav, hero backdrops, forms all usable
- [ ] Tablet viewport (768px)
- [ ] Desktop 1440px+
- [ ] Dark mode (if supported) toggles correctly across all pages
- [ ] 404 page on a bogus URL
- [ ] 403 page when hitting `/admin` as a normal user
- [ ] Browser back/forward after form submits doesn't break state
- [ ] CSRF — log in, leave tab open >2 hrs, submit a form → graceful handling
- [ ] Hero banner backdrops render on: auctions, gacha, forums (index/category/thread/create/edit), leaderboard

---

## 2. Manual test checklist — Admin user

Log in as `admin@poketrade.test` / `password`. Admin panel at [/admin](http://poketrade-tcg.test/admin).

### 2.1 Dashboard
- [ ] [/admin](http://poketrade-tcg.test/admin) — KPIs, charts, recent orders/users render

### 2.2 Cards admin
- [ ] [/admin/cards](http://poketrade-tcg.test/admin/cards) — list with search/filter
- [ ] Create card
- [ ] Edit card (image, price, attributes)
- [ ] Delete card
- [ ] "Refresh cards" — triggers pokemontcg.io re-import without crashing

### 2.3 Shop admin
- [ ] [/admin/shop](http://poketrade-tcg.test/admin/shop) — list
- [ ] Create shop item with image upload — image renders on public `/shop/{slug}`
- [ ] Edit + delete shop item

### 2.4 Orders admin
- [ ] [/admin/orders](http://poketrade-tcg.test/admin/orders) — list, filter by status
- [ ] `/admin/orders/{code}` — detail page
- [ ] Update status (paid → packed → shipped → delivered) — customer sees the change

### 2.5 Users admin
- [ ] [/admin/users](http://poketrade-tcg.test/admin/users) — list with search
- [ ] `/admin/users/{id}` — detail
- [ ] Promote a customer to admin, then demote — role flips immediately

### 2.6 Reports admin
- [ ] [/admin/reports](http://poketrade-tcg.test/admin/reports) — reported items appear (after a user reports something in §1.7)
- [ ] Mark a report resolved / dismissed
- [ ] Reported thread/post — admin can take it down

### 2.7 Auctions admin
- [ ] [/admin/auctions](http://poketrade-tcg.test/admin/auctions) — list
- [ ] [/admin/auctions/create](http://poketrade-tcg.test/admin/auctions/create) — card search works (`/admin/auctions/cards/search`)
- [ ] Create an auction with start price, end date, image
- [ ] Edit an auction
- [ ] Delete an auction with no bids; deletion of one with bids → expected behaviour
- [ ] Issue a refund on a won auction
- [ ] Confirm a refund (two-step)

### 2.8 Permissions sanity
- [ ] Hit every admin URL while logged in as `trainer@poketrade.test` → all return 403
- [ ] Hit every admin URL while logged out → redirect to login

---

## 3. Bug report template — send this to Claude

> Copy this template per bug. Fill in every field you can — empty fields slow down the fix.

```markdown
### Bug: <one-line title>

**Severity:** blocker / high / medium / low / cosmetic
**Account used:** admin@poketrade.test  /  trainer@poketrade.test  /  e2e@poketrade.test  /  new account (state email)
**Role:** admin / customer / guest
**URL when bug occurred:** http://poketrade-tcg.test/...
**Route name (if known):** e.g. `auctions.bid`

---

**What I expected to happen:**
<1–2 sentences>

**What actually happened:**
<1–2 sentences>

---

### Steps to reproduce
1.
2.
3.
4. (stop the moment the bug shows)

**Reproduces every time?** yes / no / sometimes (state ratio, e.g. 3 of 5 attempts)

---

### Environment
- **Browser + version:** e.g. Chrome 131, Safari 18, Firefox 130
- **OS:** macOS 15 / Windows 11 / iOS 18 / Android 14
- **Viewport:** desktop 1440 / tablet 768 / mobile 375 / custom (state px)
- **Dark mode on?** yes / no
- **Network:** normal / throttled / offline
- **Local commit SHA:** `git rev-parse --short HEAD` →

---

### Visual evidence (attach ALL that apply)
- [ ] Screenshot of the broken state (full page if layout, zoomed if specific element)
- [ ] Screenshot of expected state (compare a working page if useful)
- [ ] Short screen recording (≤30s) if the bug involves animation, timing, or interaction
- [ ] Annotated screenshot (arrow / circle on the broken bit) for visual-only bugs

### Console + network (open DevTools BEFORE reproducing)
- [ ] **Browser console** — paste any red errors verbatim (full stack, not just the first line)
- [ ] **Network tab** — for failed requests: method, URL, status code, request payload, response body
- [ ] **Laravel log** — `tail -n 200 storage/logs/laravel.log` after repro; paste any new errors
- [ ] **Response HTML** — for 500 / "Whoops" pages: copy the exception class, message, and top of the stack trace

### Data state (if relevant)
- [ ] Was the DB freshly seeded? (`migrate:fresh --seed` y/n, when)
- [ ] Specific record IDs involved (order code, auction id, thread id, user id)
- [ ] Was `npm run dev` running, or was this against built assets?
- [ ] Any custom local changes / uncommitted edits? (`git status` output)

### Suspected area (optional — only if you have a hunch)
- File / feature you think is involved:
- Did this work before a recent change? Which one?

---

### Additional notes
<anything else — only-on-Safari, only-after-payment, related-to-X-bug, etc.>
```

---

## 4. Workflow during the session

1. Run through the §1 (normal user) checklist top-to-bottom.
2. Log out, log in as admin, run §2.
3. For each bug → fill out a §3 template entry, paste it into the chat with Claude. **One bug per message** — easier to triage and fix in isolation.
4. After Claude pushes a fix, re-test that single flow before moving on.

### Helpful commands while testing

| Need | Command |
|---|---|
| Tail Laravel errors live | `php artisan pail` |
| Last 200 log lines | `tail -n 200 storage/logs/laravel.log` |
| Reset DB to known state | `php artisan migrate:fresh --seed` |
| Open Tinker to inspect a record | `php artisan tinker` |
| Re-grab current commit SHA | `git rev-parse --short HEAD` |
| Capture full-page screenshot (macOS) | Cmd+Shift+4, then space, then click window |
