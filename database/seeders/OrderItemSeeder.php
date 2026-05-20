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
    }
}
