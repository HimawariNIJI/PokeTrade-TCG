<?php

namespace App\Support;

use App\Models\Card;
use Illuminate\Support\Collection;

/**
 * Single source of truth for the digital-gacha pull rates.
 *
 * The catalogue is imported from the Pokémon TCG API, which uses ~14
 * distinct `rarity` strings. We fold those into six display tiers and
 * assign each tier a pull weight. A pull rolls a tier by weight, then
 * draws a uniformly-random card from that tier's pool — so the live
 * odds match the rate table shown on the gacha page exactly.
 */
class Gacha
{
    /** Points charged for a pull once the daily free pull is spent. */
    public const PULL_COST = 10;

    /** Cards awarded per pull. */
    public const PACK_SIZE = 5;

    /**
     * Pull tiers, highest probability first. `weight` is the percent
     * chance a single card lands in the tier (weights sum to 100).
     * `rarities` are the raw API rarity strings folded into the tier;
     * any unknown/blank rarity falls back to Common.
     */
    public const TIERS = [
        'Common' => [
            'weight'   => 60.0,
            'rarities' => ['Common'],
        ],
        'Uncommon' => [
            'weight'   => 25.0,
            'rarities' => ['Uncommon'],
        ],
        'Rare' => [
            'weight'   => 10.0,
            'rarities' => ['Rare', 'Double Rare', 'Ultra Rare', 'ACE SPEC Rare', 'Promo'],
        ],
        'Illustration' => [
            'weight'   => 4.0,
            'rarities' => ['Illustration Rare'],
        ],
        'Special Illu.' => [
            'weight'   => 0.9,
            'rarities' => ['Special Illustration Rare'],
        ],
        'Hyper Rare' => [
            'weight'   => 0.1,
            'rarities' => ['Hyper Rare', 'Mega Hyper Rare', 'MEGA_ATTACK_RARE', 'Black White Rare'],
        ],
    ];

    /**
     * Rate-table rows for the UI: [['rarity' => 'Common', 'rate' => '60%'], ...].
     */
    public static function rateTable(): array
    {
        return collect(self::TIERS)
            ->map(fn (array $tier, string $name) => [
                'rarity' => $name,
                'rate'   => self::formatRate($tier['weight']),
            ])
            ->values()
            ->all();
    }

    /**
     * Roll a pack: pick each card's tier by weight, then a random card
     * from that tier. Duplicates are possible and intentional.
     *
     * @return Collection<int, Card>
     */
    public static function roll(int $count = self::PACK_SIZE): Collection
    {
        $idsByTier = self::cardIdsByTier();
        $allIds = array_merge(...array_values($idsByTier));

        if (empty($allIds)) {
            return collect();
        }

        $chosenIds = [];
        for ($i = 0; $i < $count; $i++) {
            $tier = self::rollTier();
            // Fall back to the whole catalogue if a tier happens to be empty.
            $pool = ! empty($idsByTier[$tier]) ? $idsByTier[$tier] : $allIds;
            $chosenIds[] = $pool[array_rand($pool)];
        }

        $byId = Card::query()
            ->whereIn('id', array_unique($chosenIds))
            ->get()
            ->keyBy('id');

        return collect($chosenIds)->map(fn ($id) => $byId[$id]);
    }

    /**
     * Choose a tier name by weighted random draw.
     */
    public static function rollTier(): string
    {
        $total = array_sum(array_column(self::TIERS, 'weight'));
        // Uniform real in [0, total] at 0.001 resolution — fine enough to
        // resolve the 0.1% tier.
        $roll = mt_rand(0, (int) round($total * 1000)) / 1000.0;

        $cumulative = 0.0;
        foreach (self::TIERS as $name => $tier) {
            $cumulative += $tier['weight'];
            if ($roll <= $cumulative) {
                return $name;
            }
        }

        return array_key_first(self::TIERS);
    }

    /**
     * The display tier a raw API rarity belongs to (Common by default).
     */
    public static function tierForRarity(?string $rarity): string
    {
        return self::rarityToTierMap()[$rarity] ?? 'Common';
    }

    /**
     * Catalogue card ids grouped by tier, one lightweight query.
     *
     * @return array<string, array<int, int>>
     */
    private static function cardIdsByTier(): array
    {
        $rarityToTier = self::rarityToTierMap();

        $buckets = [];
        foreach (array_keys(self::TIERS) as $name) {
            $buckets[$name] = [];
        }

        foreach (Card::query()->get(['id', 'rarity']) as $card) {
            $tier = $rarityToTier[$card->rarity] ?? 'Common';
            $buckets[$tier][] = $card->id;
        }

        return $buckets;
    }

    /**
     * raw rarity string => tier name.
     *
     * @return array<string, string>
     */
    private static function rarityToTierMap(): array
    {
        $map = [];
        foreach (self::TIERS as $name => $tier) {
            foreach ($tier['rarities'] as $rarity) {
                $map[$rarity] = $name;
            }
        }

        return $map;
    }

    private static function formatRate(float $weight): string
    {
        return rtrim(rtrim(number_format($weight, 1), '0'), '.') . '%';
    }
}
