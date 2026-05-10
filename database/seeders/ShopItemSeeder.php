<?php

namespace Database\Seeders;

use App\Models\ShopItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopItemSeeder extends Seeder
{
    public function run(): void
    {
        // Custom shop merch — independent of the card catalog.
        // All prices in Indonesian Rupiah (IDR). Realistic local retail
        // pricing for Indonesian TCG hobbyists circa 2026.
        $items = [
            ['name' => 'Prismatic Evolutions Booster Box',          'category' => 'booster',   'price' => 2_599_000, 'stock' => 12, 'featured' => true],
            ['name' => 'Prismatic Evolutions Elite Trainer Box',    'category' => 'bundle',    'price' => 1_299_000, 'stock' => 18, 'featured' => true],
            ['name' => 'Prismatic Evolutions Booster Bundle (×6)',  'category' => 'bundle',    'price' => 489_000,   'stock' => 40, 'featured' => false],
            ['name' => 'Eevee Holographic Sleeves (×65)',           'category' => 'accessory', 'price' => 209_000,   'stock' => 80, 'featured' => false],
            ['name' => 'Umbreon Premium Playmat',                   'category' => 'accessory', 'price' => 559_000,   'stock' => 25, 'featured' => true],
            ['name' => 'Sylveon Deck Box',                          'category' => 'accessory', 'price' => 239_000,   'stock' => 60, 'featured' => false],
            ['name' => 'Espeon Plush — 12 inch',                    'category' => 'plush',     'price' => 399_000,   'stock' => 20, 'featured' => false],
            ['name' => 'Vaporeon Plush — 12 inch',                  'category' => 'plush',     'price' => 399_000,   'stock' => 18, 'featured' => false],
            ['name' => 'Prismatic Holo Card Binder (240ct)',        'category' => 'accessory', 'price' => 639_000,   'stock' => 22, 'featured' => false],
            ['name' => 'Mystery Vintage Pack Bundle',               'category' => 'other',     'price' => 799_000,   'stock' => 15, 'featured' => true],
        ];

        foreach ($items as $i) {
            ShopItem::updateOrCreate(
                ['slug' => Str::slug($i['name'])],
                array_merge($i, [
                    'description' => "Officially licensed Pokémon TCG merchandise — {$i['name']}.",
                    'is_active' => true,
                ])
            );
        }
    }
}
