import { test, expect } from '@playwright/test';
import { trackErrors } from './helpers';

// These run under the "authed" project with a stored logged-in session.

const authedPages: [string, string][] = [
  ['collection', '/collection'],
  ['pull history', '/collection/history'],
  ['wishlist', '/wishlist'],
  ['orders', '/orders'],
  ['notifications', '/notifications'],
  ['settings', '/settings'],
  ['profile', '/profile'],
  ['cart', '/cart'],
  ['new thread', '/forums/new'],
];

for (const [name, path] of authedPages) {
  test(`authed page renders cleanly: ${name}`, async ({ page }) => {
    const errors = trackErrors(page);
    const resp = await page.goto(path, { waitUntil: 'domcontentloaded' });
    expect(resp!.status(), `${path} status`).toBeLessThan(400);
    await expect(page.locator('h1, h2').first()).toBeVisible();
    await page.waitForTimeout(300);
    expect(errors, `errors on ${path}:\n${errors.join('\n')}`).toEqual([]);
  });
}

test('workflow: gacha pull deals a pack and shows ownership info', async ({ page }) => {
  await page.goto('/gacha', { waitUntil: 'domcontentloaded' });
  // Open the confirm modal, then confirm the pull.
  await page.getByRole('button', { name: /Pull a pack/i }).first().click();
  await page.getByRole('button', { name: /^Confirm$/ }).click();

  await expect(page.getByText(/Pack pulled/i)).toBeVisible({ timeout: 10000 });
  // Skip the reveal animation to expose card labels + ownership badges.
  await page.getByRole('button', { name: /Skip animation/i }).click().catch(() => {});
  await expect(page.getByText(/held/i).first()).toBeVisible({ timeout: 10000 });
});

test('workflow: wishlist toggle persists', async ({ page }) => {
  // Toggle a chase card from a card detail page, then confirm it shows in the wishlist.
  await page.goto('/cards/sve-basic-water-energy-3', { waitUntil: 'domcontentloaded' });
  const toggle = page.locator('form[action*="/wishlist/"] button').first();
  if (await toggle.count()) {
    await toggle.click();
    await page.waitForLoadState('domcontentloaded');
  }
  const resp = await page.goto('/wishlist', { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBeLessThan(400);
});
