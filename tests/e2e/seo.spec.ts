import { test, expect } from '@playwright/test';

test('home has complete SEO + social meta', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });

  await expect(page).toHaveTitle(/PokeTrade/);
  await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', /.{40,}/);
  await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
  await expect(page.locator('meta[property="og:title"]')).toHaveAttribute('content', /PokeTrade/);
  await expect(page.locator('meta[property="og:image"]')).toHaveAttribute('content', /og-default\.png/);
  await expect(page.locator('meta[name="twitter:card"]')).toHaveAttribute('content', 'summary_large_image');

  // structured data present and valid JSON
  const ld = await page.locator('script[type="application/ld+json"]').first().textContent();
  expect(ld).toBeTruthy();
  const json = JSON.parse(ld!);
  expect(JSON.stringify(json)).toContain('WebSite');
});

test('per-page titles differ from home', async ({ page }) => {
  await page.goto('/cards', { waitUntil: 'domcontentloaded' });
  await expect(page).toHaveTitle(/Price Tracker/);
  await page.goto('/gacha', { waitUntil: 'domcontentloaded' });
  await expect(page).toHaveTitle(/Gacha/);
});

test('og image asset exists', async ({ request }) => {
  const r = await request.get('/images/og-default.png');
  expect(r.status()).toBe(200);
  expect(r.headers()['content-type']).toContain('image');
});

test('sitemap.xml is valid and lists core pages', async ({ request }) => {
  const r = await request.get('/sitemap.xml');
  expect(r.status()).toBe(200);
  expect(r.headers()['content-type']).toContain('xml');
  const body = await r.text();
  expect(body).toContain('<urlset');
  expect(body).toContain('/cards');
  expect(body).toContain('/shop');
  expect(body).toContain('/auctions');
});

test('robots.txt references the sitemap', async ({ request }) => {
  const r = await request.get('/robots.txt');
  expect(r.status()).toBe(200);
  expect(await r.text()).toContain('sitemap.xml');
});

test('favicon svg is served', async ({ request }) => {
  const r = await request.get('/favicon.svg');
  expect(r.status()).toBe(200);
  expect(r.headers()['content-type']).toContain('svg');
});
