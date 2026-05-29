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
  ['auction detail', '/auctions/1'],
  ['forum category', '/forums/c/general-discussion'],
  ['forum thread', '/forums/t/1'],
  ['public profile', '/u/11'],
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

test('cards search returns results', async ({ page }) => {
  await page.goto('/cards?q=eevee', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('h1')).toContainText(/price/i);
});

test('404 page does not 500', async ({ page }) => {
  const resp = await page.goto('/this-route-does-not-exist-zzz', { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBe(404);
});
