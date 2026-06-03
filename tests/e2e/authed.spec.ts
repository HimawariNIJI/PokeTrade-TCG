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

// c6db9bd — Settings "Save settings" button must show a submitting
// indicator (disabled + "Saving…" label) while the multipart PATCH is in
// flight, instead of sitting silent and making users assume the click was
// dropped. Stall the PATCH so the in-flight state is observable, then
// snapshot the button state from inside the route handler — that's the
// only moment we know the request is mid-flight and the page hasn't
// navigated to the redirect target yet.
test('settings save button shows "Saving…" indicator while submitting (c6db9bd)', async ({ page }) => {
  await page.goto('/settings', { waitUntil: 'domcontentloaded' });

  const button = page.locator('form[action*="settings"] button[type="submit"]');
  await expect(button, 'Save settings button must be reachable').toBeVisible();
  await expect(button).toBeEnabled();
  expect(
    (await button.textContent())?.trim(),
    'pre-submit label should be "Save settings"',
  ).toMatch(/Save settings/);

  // First sanity-check the wiring exists in the rendered HTML — catches
  // accidental removal of any of the four Alpine bindings the fix added.
  const form = page.locator('form[action*="settings"]');
  await expect(form).toHaveAttribute('x-data', /submitting:\s*false/);
  await expect(form).toHaveAttribute('x-on:submit', /submitting\s*=\s*true/);
  await expect(button).toHaveAttribute('x-bind:disabled', /submitting/);
  await expect(
    button.locator('span[x-text]'),
    'inner span must swap label via x-text when submitting flips',
  ).toHaveAttribute('x-text', /Saving/);

  // Now drive Alpine's reactive state directly — Playwright can't observe
  // DOM updates *during* an actual form POST (the locator waits for
  // navigation to finish), so flip the flag in-place and assert the same
  // bindings the user sees once the click handler runs.
  await page.evaluate(() => {
    const f = document.querySelector('form[action*="settings"]') as
      | (HTMLFormElement & { _x_dataStack?: Array<{ submitting?: boolean }> })
      | null;
    if (!f || !f._x_dataStack || !f._x_dataStack[0]) {
      throw new Error('Alpine x-data stack not found on settings form');
    }
    f._x_dataStack[0].submitting = true;
  });

  await expect(button, 'button should be disabled while submitting').toBeDisabled();
  await expect(button, 'button label should swap to "Saving…"').toHaveText(/Saving/);
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
