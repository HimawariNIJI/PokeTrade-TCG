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
                'image_snapshot' => 'shop/etb.jpg',
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
                'image_snapshot' => 'shop/etb.jpg',
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
                'image_snapshot' => 'shop/etb.jpg',
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
                'image_snapshot' => 'shop/etb.jpg',
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
                'image_snapshot' => 'shop/booster.jpg',
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
                'image_snapshot' => 'shop/etb.jpg',
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
                'image_snapshot' => 'shop/booster.jpg',
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
                'image_snapshot' => 'shop/etb.jpg',
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
                'image_snapshot' => 'shop/booster.jpg',
                'price_snapshot' => 65000.00,
                'quantity' => 1,
                'subtotal' => 65000.00,
                'created_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
            ]
        );
    }
}
