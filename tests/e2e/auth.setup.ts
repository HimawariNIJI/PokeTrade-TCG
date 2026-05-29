import { test as setup, expect } from '@playwright/test';

const AUTH_FILE = '.auth/user.json';

// Logs in the dedicated E2E trainer (created via tinker, 500 points)
// and persists the session for the authed project.
setup('authenticate', async ({ page }) => {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', 'e2e@poketrade.test');
  await page.fill('input[name="password"]', 'password123');
  await page.click('button[type="submit"]');

  // Login redirects to the app. Poll the URL (don't wait for the full
  // `load` event, which stalls on slow external card images).
  await expect(page).not.toHaveURL(/\/login(\?|$)/, { timeout: 15000 });
  await expect(page.locator('body')).not.toContainText('These credentials do not match');

  await page.context().storageState({ path: AUTH_FILE });
});
