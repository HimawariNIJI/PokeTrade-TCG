import { test, expect } from '@playwright/test';
import { spawnSync } from 'node:child_process';
import { trackErrors } from './helpers';

/**
 * End-to-end test for the physical-card delivery flow.
 *
 * Covers two paths that both end in a real, shipped Pokémon TCG card or merch
 * item arriving at the trainer's home address:
 *
 *   1. Auction → place a winning bid → win the auction → an Order is created
 *      and the trainer sees "Heading to your house" copy on both the auction
 *      page and the order page.
 *   2. Merch → browse the shop → confirm physical-item copy → add to cart →
 *      arrive at checkout with the shipping form, plus a previously-paid
 *      merch order showing the same physical-delivery copy.
 *
 * Midtrans Snap is a third-party modal that can't run against a fake key in
 * CI, so the test seeds the post-payment DB state via `php artisan tinker`
 * (same pattern as `resetE2EUserState` in authed.spec.ts) and then drives the
 * actual UI from there.
 */

type SetupFixtures = {
  auctionId: number;
  auctionOrderCode: string;
  merchOrderCode: string;
  shopItemSlug: string;
};

/**
 * Sets up the trainer's state for the delivery flow:
 *   - clears any prior auction wins / merch orders so the test is repeatable
 *   - seeds a previously-paid merch order with a full shipping address
 *   - hands the trainer the highest paid bid on a live auction, closes it,
 *     and calls snapshotWinner() so the AuctionWon order is generated the
 *     same way real bid settlement would generate it
 *   - returns the IDs/slugs the test needs to navigate the UI
 */
function setupDeliveryFixtures(): SetupFixtures {
  // Single tinker invocation so we pay the artisan boot cost once. PHP code
  // is intentionally one statement per `;` with NO inline `//` comments —
  // tinker's --execute mode accepts multi-line PHP but `//` would swallow
  // the rest of the script after any newline-collapsing. The PHP below:
  //   (1) clears the e2e trainer's prior orders, bids, auction wins
  //   (2) seeds a paid merch order with a full home shipping address
  //   (3) makes the trainer the winning bidder on a live auction and calls
  //       snapshotWinner() — same path bid settlement would take in prod —
  //       which generates the auction-win Order
  //   (4) prints a parseable <<FIXTURES>>{json}<<END>> marker so the test
  //       knows which IDs/codes to navigate to.
  const php = [
    `$u = App\\Models\\User::where('email','e2e@poketrade.test')->first();`,
    `if(!$u){ throw new \\Exception('e2e user missing - run migrate --seed'); }`,
    `$u->forceFill(['phone' => '+62 812 0000 0001'])->save();`,
    `$priorOrderIds = $u->orders()->pluck('id');`,
    `\\DB::table('order_items')->whereIn('order_id', $priorOrderIds)->delete();`,
    `$u->orders()->delete();`,
    `\\App\\Models\\Auction::where('winner_id', $u->id)->update(['winner_id' => null, 'winning_amount' => null, 'winner_paid_at' => null]);`,
    `\\App\\Models\\Bid::where('user_id', $u->id)->delete();`,
    `$shopItem = \\App\\Models\\ShopItem::orderBy('id')->first();`,
    `if(!$shopItem){ throw new \\Exception('shop items missing - run migrate --seed'); }`,
    `$merchCode = 'PT-MERCH-E2E-' . strtoupper(\\Illuminate\\Support\\Str::random(6));`,
    `$merchOrder = \\App\\Models\\Order::create(['code' => $merchCode, 'user_id' => $u->id, 'status' => 'paid', 'payment_status' => 'paid', 'payment_method' => 'midtrans', 'payment_reference' => 'e2e-merch-'.uniqid(), 'subtotal' => $shopItem->price, 'shipping_fee' => 25000, 'tax' => $shopItem->price * 0.1, 'total' => $shopItem->price + 25000 + ($shopItem->price * 0.1), 'shipping_name' => $u->name, 'shipping_phone' => $u->phone, 'shipping_address' => 'Jl. Trainer Road No. 42', 'shipping_city' => 'Pallet Town', 'shipping_postal_code' => '12345', 'paid_at' => now()]);`,
    `\\App\\Models\\OrderItem::create(['order_id' => $merchOrder->id, 'itemable_id' => $shopItem->id, 'itemable_type' => \\App\\Models\\ShopItem::class, 'name_snapshot' => $shopItem->name, 'image_snapshot' => $shopItem->image, 'price_snapshot' => $shopItem->price, 'quantity' => 1, 'subtotal' => $shopItem->price]);`,
    `$auction = \\App\\Models\\Auction::orderBy('id')->first();`,
    `if(!$auction){ throw new \\Exception('no auction to win - run migrate --seed'); }`,
    `$auction->forceFill(['status' => 'live', 'current_bid' => $auction->starting_bid, 'current_leader_id' => null, 'winner_id' => null, 'winning_amount' => null, 'winner_paid_at' => null, 'starts_at' => now()->subHour(), 'ends_at' => now()->addDays(1)])->save();`,
    `\\App\\Models\\Bid::where('auction_id', $auction->id)->delete();`,
    `$winningAmount = (float)$auction->starting_bid + (float)$auction->bid_increment + 25000;`,
    `\\App\\Models\\Bid::create(['auction_id' => $auction->id, 'user_id' => $u->id, 'amount' => $winningAmount, 'status' => 'paid', 'order_id' => 'e2e-bid-'.uniqid(), 'paid_at' => now()]);`,
    `$auction->forceFill(['current_bid' => $winningAmount, 'current_leader_id' => $u->id, 'ends_at' => now()->subMinute()])->save();`,
    `$auction->snapshotWinner();`,
    `$auction->update(['status' => 'ended']);`,
    `$winnerOrder = $auction->winnerOrder();`,
    `if(!$winnerOrder){ throw new \\Exception('auction winner order was not created'); }`,
    `echo '<<FIXTURES>>' . json_encode(['auctionId' => $auction->id, 'auctionOrderCode' => $winnerOrder->code, 'merchOrderCode' => $merchCode, 'shopItemSlug' => $shopItem->slug]) . '<<END>>';`,
  ].join(' ');

  const r = spawnSync('php', ['artisan', 'tinker', '--execute=' + php], {
    cwd: process.cwd(),
    encoding: 'utf8',
  });
  if (r.status !== 0) {
    throw new Error(`delivery setup failed (exit ${r.status}):\n${r.stderr}\n${r.stdout}`);
  }

  const match = /<<FIXTURES>>(.+?)<<END>>/.exec(r.stdout);
  if (!match) {
    throw new Error(`delivery setup did not return fixtures:\n${r.stdout}`);
  }
  return JSON.parse(match[1]);
}

