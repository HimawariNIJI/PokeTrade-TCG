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
        // Images live under storage/app/public/shop/ (real product photos sourced
        // from tcgplayer-cdn and archives.bulbagarden.net for the plushes).
        // If the file is missing locally, the tile falls back to its category placeholder.
        $items = [
            ['name' => 'Prismatic Evolutions Booster Box',          'category' => 'booster',   'price' => 2_599_000, 'stock' => 12, 'featured' => true,  'image' => 'shop/booster-box.jpg'],
            ['name' => 'Prismatic Evolutions Elite Trainer Box',    'category' => 'bundle',    'price' => 1_299_000, 'stock' => 18, 'featured' => true,  'image' => 'shop/etb.jpg'],
            ['name' => 'Prismatic Evolutions Booster Bundle (×6)',  'category' => 'bundle',    'price' => 489_000,   'stock' => 40, 'featured' => false, 'image' => 'shop/booster-bundle.jpg'],
            ['name' => 'Eevee Holographic Sleeves (×65)',           'category' => 'accessory', 'price' => 209_000,   'stock' => 80, 'featured' => false, 'image' => 'shop/eevee-sleeves.jpg'],
            ['name' => 'Umbreon Premium Playmat',                   'category' => 'accessory', 'price' => 559_000,   'stock' => 25, 'featured' => true,  'image' => 'shop/umbreon-playmat.jpg'],
            ['name' => 'Sylveon Deck Box',                          'category' => 'accessory', 'price' => 239_000,   'stock' => 60, 'featured' => false, 'image' => 'shop/sylveon-deckbox.jpg'],
            ['name' => 'Espeon Plush — 12 inch',                    'category' => 'plush',     'price' => 399_000,   'stock' => 20, 'featured' => false, 'image' => 'shop/espeon-plush.jpg'],
            ['name' => 'Vaporeon Plush — 12 inch',                  'category' => 'plush',     'price' => 399_000,   'stock' => 18, 'featured' => false, 'image' => 'shop/vaporeon-plush.jpg'],
            ['name' => 'Prismatic Holo Card Binder (240ct)',        'category' => 'accessory', 'price' => 639_000,   'stock' => 22, 'featured' => false, 'image' => 'shop/holo-binder.jpg'],
            ['name' => 'Mystery Vintage Pack Bundle',               'category' => 'other',     'price' => 799_000,   'stock' => 15, 'featured' => true,  'image' => 'shop/vintage-pack.jpg'],
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
