import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const pages: [string, string][] = [
  ['home', '/'],
  ['cards', '/cards'],
  ['shop', '/shop'],
  ['gacha', '/gacha'],
  ['auctions', '/auctions'],
  ['login', '/login'],
];

for (const [name, path] of pages) {
  test(`a11y: no serious/critical violations on ${name}`, async ({ page }) => {
    await page.goto(path, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(300);

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa'])
      .analyze();

    const blocking = results.violations.filter(
      (v) => v.impact === 'serious' || v.impact === 'critical'
    );

    const summary = blocking
      .map((v) => `- [${v.impact}] ${v.id}: ${v.help} (${v.nodes.length} nodes)`)
      .join('\n');

    expect(blocking, `a11y violations on ${path}:\n${summary}`).toEqual([]);
  });
}
