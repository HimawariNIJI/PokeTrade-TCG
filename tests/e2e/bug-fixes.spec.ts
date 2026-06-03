import { test, expect } from '@playwright/test';

// The chromium project has no storageState, so the default `page` fixture
// gives each test an isolated, signed-out context that Playwright tears
// down automatically. Don't `browser.newContext()` — closing a context
// with pending external-image requests can hang past the test timeout.

// Regression coverage for the three QA bug-fix commits:
//   caf270b — card price "Rp 0" + Remember Me persistence
//   c6db9bd — forum avatar accessor + settings submitting indicator
//   046f311 — back/forward bypasses OTP gate + admin refund 405
//
// Forum avatar (c6db9bd) and the settings submitting indicator are
// covered separately — forum avatar by the PHP Feature test added in
// the same commit (tests/Feature/ForumFeaturesTest.php), and the
// settings indicator by an authed Playwright test in authed.spec.ts.

// ---------------------------------------------------------------------------
// caf270b — Cards listing must never display "Rp 0" as the market price.
// The bug: `decimal:2` cast returns the string "0.00" which PHP treats as
// truthy, so the `?:` fallback to display_price never fired and 572 cards
// rendered as Rp 0. Fix casts to float before the fallback.
// ---------------------------------------------------------------------------
test('cards listing does not display Rp 0 market prices (caf270b)', async ({ page }) => {
  await page.goto('/cards', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1, h2').first()).toBeVisible();

  // Exact-match "Rp 0" — won't match "Rp 1.000", "Rp 10", etc.
  const zeroPrices = page.getByText(/^Rp\s+0$/);
  await expect(
    zeroPrices,
    'no card tile should show Rp 0 as a market price',
  ).toHaveCount(0);
});

// ---------------------------------------------------------------------------
// caf270b — Remember Me must actually persist the login session.
// With SESSION_EXPIRE_ON_CLOSE=true, the session cookie is a session cookie
// (no Max-Age/Expires → expires === -1 in Playwright's cookie API). When the
// user ticks "Remember me", Laravel sets a long-lived `remember_web_*`
// cookie that survives browser close. Without the tick, no remember cookie.
// ---------------------------------------------------------------------------
test('login without "remember me" leaves only a session cookie (caf270b)', async ({ page, context }) => {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', 'e2e@poketrade.test');
  await page.fill('input[name="password"]', 'password123');
  // intentionally do NOT check the remember checkbox
  await page.click('button[type="submit"]');
  await expect(page).not.toHaveURL(/\/login(\?|$)/, { timeout: 15000 });

  const cookies = await context.cookies();
  const remember = cookies.find((c) => c.name.startsWith('remember_web_'));
  expect(remember, 'no remember_web_* cookie should be set when "remember" is unchecked').toBeUndefined();

  // Laravel's session cookie name defaults to "<APP_NAME slug>-session"
  // (Str::slug joins with dashes). Match either separator so the test
  // survives an explicit SESSION_COOKIE override too.
  const session = cookies.find((c) => /[-_]session$/.test(c.name));
  expect(session, 'session cookie should be present after login').toBeDefined();
  expect(
    session!.expires,
    'session cookie must be a session cookie (expires=-1) so it dies on browser close',
  ).toBe(-1);
});

test('login with "remember me" sets a long-lived remember_web_* cookie (caf270b)', async ({ page, context }) => {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', 'e2e@poketrade.test');
  await page.fill('input[name="password"]', 'password123');
  await page.check('input[name="remember"]');
  await page.click('button[type="submit"]');
  await expect(page).not.toHaveURL(/\/login(\?|$)/, { timeout: 15000 });

  const cookies = await context.cookies();
  const remember = cookies.find((c) => c.name.startsWith('remember_web_'));
  expect(remember, 'remember_web_* cookie should be set when "remember" is checked').toBeDefined();
  const nowSeconds = Date.now() / 1000;
  // Laravel sets the remember cookie to ~5 years. Assert at least a week
  // out, which excludes session cookies (-1) and short-lived ones.
  expect(
    remember!.expires,
    `remember cookie should outlive the session (expires=${remember!.expires})`,
  ).toBeGreaterThan(nowSeconds + 7 * 24 * 3600);
});

// ---------------------------------------------------------------------------
// 046f311 — Pressing back from the OTP verify page must not let an
// unverified, freshly-registered user roam the site. The `verified`
// middleware now gates customer + admin route groups, so any authed-but-
// unverified hit to a protected route is funneled back to the OTP form.
// ---------------------------------------------------------------------------
test('unverified user is redirected to /verify-email when navigating after back (046f311)', async ({ page }) => {
  // Register flow does mailable + OTP token writes, so give it headroom.
  test.setTimeout(60_000);
  // Unique email per run so re-running doesn't trip the unique-email rule.
  const email = `otp-bypass-${Date.now()}@e2e.test`;

  await page.goto('/register', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="name"]', 'OTP Bypass Probe');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', 'password123');
  await page.fill('input[name="password_confirmation"]', 'password123');
  await page.click('button[type="submit"]');

  // After registration the controller Auth::login's the new user and
  // redirects to the OTP form. Wait for the URL change rather than the
  // full load event, which can stall on slow shared assets.
  await expect(page).toHaveURL(/\/verify-email/, { timeout: 20_000 });

  // Simulate the user pressing back and then trying to reach a protected
  // page (the bug let them browse /cards etc. while still unverified).
  // /cart is `auth,verified`-gated and exists for any signed-in user, so
  // it's a clean probe target.
  const resp = await page.goto('/cart', { waitUntil: 'domcontentloaded' });
  expect(resp!.status(), 'protected route should not 5xx').toBeLessThan(500);
  await expect(
    page,
    'verified middleware should bounce unverified user back to the OTP form',
  ).toHaveURL(/\/verify-email/);
});

// ---------------------------------------------------------------------------
// 046f311 — Admin refund endpoint must accept GET (the confirmation page)
// without 405 Method Not Allowed. Before the fix the route was PATCH-only;
// pressing browser back after a successful refund replayed the GET request
// and hit "GET method is not supported for route admin/auctions/{id}/refund".
// ---------------------------------------------------------------------------
test('admin refund route serves GET without 405 (046f311)', async ({ page }) => {
  // Login as admin via the form (no stored storageState for admin).
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', 'admin@poketrade.test');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page).not.toHaveURL(/\/login(\?|$)/, { timeout: 15000 });

  // Discover an ended, unrefunded auction by scraping the admin index for
  // a "Refund" link — avoids hard-coding an id that drifts seed-to-seed.
  await page.goto('/admin/auctions', { waitUntil: 'domcontentloaded' });
  const refundHref = await page.locator('a[href*="/admin/auctions/"][href$="/refund"]')
    .first()
    .getAttribute('href');
  test.skip(!refundHref, 'no ended-unrefunded auction in current seed — nothing to probe');

  // The fix made the refund URL an idempotent GET (renders the confirm
  // page). page.request shares the page's cookies, so the admin session
  // is reused without extracting/replanting storageState. A 405 here is
  // the original bug; a <400 means the GET route is in place.
  const refundResp = await page.request.get(refundHref!);
  expect(
    refundResp.status(),
    `GET ${refundHref} should render the confirmation page, not 405`,
  ).toBeLessThan(400);
});
