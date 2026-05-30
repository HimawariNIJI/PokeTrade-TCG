# PokeTrade-TCG — End-to-End Test Report

**Date:** 2026-05-30
**Suite:** Playwright (`@playwright/test` 1.60.0)
**Command:** `npx playwright test`
**Result:** **50 / 52 passed** (2 failed, after 1 retry each) in ~1.5 min on 3 workers.

The two failures are **test-side brittleness**, not application bugs. The app itself rendered, authenticated, and served every page correctly. Both failures have a clear, low-risk fix below.

---

## 1. How the suite was run

Environment used:

- Laravel + Blade + Alpine.js + Tailwind v4 on PHP 8.4.19, Node 24.13.0, npm 11.11.0.
- SQLite database at [database/database.sqlite](database/database.sqlite) (seeded).
- E2E login user `e2e@poketrade.test` / `password123` already present in the DB (id 11, 430 pts) — created previously via `tinker`, **not** by any seeder. See §6.
- Playwright browsers cached locally (chromium-1223, webkit-2287, firefox-1522 — only chromium is exercised by the current config).

Playwright config — [playwright.config.ts](playwright.config.ts):

- `webServer` rebuilds assets and starts `php artisan serve --port=8123` with `PHP_CLI_SERVER_WORKERS=8`, then reuses an existing server if one is up.
- 4 projects: `setup` → `chromium` (public/a11y/SEO) → `mobile` (Pixel 5) → `authed` (depends on `setup`).
- `retries: 1`, `workers: 3`, `trace: 'retain-on-failure'`, `screenshot: 'only-on-failure'`.

No installation was needed — `node_modules` and Playwright browsers were already in place.

---

## 2. What is covered today

| Spec | Project | Tests | Notes |
|---|---|---|---|
| [tests/e2e/auth.setup.ts](tests/e2e/auth.setup.ts) | setup | 1 | Logs in the E2E trainer, persists `.auth/user.json`. |
| [tests/e2e/public.spec.ts](tests/e2e/public.spec.ts) | chromium | 19 | 17 page smoke tests (status + title + h1/h2 + zero JS errors / 4xx-5xx same-origin responses) + cards search + 404. |
| [tests/e2e/a11y.spec.ts](tests/e2e/a11y.spec.ts) | chromium | 6 | axe-core WCAG 2 A/AA — fails on serious/critical violations only. |
| [tests/e2e/seo.spec.ts](tests/e2e/seo.spec.ts) | chromium | 6 | Title, meta, OG/Twitter, JSON-LD, sitemap.xml, robots.txt, favicon. |
| [tests/e2e/responsive.spec.ts](tests/e2e/responsive.spec.ts) | mobile | 9 | Pixel 5 — no horizontal overflow on 8 pages + mobile nav toggle. |
| [tests/e2e/authed.spec.ts](tests/e2e/authed.spec.ts) | authed | 11 | 9 authed-page smoke tests + gacha-pull workflow + wishlist toggle. |

Total: **52 tests** across 4 projects.

---

## 3. Failures — root cause and exact fix

### 3.1 `public page renders cleanly: auction detail` — `/auctions/1` returns 404

