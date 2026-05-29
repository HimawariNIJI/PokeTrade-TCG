import { Page } from '@playwright/test';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8123';
const ORIGIN = new URL(BASE).host;

/**
 * Collects real problems on a page: uncaught JS exceptions and
 * same-origin responses that fail (>=400). External font/CDN requests
 * are intentionally ignored so the offline sandbox doesn't create noise.
 */
export function trackErrors(page: Page): string[] {
  const errors: string[] = [];

  page.on('pageerror', (e) => errors.push('JS exception: ' + e.message));

  page.on('response', (r) => {
    const url = r.url();
    let host = '';
    try { host = new URL(url).host; } catch { /* data: urls etc */ }
    if (host === ORIGIN && r.status() >= 400) {
      errors.push(`${r.status()} ${url}`);
    }
  });

  return errors;
}