test.describe('physical-card delivery flow', () => {
  // Both tests in this describe mutate the e2e trainer's orders + a seeded
  // auction. Running them in parallel workers makes test #2's setup delete
  // test #1's freshly-created auction order (both setups call $u->orders()->
  // delete() to start from a known state), which 404s test #1's navigation.
  // Pin them to a single worker so the shared beforeAll fixture is honored.
  test.describe.configure({ mode: 'serial' });

  let fx: SetupFixtures;

  test.beforeAll(() => {
    fx = setupDeliveryFixtures();
  });

  test('auction win: bid arena → won-auction banner → physical order page', async ({ page }) => {
    // trackErrors catches *all* errors on `page` across navigations. The
    // auction show page polls a 1s "auction expired? end it" handler that
    // POSTs to /auctions/{id}/end — when the auction is already ended that
    // endpoint legitimately returns 422 (it's a noop). Filter only that
    // specific known-noisy URL pattern, so a genuinely broken page still fails.
    const rawErrors = trackErrors(page);
    const auctionEndNoop = new RegExp(`422 .*?/auctions/${fx.auctionId}/end`);

    // --- Arrive on the auction the trainer just won ---
    const showResp = await page.goto(`/auctions/${fx.auctionId}`, { waitUntil: 'domcontentloaded' });
    expect(showResp!.status(), 'auction page status').toBeLessThan(400);

    // The hardcoded physical-card callout must be visible to every visitor so
    // bidders know up front that winning means a real card gets mailed to them.
    const callout = page.locator('[data-test="auction-physical-callout"]');
    await expect(callout, 'auction page must show physical-card callout').toBeVisible();
    await expect(callout).toContainText(/Real physical card/i);
    await expect(callout).toContainText(/ship the actual graded card to your home address/i);

    // Winner banner: the trainer is the winner, so the "You won" block + the
    // physical-delivery reassurance + a link to the generated Order must all
    // be present.
    const winnerBanner = page.locator('[data-test="auction-winner-banner"]');
    await expect(winnerBanner, 'auction page must show winner banner for the winning trainer').toBeVisible();
    await expect(winnerBanner).toContainText(/You won this auction/i);

    const winnerPhysicalNote = page.locator('[data-test="auction-winner-physical-note"]');
    await expect(winnerPhysicalNote).toBeVisible();
    await expect(winnerPhysicalNote).toContainText(/Heading to your house/i);
    await expect(winnerPhysicalNote).toContainText(/real, physical Pokémon TCG card/i);

    // --- Follow the "View order" CTA into the generated Order ---
    const viewOrder = winnerBanner.getByRole('link', { name: /View order/i });
    await expect(viewOrder).toBeVisible();
    await viewOrder.click();
    await page.waitForLoadState('domcontentloaded');
    await expect(page).toHaveURL(new RegExp(`/orders/${fx.auctionOrderCode}$`));

    // Order page: hardcoded physical-delivery callout + shipping address +
    // status timeline must all reflect that this is a real package en route.
    const orderCallout = page.locator('[data-test="order-physical-callout"]');
    await expect(orderCallout, 'order page must show physical-delivery callout').toBeVisible();
    await expect(orderCallout).toContainText(/Physical delivery/i);
    await expect(orderCallout).toContainText(/real, physical products/i);
    await expect(orderCallout).toContainText(/shipping address shown on this order/i);

    // The shipping address that cascaded from the prior merch order must show
    // up — this is the literal home address the card is being mailed to.
    await expect(page.getByText(/Jl\. Trainer Road No\. 42/)).toBeVisible();
    await expect(page.getByText(/Pallet Town/)).toBeVisible();

    const errors = rawErrors.filter((e) => !auctionEndNoop.test(e));
    expect(errors, `errors on auction-win flow:\n${errors.join('\n')}`).toEqual([]);
  });

  test('merch purchase: shop → cart → checkout → previously-paid order delivery', async ({ page }) => {
    const errors = trackErrors(page);

    // --- Browse the shop and confirm the physical-product line ---
    const shopResp = await page.goto(`/shop/${fx.shopItemSlug}`, { waitUntil: 'domcontentloaded' });
    expect(shopResp!.status(), 'shop item page status').toBeLessThan(400);

    const shopNote = page.locator('[data-test="shop-physical-note"]');
    await expect(shopNote, 'shop item page must show physical-item note').toBeVisible();
    await expect(shopNote).toContainText(/Physical item · shipped to your home/i);

    // Add to cart and follow the redirect to /cart.
    await page.getByRole('button', { name: /Add to cart/i }).click();
    await page.waitForLoadState('domcontentloaded');

    // --- Cart shows physical reassurance + the shop item we just added ---
    if (!/\/cart/.test(page.url())) {
      await page.goto('/cart', { waitUntil: 'domcontentloaded' });
    }
    const cartNote = page.locator('[data-test="cart-physical-note"]');
    await expect(cartNote, 'cart must show physical-product note').toBeVisible();
    await expect(cartNote).toContainText(/real, physical product/i);
    await expect(cartNote).toContainText(/ship it to your home address/i);

    // --- Proceed to checkout and verify the physical-delivery banner ---
    await page.getByRole('link', { name: /Proceed to checkout/i }).click();
    await page.waitForLoadState('domcontentloaded');
    await expect(page).toHaveURL(/\/checkout/);

    const checkoutBanner = page.locator('[data-test="checkout-physical-banner"]');
    await expect(checkoutBanner, 'checkout must show physical-delivery banner').toBeVisible();
    await expect(checkoutBanner).toContainText(/Real items, real address/i);
    await expect(checkoutBanner).toContainText(/sealed merch and actual Pokémon TCG cards/i);
    await expect(checkoutBanner).toContainText(/ship them to the address you enter below/i);

    // Shipping form must be present so the trainer can supply a home address.
    await expect(page.locator('input[name="shipping_address"]')).toBeVisible();
    await expect(page.locator('input[name="shipping_city"]')).toBeVisible();
    await expect(page.locator('input[name="shipping_postal_code"]')).toBeVisible();

    // --- A previously-paid merch order shows the same delivery copy on /orders/{code} ---
    // This mirrors what a trainer would see after Midtrans Snap settles a real
    // checkout: the order is paid and physical-delivery copy is reiterated.
    const merchOrderResp = await page.goto(`/orders/${fx.merchOrderCode}`, { waitUntil: 'domcontentloaded' });
    expect(merchOrderResp!.status(), 'merch order page status').toBeLessThan(400);

    const merchOrderCallout = page.locator('[data-test="order-physical-callout"]');
    await expect(merchOrderCallout).toBeVisible();
    await expect(merchOrderCallout).toContainText(/Physical delivery/i);
    await expect(merchOrderCallout).toContainText(/actual cards and merch arriving at your door/i);
    await expect(page.getByText(/Jl\. Trainer Road No\. 42/)).toBeVisible();

    expect(errors, `errors on merch-purchase flow:\n${errors.join('\n')}`).toEqual([]);
  });
});
