import { test, expect } from '@playwright/test';

// Runs under the "mobile" project (Pixel 5). Guards against the most
// common mobile regressions: horizontal overflow and a broken menu.
const pages: [string, string][] = [
  ['home', '/'],
  ['cards', '/cards'],
  ['shop', '/shop'],
  ['gacha', '/gacha'],
  ['auctions', '/auctions'],
  ['forums', '/forums'],
  ['leaderboard', '/leaderboard'],
  ['about', '/about'],
];

for (const [name, path] of pages) {
  test(`mobile: no horizontal overflow on ${name}`, async ({ page }) => {
    await page.goto(path, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);

    const { scrollW, clientW } = await page.evaluate(() => ({
      scrollW: document.documentElement.scrollWidth,
      clientW: document.documentElement.clientWidth,
    }));

    // allow 2px slack for sub-pixel rounding
    expect(scrollW, `${path} overflows horizontally (scroll ${scrollW} > client ${clientW})`)
      .toBeLessThanOrEqual(clientW + 2);
  });
}

test('mobile: nav menu toggle opens links', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  const toggle = page.locator('header button').filter({ has: page.locator('svg') }).last();
  await toggle.click();
  await expect(page.getByRole('link', { name: 'Cards' }).last()).toBeVisible();
});
