<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('order_items')->updateOrInsert(
            ['id' => 5],
            [
                'order_id' => 4,
                'itemable_id' => 1,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box',
                'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                'price_snapshot' => 1299000.00,
                'quantity' => 1,
                'subtotal' => 1299000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('order_items')->updateOrInsert(
            ['id' => 6],
            [
                'order_id' => 5,
                'itemable_id' => 1,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box',
                'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                'price_snapshot' => 1299000.00,
                'quantity' => 1,
                'subtotal' => 1299000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('order_items')->updateOrInsert(
            ['id' => 7],
            [
                'order_id' => 6,
                'itemable_id' => 1,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box',
                'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                'price_snapshot' => 1299000.00,
                'quantity' => 1,
                'subtotal' => 1299000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // December 2025 order items
        DB::table('order_items')->updateOrInsert(
            ['id' => 8],
            [
                'order_id' => 7,
                'itemable_id' => 1,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box',
                'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                'price_snapshot' => 100000.00,
                'quantity' => 1,
                'subtotal' => 100000.00,
                'created_at' => \Carbon\Carbon::create(2025, 12, 20, 10, 0, 0),
                'updated_at' => \Carbon\Carbon::create(2025, 12, 20, 10, 0, 0),
            ]
        );

        DB::table('order_items')->updateOrInsert(
            ['id' => 9],
            [
                'order_id' => 7,
                'itemable_id' => 2,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Booster Box - Mystery Collection',
                'image_snapshot' => 'shop/mystery.jpg',
                'price_snapshot' => 65000.00,
                'quantity' => 1,
                'subtotal' => 65000.00,
                'created_at' => \Carbon\Carbon::create(2025, 12, 20, 10, 0, 0),
                'updated_at' => \Carbon\Carbon::create(2025, 12, 20, 10, 0, 0),
            ]
        );

        // January 2026 order items
        DB::table('order_items')->updateOrInsert(
            ['id' => 10],
            [
                'order_id' => 8,
                'itemable_id' => 1,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box',
                'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                'price_snapshot' => 250000.00,
                'quantity' => 1,
                'subtotal' => 250000.00,
                'created_at' => \Carbon\Carbon::create(2026, 1, 15, 8, 0, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 1, 15, 8, 0, 0),
            ]
        );

        DB::table('order_items')->updateOrInsert(
            ['id' => 11],
            [
                'order_id' => 8,
                'itemable_id' => 2,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Booster Box - Mystery Collection',
                'image_snapshot' => 'shop/mystery.jpg',
                'price_snapshot' => 220000.00,
                'quantity' => 1,
                'subtotal' => 220000.00,
                'created_at' => \Carbon\Carbon::create(2026, 1, 15, 8, 0, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 1, 15, 8, 0, 0),
            ]
        );

        // March 2026 order items
        DB::table('order_items')->updateOrInsert(
            ['id' => 12],
            [
                'order_id' => 9,
                'itemable_id' => 1,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box',
                'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                'price_snapshot' => 95000.00,
                'quantity' => 1,
                'subtotal' => 95000.00,
                'created_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
            ]
        );

        DB::table('order_items')->updateOrInsert(
            ['id' => 13],
            [
                'order_id' => 9,
                'itemable_id' => 2,
                'itemable_type' => 'App\\Models\\ShopItem',
                'name_snapshot' => 'Booster Box - Mystery Collection',
                'image_snapshot' => 'shop/mystery.jpg',
                'price_snapshot' => 65000.00,
                'quantity' => 1,
                'subtotal' => 65000.00,
                'created_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
            ]
        );
        // --- DUMMY DATA FOR DASHBOARD SHOWCASE ITEMS ---
        
        $showcaseItems = [
            // Jan 2026 Items (Order ID 10)
            ['id' => 14, 'order_id' => 10, 'qty' => 100, 'price' => 1499800.00, 'subtotal' => 149980000.00],
            // Feb 2026 Items (Order ID 11)
            ['id' => 15, 'order_id' => 11, 'qty' => 140, 'price' => 1499857.14, 'subtotal' => 209980000.00],
            // Mar 2026 Items (Order ID 12)
            ['id' => 16, 'order_id' => 12, 'qty' => 120, 'price' => 1499833.33, 'subtotal' => 179980000.00],
            // Apr 2026 Items (Order ID 13)
            ['id' => 17, 'order_id' => 13, 'qty' => 160, 'price' => 1499875.00, 'subtotal' => 239980000.00],
            // May 2026 Items (Order ID 14)
            ['id' => 18, 'order_id' => 14, 'qty' => 130, 'price' => 1499846.15, 'subtotal' => 194980000.00],
        ];

        foreach ($showcaseItems as $item) {
            // Month is derived from order_id (10 -> Jan(1), 11 -> Feb(2), etc)
            $month = $item['order_id'] - 9; 

            DB::table('order_items')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'order_id' => $item['order_id'],
                    'itemable_id' => 1, // Elite Trainer Box
                    'itemable_type' => 'App\\Models\\ShopItem',
                    'name_snapshot' => 'Prismatic Evolutions Elite Trainer Box (Wholesale)',
                    'image_snapshot' => 'shop/prismatic-evolutions-booster-box.jpeg',
                    'price_snapshot' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => \Carbon\Carbon::create(2026, $month, 15, 10, 0, 0),
                    'updated_at' => \Carbon\Carbon::create(2026, $month, 15, 10, 0, 0),
                ]
            );
        }
    }
}
