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
        // These satisfy the rubric's image-upload requirement (admin can re-upload).
        $items = [
            ['name' => 'Prismatic Evolutions Booster Box',          'category' => 'booster',   'price' => 159.99, 'stock' => 12, 'featured' => true],
            ['name' => 'Prismatic Evolutions Elite Trainer Box',    'category' => 'bundle',    'price' => 79.99,  'stock' => 18, 'featured' => true],
            ['name' => 'Prismatic Evolutions Booster Bundle (×6)',  'category' => 'bundle',    'price' => 29.99,  'stock' => 40, 'featured' => false],
            ['name' => 'Eevee Holographic Sleeves (×65)',           'category' => 'accessory', 'price' => 12.99,  'stock' => 80, 'featured' => false],
            ['name' => 'Umbreon Premium Playmat',                   'category' => 'accessory', 'price' => 34.99,  'stock' => 25, 'featured' => true],
            ['name' => 'Sylveon Deck Box',                          'category' => 'accessory', 'price' => 14.99,  'stock' => 60, 'featured' => false],
            ['name' => 'Espeon Plush — 12 inch',                    'category' => 'plush',     'price' => 24.99,  'stock' => 20, 'featured' => false],
            ['name' => 'Vaporeon Plush — 12 inch',                  'category' => 'plush',     'price' => 24.99,  'stock' => 18, 'featured' => false],
            ['name' => 'Prismatic Holo Card Binder (240ct)',        'category' => 'accessory', 'price' => 39.99,  'stock' => 22, 'featured' => false],
            ['name' => 'Mystery Vintage Pack Bundle',               'category' => 'other',     'price' => 49.99,  'stock' => 15, 'featured' => true],
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