[tests/e2e/public.spec.ts:21](tests/e2e/public.spec.ts#L21)

```
Expected: < 400
Received:   404
```

**Root cause.** The page list hard-codes `/auctions/1`:

```ts
['auction detail', '/auctions/1'],
```

The recent `feat(auctions): self-healing keep-live command` (commit `5fae133`) keeps the demo floor full by rotating auctions — so the lowest live auction ID is now **7**, not 1. The route is `auctions/{auction}` (implicit model binding by ID), so `/auctions/1` resolves to a `ModelNotFoundException` → 404. Verified:

```
count=6 — IDs 7-12 (4 live, 1 scheduled, 1 ended)
```

**Why it ever passed.** It only passes while no keep-live cycle has rotated past id=1, which is no longer the case.

**Fix.** Don't hard-code a fixture ID — discover one from the index page. Replace the `auction detail` entry in the static list with a dedicated test:

```ts
// Remove this from the `pages` list:
// ['auction detail', '/auctions/1'],

test('auction detail renders cleanly (first live auction)', async ({ page }) => {
  const errors = trackErrors(page);
  await page.goto('/auctions', { waitUntil: 'domcontentloaded' });
  const firstAuction = page.locator('a[href^="/auctions/"]')
    .filter({ hasNot: page.locator('a[href$="/auctions"]') })
    .first();
  const href = await firstAuction.getAttribute('href');
  expect(href, 'no live auction link on /auctions').toMatch(/^\/auctions\/\d+/);

  const resp = await page.goto(href!, { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBeLessThan(400);
  await expect(page.locator('h1, h2').first()).toBeVisible();
  await page.waitForTimeout(300);
  expect(errors, `errors on ${href}:\n${errors.join('\n')}`).toEqual([]);
});
```

### 3.2 `workflow: gacha pull deals a pack…` — "Pull a pack" button not found

[tests/e2e/authed.spec.ts:32](tests/e2e/authed.spec.ts#L32)

```
locator.click: Test timeout of 30000ms exceeded.
  - waiting for getByRole('button', { name: /Pull a pack/i }).first()
```

**Root cause.** The `/gacha` button has two labels depending on `freeGachaAvailable()` — see [resources/views/pages/gacha/index.blade.php:80](resources/views/pages/gacha/index.blade.php#L80):

```blade
{{ $freePullAvailable ? 'Pull free pack' : 'Pull a pack (' . $pullCost . ' Points)' }}
```

When the daily free pull is available (the common case — first run of the day), the label is **"Pull free pack"**, and the regex `/Pull a pack/i` never matches. The test only succeeds on the second+ run of the same day (after a free pull was consumed).

**Fix.** Accept both labels:

```ts
await page.getByRole('button', { name: /Pull (free pack|a pack)/i }).first().click();
```

This is the minimal change. A more robust version (and the recommended one) also resets state up front so the test is deterministic — see §4.

---

## 4. Flaky / state-dependent tests

Two tests mutate persistent DB state and depend on prior state, which creates self-inflicted flakes across re-runs on the same day:

- **`workflow: gacha pull`** ([authed.spec.ts:29](tests/e2e/authed.spec.ts#L29)) — consumes the user's daily free pull (sets `users.last_free_gacha_at = today`). The next same-day run sees a different button label and different point balance. Combined with the bug in §3.2, this is a real source of "passes on Tuesday, fails on Wednesday" flakes.

- **`workflow: wishlist toggle persists`** ([authed.spec.ts:41](tests/e2e/authed.spec.ts#L41)) — toggles the chase-card flag without ever cleaning up; over many runs the assertion drifts (every other run toggles it off).

**Recommendation.** Add a tiny per-test reset, either:

- A short fixture that runs `User::where('email','e2e@poketrade.test')->update(['last_free_gacha_at'=>null,'points'=>500])` and clears the user's wishlist before mutating tests, or
- A request-mode test (`page.request.post('/__test/reset')`) hitting a dev-only reset route guarded by `App::environment('local','testing')`.

Either keeps the auth project deterministic without slowing down the read-only specs.

---

## 5. Brittle fixtures

Beyond the two failures above, there are other hard-coded IDs that will rot the same way:

| Reference | Risk | Suggested fix |
|---|---|---|
| `/forums/t/1` in [public.spec.ts:21](tests/e2e/public.spec.ts#L21) | thread id 1 may not survive a re-seed | grab the first link off `/forums/c/general-discussion` |
| `/u/11` in [public.spec.ts:22](tests/e2e/public.spec.ts#L22) | depends on E2E user being id=11 | use `users.public_id` slug or look up the link from the leaderboard / a forum thread |
| `/cards/sve-basic-water-energy-3` in [public.spec.ts:19](tests/e2e/public.spec.ts#L19), [authed.spec.ts:43](tests/e2e/authed.spec.ts#L43) | tightly couples to CardSeeder data | acceptable for now (slug is stable), but worth pinning a fixture constant in `helpers.ts` so a future rename is one-line. |

---

## 6. CI is currently non-functional

[.github/workflows/playwright.yml](.github/workflows/playwright.yml) was added as the default Playwright scaffold and will fail on any PR.

- **No PHP / Composer / Laravel setup step.** The workflow installs Node + Playwright then runs `npx playwright test`, which triggers the `webServer` command `npm run build && php artisan serve…`. On a clean runner there's no PHP, no `vendor/`, no `.env`, no SQLite file, and no migrations have run.
- **No `e2e@poketrade.test` user.** The user is only created manually via `tinker` — no seeder touches it. Even if the DB were migrated and seeded, the `setup` project would fail at login.
- The PHP suite is also not run in CI.

**Recommendation — workflow rewrite.** Add the missing steps:

```yaml
- uses: shivammathur/setup-php@v2
  with: { php-version: '8.4' }
- name: Composer install
  run: composer install --no-interaction --prefer-dist --optimize-autoloader
- name: App key + sqlite
  run: |
    cp .env.example .env
    php artisan key:generate
    mkdir -p database && touch database/database.sqlite
- name: Migrate + seed
  run: php artisan migrate --seed --force
- name: PHP tests
  run: php artisan test --testsuite=Feature   # see §9 for the known OTP-related skip
```

**Recommendation — seed the E2E user** so the `setup` project is reproducible. In [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) add (next to the existing admin/trainer blocks):

```php
if (app()->environment(['local', 'testing'])) {
    User::firstOrCreate(
        ['email' => 'e2e@poketrade.test'],
        [
            'name' => 'E2E Trainer',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
            'email_verified_at' => now(),
            'points' => 500,
        ]
    );
}
```

(Or switch the auth setup to use the already-seeded `trainer@poketrade.test` / `password` — quicker but loses the "isolated E2E account" property.)

---

## 7. Leftover scaffold to clean up

The recent "add Playwright E2E testing setup" commit (`60e44d7`) created scaffold files alongside the real suite:

- [e2e/example.spec.ts](e2e/example.spec.ts) — the default `npm init playwright` example that navigates to **playwright.dev** (external internet, not even this project). It is currently **not executed** because `playwright.config.ts` sets `testDir: './tests/e2e'`, but it's confusing dead code. Delete `e2e/`.
- [.github/workflows/playwright.yml](.github/workflows/playwright.yml) — see §6, replace rather than delete.

---

## 8. Coverage gaps worth filling

The existing suite is strong on smoke + a11y + SEO. Things it does not yet cover:

1. **Cross-browser.** Firefox and WebKit are downloaded but not exercised. Add a fast `cross-browser` project that runs only the public-page smoke + `a11y.spec.ts` on Firefox and WebKit. Skip the authed flow there to keep wall time down.
2. **Auth flows.** No assertion on login failure, no `register` happy path, no OTP password-reset flow (the project has a custom `OtpPasswordResetController`). At minimum: bad-password rejection, register-then-redirect, OTP request shows the rate-limit copy.
3. **Cart → checkout.** `/cart` is hit as a smoke page but the add-to-cart → checkout → order flow is not. Worth one spec for the merch store since "points earned from shop drive gacha" is the core loop.
4. **Auction bidding.** Place a bid via the form, assert points debited and high-bid badge appears. The bid endpoint is `POST auctions/{auction}/bid`.
5. **Forum post.** Create a thread and a reply (`/forums/new` is hit but not submitted).
6. **Settings toggles persist.** Profile-visibility toggles in `/settings` round-trip (memory `concept-pivot` calls these out as real backend, not stub).
7. **Negative SEO checks.** `meta[name="description"]` is checked on `/`, but not on `/cards`, `/gacha`, `/auctions` — easy to extend the loop.
8. **CSRF / 419 regression.** A test that submits a form after deliberately stripping the CSRF token to assert the 419 page renders cleanly (no JS exception).

---

## 9. Adjacent suites (context only)

- **PHP feature suite.** `php artisan test` currently has a known 3-test failure in `tests/Feature/Auth/PasswordResetTest.php` because Breeze's default `password.request/email/store/reset` routes were replaced by the project's custom OTP flow (`otp.*`). This is documented in project memory and is **not** a regression to fix — either delete the obsolete tests or rewrite them against `OtpPasswordResetController`.

---

## 10. Prioritized action list

**P0 — Make the suite green again** (≤ 30 min):

1. Fix the auction-detail test as in §3.1.
2. Widen the gacha button regex as in §3.2.

**P1 — Make it reproducible on CI / clean machines** (≤ 1–2 h):

3. Seed `e2e@poketrade.test` under `local|testing` (§6).
4. Rewrite [.github/workflows/playwright.yml](.github/workflows/playwright.yml) to install PHP, composer install, key:generate, create sqlite, migrate --seed before `playwright test` (§6).
5. Delete [e2e/example.spec.ts](e2e/example.spec.ts) and the empty `e2e/` directory (§7).

**P2 — De-flake** (≤ 1 h):

6. Replace remaining hard-coded fixture IDs (`/forums/t/1`, `/u/11`) with index-page-derived links (§5).
7. Add per-test reset of `last_free_gacha_at` and the wishlist row for the E2E user (§4).

**P3 — Broaden coverage** (incremental):

8. Cross-browser project for smoke + a11y on Firefox/WebKit (§8.1).
9. One spec each for: login-failure, register, OTP password-reset, cart → checkout, auction bidding, forum post (§8.2–8.5).
10. Settings persistence and CSRF/419 regression (§8.6, §8.8).

---

## Appendix A — Raw failure excerpts

```
✘ [chromium] › public.spec.ts:26:3 › public page renders cleanly: auction detail (516ms)
    Error: /auctions/1 HTTP status
    Expected: < 400
    Received:   404
        at tests/e2e/public.spec.ts:31:51

✘ [authed]   › authed.spec.ts:29:1   › workflow: gacha pull deals a pack and shows ownership info
    Test timeout of 30000ms exceeded.
    Error: locator.click: ...
      - waiting for getByRole('button', { name: /Pull a pack/i }).first()
        at tests/e2e/authed.spec.ts:32:68
```

## Appendix B — HTML report

Playwright wrote a full HTML report to `playwright-report/` (gitignored). Open with:

```sh
npx playwright show-report
```

Traces, screenshots, and `error-context.md` for both failures live under `test-results/`.
