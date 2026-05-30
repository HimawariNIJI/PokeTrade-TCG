# PokeTrade-TCG — Setup Guide

A Laravel 13 + Tailwind 4 + Alpine.js storefront for Pokémon TCG cards. Cards are pulled from the public [pokemontcg.io](https://pokemontcg.io) API (Prismatic Evolutions set, `sv8pt5`).

This guide gets a fresh machine from `git clone` to a working local site.

---

## 1. Prerequisites

Install these first if you don't have them:

| Tool | Version | Why |
|---|---|---|
| [Laravel Herd](https://herd.laravel.com) | latest | Serves the site at `*.test`. Bundles PHP, Composer, Nginx. |
| PHP | `^8.3` | Comes with Herd. Verify: `php -v` |
| Composer | `^2` | Comes with Herd. Verify: `composer -V` |
| Node | `^20` | Build the frontend. Verify: `node -v` |
| npm | `^10` | Verify: `npm -v` |
| Git | any | Verify: `git --version` |

Herd is the recommended way to serve the site — everything below assumes it. If you can't use Herd, see [Alternative: `composer run dev`](#alternative-no-herd) at the bottom.

---

## 2. Clone and install

```bash
git clone <repo-url> PokeTrade-TCG
cd PokeTrade-TCG

# PHP deps
composer install

# JS deps
npm install
```

---

## 3. Environment + database

```bash
# Copy env template and generate the app key
cp .env.example .env
php artisan key:generate

# Create the SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

The project uses **SQLite by default** (set in `.env.example` as `DB_CONNECTION=sqlite`). No MySQL/Postgres setup needed.

---

## 4. Seed cards and demo data

This step fetches **180 Prismatic Evolutions cards** from pokemontcg.io and creates demo users + shop items. Needs internet on first run.

```bash
php artisan db:seed
```

Default accounts created by the seeder:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@poketrade.test` | `password` |
| Customer | `trainer@poketrade.test` | `password` |

> Cards have remote image URLs (`https://images.pokemontcg.io/...`), so an internet connection is also needed to view them in the browser.

---

## 5. Create the storage symlink

Shop item images live under `storage/app/public/`. Laravel needs a public symlink for them to load:

```bash
php artisan storage:link
```

You only need to run this once per clone.

---

## 6. Build frontend assets

```bash
npm run build
```

This builds Tailwind CSS + JS into `public/build/`. Required for Herd serving — without it you'll get a `Vite manifest not found` error.

For **active frontend development** use `npm run dev` instead — it runs the Vite dev server with hot-reload and Laravel auto-detects it via `public/hot`.

---

## 7. Serve with Herd

From the project root:

```bash
herd link poketrade-tcg
```

That symlinks the project into Herd and updates `APP_URL` in `.env` to `http://poketrade-tcg.test`. Open it:

```bash
herd open
```

Optional — HTTPS:

```bash
herd secure        # site becomes https://poketrade-tcg.test
herd unsecure      # revert
```

To remove the site later: `herd unlink poketrade-tcg`.

---

## TL;DR — full setup in one block

```bash
git clone <repo-url> PokeTrade-TCG
cd PokeTrade-TCG
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
herd link poketrade-tcg
herd open
```

---

## Day-to-day workflow

| Task | Command |
|---|---|
| Frontend dev with hot-reload | `npm run dev` (leave running) |
| Rebuild assets for Herd | `npm run build` |
| Run tests (Pest) | `composer test` |
| Format PHP code | `vendor/bin/pint` |
| Fresh DB + re-seed | `php artisan migrate:fresh --seed` |
| Re-import cards only | `php artisan db:seed --class=CardSeeder` |
| Tail Laravel logs | `php artisan pail` |
| Open Tinker REPL | `php artisan tinker` |

---

## Project layout (orientation)

```
app/
  Http/Controllers/   — Card, Shop, Cart, Checkout, Auction, Trade, Admin/*
  Models/             — Card, ShopItem, Cart, Order, Auction, Trade, User, Wishlist
database/
  migrations/         — schema
  seeders/CardSeeder  — pulls 180 cards from pokemontcg.io
  seeders/ShopItemSeeder
resources/
  views/              — Blade templates (pages/, components/, admin/, layouts/)
  css/, js/           — Tailwind 4 + Alpine.js entry points
routes/web.php        — all HTTP routes
```

---

## Troubleshooting

**`Vite manifest not found at: public/build/manifest.json`**
→ Run `npm run build` (or start `npm run dev` for hot-reload).

**Card images are blank**
→ Card data wasn't seeded. Run `php artisan db:seed --class=CardSeeder`. Needs internet — images are loaded from `images.pokemontcg.io`.

**Shop item images broken (404 on `/storage/...`)**
→ Symlink missing. Run `php artisan storage:link`.

**`SQLSTATE[HY000]: ... no such table`**
→ Migrations didn't run. `php artisan migrate` (or `migrate:fresh --seed` to start over).

**`http://poketrade-tcg.test` doesn't resolve**
→ Re-link: `herd link poketrade-tcg`. Confirm with `herd parked` / `herd links`.

**500 error after pulling new code**
→ Clear caches: `php artisan optimize:clear`. If new migrations were added: `php artisan migrate`. If composer/npm deps changed: `composer install && npm install && npm run build`.

---

## <a id="scheduler"></a>Keeping the scheduler running

Two scheduled commands live in [`routes/console.php`](routes/console.php):

| Command | Cadence | Purpose |
|---|---|---|
| `cards:refresh-prices` | daily (midnight) | Captures a real TCGplayer market-value snapshot per card. Without this, the price-history chart only ever shows synthetic backfill. |
| `auctions:keep-live` | every 15 min | Keeps the demo auction floor populated. |

These run automatically only if **some OS-level process calls `php artisan schedule:run` every minute**. Set this up per environment:

### Local — macOS (LaunchAgent)

A LaunchAgent calls `schedule:run` every minute. A template lives at [`deploy/macos-launchd.plist.example`](deploy/macos-launchd.plist.example). To install on a new Mac:

```bash
# 1. Copy the template into your user LaunchAgents folder
cp deploy/macos-launchd.plist.example ~/Library/LaunchAgents/com.poketrade.schedule.plist

# 2. Open it and replace the /Users/CHANGEME/... paths with your real
#    PHP binary path (`which php`) and project path. Then:
launchctl load ~/Library/LaunchAgents/com.poketrade.schedule.plist

# 3. Verify it's loaded (should print the label):
launchctl list | grep poketrade

# Output goes to storage/logs/schedule.log
```

To remove:

```bash
launchctl unload ~/Library/LaunchAgents/com.poketrade.schedule.plist
rm ~/Library/LaunchAgents/com.poketrade.schedule.plist
```

### Local — alternative: `schedule:work`

If you'd rather not install a system agent, run this in a spare terminal whenever you're developing:

```bash
php artisan schedule:work
```

Foreground process — closing the terminal stops the scheduler.

### Production — Linux VPS / DigitalOcean / Hetzner / etc.

Add one line to the deploy user's crontab (`crontab -e`):

```cron
* * * * * cd /path/to/poketrade-tcg && php artisan schedule:run >> /dev/null 2>&1
```

### Production — Laravel Forge

Forge has a built-in "Scheduler" toggle on each site — flip it on. Forge adds the cron entry for you.

### Production — Railway / Render / Fly.io / Heroku

Run a dedicated worker process / dyno with:

```bash
php artisan schedule:work
```

Some PaaS providers (Heroku) also offer a scheduler add-on you can use instead of a long-running worker.

### Production — Docker

Either run a sidecar container whose entrypoint is `php artisan schedule:work`, or add a cron entry to the same container that serves the app (less ideal — the container has to stay up).

---

## <a id="alternative-no-herd"></a>Alternative: run without Herd

If you can't or don't want to use Herd:

```bash
composer run dev
```

This runs `php artisan serve`, the queue worker, log tailer, and `npm run dev` concurrently. Site will be at `http://127.0.0.1:8000`. You'll want to set `APP_URL=http://127.0.0.1:8000` in `.env` before starting.
