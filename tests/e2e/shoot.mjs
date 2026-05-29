// Ad-hoc visual capture: screenshots key pages at desktop + mobile widths.
// Usage: node tests/e2e/shoot.mjs [baseURL]
import { chromium, devices } from '@playwright/test';
import { mkdirSync } from 'node:fs';

const base = process.argv[2] || 'http://127.0.0.1:8123';
const outDir = '/tmp/poke-shots';
mkdirSync(outDir, { recursive: true });

const pages = [
  ['home', '/'],
  ['cards', '/cards'],
  ['shop', '/shop'],
  ['auctions', '/auctions'],
  ['gacha', '/gacha'],
  ['forums', '/forums'],
  ['leaderboard', '/leaderboard'],
  ['about', '/about'],
  ['login', '/login'],
];

const browser = await chromium.launch({ channel: 'chrome' });

async function shoot(label, viewport, isMobile) {
  const ctx = await browser.newContext(
    isMobile ? devices['iPhone 13'] : { viewport }
  );
  const page = await ctx.newPage();
  const errors = [];
  page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
  for (const [name, path] of pages) {
    try {
      await page.goto(base + path, { waitUntil: 'networkidle', timeout: 20000 });
      await page.waitForTimeout(400);
      await page.screenshot({ path: `${outDir}/${name}-${label}.png`, fullPage: label === 'desktop' });
    } catch (e) {
      console.log(`FAIL ${name} (${label}): ${e.message}`);
    }
  }
  if (errors.length) console.log(`[${label}] console errors:\n` + errors.join('\n'));
  await ctx.close();
}

await shoot('desktop', { width: 1366, height: 900 }, false);
await shoot('mobile', null, true);
await browser.close();
console.log('shots written to ' + outDir);
