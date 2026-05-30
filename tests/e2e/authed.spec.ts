import { test, expect } from '@playwright/test';
import { spawnSync } from 'node:child_process';
import { trackErrors } from './helpers';

// These run under the "authed" project with a stored logged-in session.

// Resets persistent state for the e2e@poketrade.test user so the
// stateful workflows below are deterministic re-run to re-run:
//   - clears the daily free-pull timestamp (so the gacha button label
//     and gacha cost are predictable)
//   - restores 500 points (the seed default)
//   - empties the wishlists pivot
function resetE2EUserState() {
  const php = `$u = App\\Models\\User::where('email','e2e@poketrade.test')->first(); if($u){ $u->forceFill(['last_free_gacha_at'=>null,'points'=>500])->save(); \\DB::table('wishlists')->where('user_id', $u->id)->delete(); }`;
  const r = spawnSync('php', ['artisan', 'tinker', '--execute=' + php], {
    cwd: process.cwd(),
    encoding: 'utf8',
  });
  if (r.status !== 0) {
    throw new Error(`E2E reset failed (exit ${r.status}):\n${r.stderr}\n${r.stdout}`);
  }
}

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
  resetE2EUserState();
  await page.goto('/gacha', { waitUntil: 'domcontentloaded' });
  // Open the confirm modal, then confirm the pull. The button label
  // flips between "Pull free pack" (daily free pull available) and
  // "Pull a pack (N Points)" (paid), so accept either.
  await page.getByRole('button', { name: /Pull (free pack|a pack)/i }).first().click();
  await page.getByRole('button', { name: /^Confirm$/ }).click();

  await expect(page.getByText(/Pack pulled/i)).toBeVisible({ timeout: 10000 });
  // Skip the reveal animation to expose card labels + ownership badges.
  await page.getByRole('button', { name: /Skip animation/i }).click().catch(() => {});
  await expect(page.getByText(/held/i).first()).toBeVisible({ timeout: 10000 });
});

test('workflow: wishlist toggle persists', async ({ page }) => {
  // Start from a known-empty wishlist so the toggle direction is "add".
  resetE2EUserState();
  const cardSlug = 'sve-basic-water-energy-3';

  await page.goto(`/cards/${cardSlug}`, { waitUntil: 'domcontentloaded' });
  const toggle = page.locator(`form[action*="/wishlist/${cardSlug}"] button`).first();
  await expect(toggle, 'wishlist toggle button must be present on card detail').toBeVisible();
  await toggle.click();
  await page.waitForLoadState('domcontentloaded');

  const resp = await page.goto('/wishlist', { waitUntil: 'domcontentloaded' });
  expect(resp!.status()).toBeLessThan(400);

  // After adding the chase card, the wishlist page should link to it.
  await expect(
    page.locator(`a[href*="/cards/${cardSlug}"]`).first(),
    'wishlisted card should be linked from /wishlist'
  ).toBeVisible();
});
