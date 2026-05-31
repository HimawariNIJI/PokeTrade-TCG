// Mobile full-page screenshots for the mobile-simplification pass.
// Forces every [data-reveal] element to is-visible before capture, since
// Playwright fullPage capture doesn't reliably trigger IntersectionObserver
// for content below the initial viewport, leaving reveal-styled elements
// stuck at opacity:0.
import { chromium, devices } from '@playwright/test';
import { mkdirSync } from 'node:fs';

const base = 'http://127.0.0.1:8123';
const outDir = '/tmp/poke-mobile';
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
];

const browser = await chromium.launch({ channel: 'chrome' });
const ctx = await browser.newContext(devices['iPhone 13']);
const page = await ctx.newPage();
const errors = [];
page.on('console', (m) => {
    if (m.type() === 'error') errors.push(`${m.location()?.url || ''} -> ${m.text()}`);
});

for (const [name, path] of pages) {
    try {
        await page.goto(base + path, { waitUntil: 'networkidle', timeout: 25000 });
        await page.evaluate(async () => {
            await new Promise((r) => setTimeout(r, 200));
            const step = Math.max(1, Math.floor(window.innerHeight * 0.8));
            for (let y = 0; y < document.body.scrollHeight; y += step) {
                window.scrollTo(0, y);
                await new Promise((r) => setTimeout(r, 60));
            }
            window.scrollTo(0, 0);
            document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
            await new Promise((r) => setTimeout(r, 200));
        });
        await page.waitForTimeout(300);
        await page.screenshot({ path: `${outDir}/${name}.png`, fullPage: true });
        console.log(`OK ${name}`);
    } catch (e) {
        console.log(`FAIL ${name}: ${e.message}`);
    }
}

if (errors.length) console.log(`\nConsole errors:\n${errors.join('\n')}`);
await ctx.close();
await browser.close();
