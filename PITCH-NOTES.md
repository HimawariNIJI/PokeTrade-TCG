# PokeTrade-TCG — Pitch Notes

**Sprint:** 12–19 May 2026 · **Team:** Charlene, Kevin, Ethan, Caroline

---

## What PokeTrade is now

The big change this week: **PokeTrade pivoted from a card marketplace to a TCG tracker.**

We no longer sell or trade Pokémon cards. PokeTrade is now a **card price tracker** with four pillars:

- **Cards** — browse the catalogue, track market prices over time
- **Auctions** — bid on real cards
- **Gacha** — open digital packs into a personal collection
- **Community** — forums and public trainer profiles

Official merch is still sold through the shop. Card trading and the Wanted List were removed.

---

## What each person built

**Charlene** — the pivot itself, plus the card experience:
- Rebuilt the app around the tracker concept (removed trading, added gacha / forums / profiles / settings)
- Card price tracking — daily price history, sparkline chart, enriched card detail page
- Auctions feature (neon "arena" listing, admin auction console)
- UI polish — gacha deck-reveal animation, card flip-to-center, tilt hover

**Kevin** — the checkout pipeline: cart → checkout → Midtrans payment → order. Working end-to-end.

**Ethan** — admin backend: shop item CRUD (with image upload) and order management with status tracking.

**Caroline** — wishlist: add/remove cards, plus notification scaffolding for when a wishlisted card hits auction.

---

## What works right now

- **Cards** — browse, filter by set/regulation, view detail
- **Checkout & payment** — full cart-to-paid-order flow via Midtrans
- **Admin** — manage shop items and orders
- **Auctions** — public listing and admin console (browse only)
- **Gacha** — pack-opening animation and digital collection
- **Forums & profiles** — pages built and seeded with demo data
- **Wishlist** — add/remove cards

---

## In progress

- **Price tracker page** — price-history chart and the enriched card detail page are built but not yet committed
- Daily price refresh command (`cards:refresh-prices`) records a snapshot per card per day

---

## Needs more work

- **Auction bidding** — users can't place bids yet (stub)
- **Admin auctions** — create/edit/delete don't save yet (stubs)
- **Wishlist→auction alerts** — notification class exists but isn't sent
- **Gacha** — pulls don't charge the user yet
- Admin card CRUD and user role management are still stubs

---

## Next steps

1. Finish and commit the price-tracker page
2. Make auctions real — bidding + admin create/edit/delete
3. Wire wishlist alerts to fire when a card goes to auction
4. Polish the new community features (forums, profiles)

---

## One-line close

> "PokeTrade is now a TCG tracker, not a marketplace — you follow card prices, bid on auctions, open digital packs, and hang out in the community. The shopping and admin tools already work; the remaining job is making auctions and the price chart fully interactive."
