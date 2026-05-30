import { test, expect } from '@playwright/test';
import { trackErrors } from './helpers';

// Static public pages plus one representative of each dynamic detail page.
const pages: [string, string][] = [
  ['home', '/'],
  ['about', '/about'],
  ['cards index', '/cards'],
  ['shop index', '/shop'],
  ['auctions index', '/auctions'],
  ['gacha index', '/gacha'],
  ['forums index', '/forums'],
  ['leaderboard', '/leaderboard'],
  ['login', '/login'],
  ['register', '/register'],
  ['forgot password', '/forgot-password'],
  ['card detail', '/cards/sve-basic-water-energy-3'],
  ['shop detail', '/shop/prismatic-evolutions-booster-box'],
  ['forum category', '/forums/c/general-discussion'],
];

for (const [name, path] of pages) {
  test(`public page renders cleanly: ${name}`, async ({ page }) => {
    const errors = trackErrors(page);
    const resp = await page.goto(path, { waitUntil: 'domcontentloaded' });

    expect(resp, `no response for ${path}`).not.toBeNull();
    expect(resp!.status(), `${path} HTTP status`).toBeLessThan(400);

    await expect(page).toHaveTitle(/\S/);
    await expect(page.locator('h1, h2').first()).toBeVisible();

    // let late assets/XHR settle, then assert no real errors
    await page.waitForTimeout(300);
    expect(errors, `errors on ${path}:\n${errors.join('\n')}`).toEqual([]);
  });
}

// Helper — pulls the first href off `selectorPage` matching `pathRegex`
// and returns it as a same-origin path. Lets us avoid hard-coded ids
// (auction, forum thread, user) that drift as seed data evolves.
async function discoverPath(
  page: import('@playwright/test').Page,
  fromPath: string,
  pathRegex: RegExp,
): Promise<string | null> {
  await page.goto(fromPath, { waitUntil: 'domcontentloaded' });
  return await page.locator('a[href]').evaluateAll((els, src) => {
    const re = new RegExp(src);
    for (const el of els) {
      const h = (el as HTMLAnchorElement).getAttribute('href') || '';
      const m = h.match(re);
      if (m) return m[0];
    }
    return null;
  }, pathRegex.source);
}

// Auction IDs drift (the keep-live command rotates auctions), so don't
// hardcode one — pick whichever auction is currently linked from the index.
test('auction detail renders cleanly (first auction from index)', async ({ page }) => {
  const errors = trackErrors(page);
  await page.goto('/auctions', { waitUntil: 'domcontentloaded' });

  // route() emits absolute URLs (http://host/auctions/N) — match by
  // substring and extract the path so the navigation is origin-relative.
  const href = await page.locator('a[href*="/auctions/"]')
    .evaluateAll((els) => {
      for (const el of els) {
        const h = (el as HTMLAnchorElement).getAttribute('href') || '';
        const m = h.match(/\/auctions\/\d+(?:\?[^"#]*)?(?:#.*)?$/);
        if (m) return m[0];
      }
      return null;
    });
  expect(href, 'no live auction link on /auctions').not.toBeNull();

  const resp = await page.goto(href!, { waitUntil: 'domcontentloaded' });
  expect(resp!.status(), `${href} HTTP status`).toBeLessThan(400);
  await expect(page).toHaveTitle(/\S/);
  await expect(page.locator('h1, h2').first()).toBeVisible();

  await page.waitForTimeout(300);
  expect(errors, `errors on ${href}:\n${errors.join('\n')}`).toEqual([]);
});

// Forum thread + public profile — derive from a category page and a
// thread page rather than hard-coding ids that may not survive a re-seed.
test('forum thread renders cleanly (first thread in general-discussion)', async ({ page }) => {
  const errors = trackErrors(page);
  const path = await discoverPath(page, '/forums/c/general-discussion', /\/forums\/t\/\d+/);
  expect(path, 'no thread link found on category page').not.toBeNull();

  const resp = await page.goto(path!, { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBeLessThan(400);
  await expect(page.locator('h1, h2').first()).toBeVisible();
  await page.waitForTimeout(300);
  expect(errors, `errors on ${path}:\n${errors.join('\n')}`).toEqual([]);
});

test('public profile renders cleanly (first author from a forum thread)', async ({ page }) => {
  const errors = trackErrors(page);

  const threadPath = await discoverPath(page, '/forums/c/general-discussion', /\/forums\/t\/\d+/);
  expect(threadPath, 'no thread link on category page').not.toBeNull();

  const profilePath = await discoverPath(page, threadPath!, /\/u\/\d+/);
  expect(profilePath, 'no /u/N profile link on thread page').not.toBeNull();

  const resp = await page.goto(profilePath!, { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBeLessThan(400);
  await expect(page.locator('h1, h2').first()).toBeVisible();
  await page.waitForTimeout(300);
  expect(errors, `errors on ${profilePath}:\n${errors.join('\n')}`).toEqual([]);
});

test('cards search returns results', async ({ page }) => {
  await page.goto('/cards?q=eevee', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1')).toContainText(/price/i);
});

test('login: rejects bad credentials and stays on /login', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  // Use a fresh non-existent email so we don't share a throttle bucket
  // with the real e2e@poketrade.test user across re-runs.
  await page.fill('input[name="email"]', `nope-${Date.now()}@example.test`);
  await page.fill('input[name="password"]', 'definitely-wrong');

  // Click + wait for the validation-error redirect to settle before
  // asserting the body — WebKit can lag on the round-trip.
  await Promise.all([
    page.waitForURL(/\/login/, { waitUntil: 'domcontentloaded' }),
    page.click('button[type="submit"]'),
  ]);

  await expect(page.locator('body')).toContainText(
    /credentials|do not match|incorrect|invalid/i,
  );
});

test('404 page does not 500', async ({ page }) => {
  const resp = await page.goto('/this-route-does-not-exist-zzz', { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBe(404);
});
