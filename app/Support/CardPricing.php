<?php

namespace App\Support;

/**
 * Single source of truth for turning pokemontcg.io / TCGplayer price
 * data into our IDR figures.
 *
 * Used by CardSeeder (initial import) and the cards:refresh-prices
 * command (hourly market-price refresh).
 */
class CardPricing
{
    /** USD → IDR conversion rate. Adjust if rates shift dramatically. */
    public const USD_TO_IDR = 16000;

    /** Markup applied over market price for our house listings. */
    public const HOUSE_MARKUP = 1.10;

    /** Floor price for cards with no available market data (Rp 5.000). */
    public const PRICE_FLOOR_IDR = 5000;

    /**
     * Market price in IDR derived from a tcgplayer "prices" block.
     * Returns 0 when the block holds no usable market data.
     */
    public static function marketPriceIdr(array $tcgplayerPrices): int
    {
        $usd = self::extractMarketPriceUsd($tcgplayerPrices);

        return $usd > 0
            ? self::roundToNearest($usd * self::USD_TO_IDR, 500)
            : 0;
    }

    /**
     * House (selling) price in IDR derived from a market price.
     * Falls back to the floor price when there is no market data.
     */
    public static function housePriceIdr(int $marketIdr): int
    {
        return $marketIdr > 0
            ? self::roundToNearest($marketIdr * self::HOUSE_MARKUP, 500)
            : self::PRICE_FLOOR_IDR;
    }

    /** Pick the best available market price (USD) from a tcgplayer prices block. */
    private static function extractMarketPriceUsd(array $prices): float
    {
        // Prefer normal, then holofoil, then reverseHolofoil.
        foreach (['normal', 'holofoil', 'reverseHolofoil', '1stEditionHolofoil'] as $variant) {
            $market = $prices[$variant]['market'] ?? null;
            if (is_numeric($market) && $market > 0) {
                return (float) $market;
            }
        }

        return 0.0;
    }

    /** Round an IDR amount to the nearest multiple (e.g. 500 or 1000). */
    private static function roundToNearest(float $amount, int $step): int
    {
        return (int) (round($amount / $step) * $step);
    }
}
