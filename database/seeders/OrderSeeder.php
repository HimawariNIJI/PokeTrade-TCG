<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'trainer@poketrade.test'],
            [
                'name' => 'Ash Ketchum',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ]
        );

        DB::table('orders')->updateOrInsert(
            ['id' => 4],
            [
                'code' => 'PT-2026-000004',
                'user_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => null,
                'payment_reference' => null,
                'subtotal' => 1299000.00,
                'shipping_fee' => 20000.00,
                'tax' => 14950.00,
                'total' => 1333950.00,
                'shipping_name' => 'Ash Ketchum',
                'shipping_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                'shipping_city' => 'Pallet Town',
                'shipping_postal_code' => '12345',
                'notes' => 'Payment pending via bank transfer.',
                'paid_at' => null,
                'shipped_at' => null,
                'delivered_at' => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]
        );

        DB::table('orders')->updateOrInsert(
            ['id' => 5],
            [
                'code' => 'PT-2026-000005',
                'user_id' => $customer->id,
                'status' => 'paid',
                'payment_status' => 'paid',
                'payment_method' => null,
                'payment_reference' => null,
                'subtotal' => 1299000.00,
                'shipping_fee' => 20000.00,
                'tax' => 14950.00,
                'total' => 1333950.00,
                'shipping_name' => 'Ash Ketchum',
                'shipping_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                'shipping_city' => 'Pallet Town',
                'shipping_postal_code' => '12345',
                'notes' => 'Payment confirmed and order is being processed.',
                'paid_at' => now()->subHour(),
                'shipped_at' => null,
                'delivered_at' => null,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHour(),
            ]
        );

        DB::table('orders')->updateOrInsert(
            ['id' => 6],
            [
                'code' => 'PT-2026-000006',
                'user_id' => $customer->id,
                'status' => 'paid',
                'payment_status' => 'paid',
                'payment_method' => null,
                'payment_reference' => null,
                'subtotal' => 1299000.00,
                'shipping_fee' => 20000.00,
                'tax' => 14950.00,
                'total' => 1333950.00,
                'shipping_name' => 'Ash Ketchum',
                'shipping_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                'shipping_city' => 'Pallet Town',
                'shipping_postal_code' => '12345',
                'notes' => 'Payment confirmed and order is being processed.',
                'paid_at' => now()->subHour(),
                'shipped_at' => null,
                'delivered_at' => null,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHour(),
            ]
        );

        // December 2025 paid order
        DB::table('orders')->updateOrInsert(
            ['id' => 7],
            [
                'code' => 'PT-2025-000007',
                'user_id' => $customer->id,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => null,
                'payment_reference' => null,
                'subtotal' => 165000.00,
                'shipping_fee' => 20000.00,
                'tax' => 10000.00,
                'total' => 195000.00,
                'shipping_name' => 'Ash Ketchum',
                'shipping_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                'shipping_city' => 'Pallet Town',
                'shipping_postal_code' => '12345',
                'notes' => 'Holiday season order - successfully delivered.',
                'paid_at' => \Carbon\Carbon::create(2025, 12, 20, 14, 30, 0),
                'shipped_at' => \Carbon\Carbon::create(2025, 12, 22, 10, 0, 0),
                'delivered_at' => \Carbon\Carbon::create(2025, 12, 28, 15, 45, 0),
                'created_at' => \Carbon\Carbon::create(2025, 12, 20, 10, 0, 0),
                'updated_at' => \Carbon\Carbon::create(2025, 12, 28, 15, 45, 0),
            ]
        );

        // January 2026 paid order
        DB::table('orders')->updateOrInsert(
            ['id' => 8],
            [
                'code' => 'PT-2026-000008',
                'user_id' => $customer->id,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => null,
                'payment_reference' => null,
                'subtotal' => 470000.00,
                'shipping_fee' => 20000.00,
                'tax' => 10000.00,
                'total' => 500000.00,
                'shipping_name' => 'Ash Ketchum',
                'shipping_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                'shipping_city' => 'Pallet Town',
                'shipping_postal_code' => '12345',
                'notes' => 'New year purchase - successfully delivered.',
                'paid_at' => \Carbon\Carbon::create(2026, 1, 15, 11, 20, 0),
                'shipped_at' => \Carbon\Carbon::create(2026, 1, 17, 9, 0, 0),
                'delivered_at' => \Carbon\Carbon::create(2026, 1, 22, 14, 30, 0),
                'created_at' => \Carbon\Carbon::create(2026, 1, 15, 8, 0, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 1, 22, 14, 30, 0),
            ]
        );

        // March 2026 paid order
        DB::table('orders')->updateOrInsert(
            ['id' => 9],
            [
                'code' => 'PT-2026-000009',
                'user_id' => $customer->id,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => null,
                'payment_reference' => null,
                'subtotal' => 160000.00,
                'shipping_fee' => 20000.00,
                'tax' => 10000.00,
                'total' => 190000.00,
                'shipping_name' => 'Ash Ketchum',
                'shipping_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                'shipping_city' => 'Pallet Town',
                'shipping_postal_code' => '12345',
                'notes' => 'Spring collection order - successfully delivered.',
                'paid_at' => \Carbon\Carbon::create(2026, 3, 10, 13, 45, 0),
                'shipped_at' => \Carbon\Carbon::create(2026, 3, 12, 11, 15, 0),
                'delivered_at' => \Carbon\Carbon::create(2026, 3, 18, 16, 0, 0),
                'created_at' => \Carbon\Carbon::create(2026, 3, 10, 9, 30, 0),
                'updated_at' => \Carbon\Carbon::create(2026, 3, 18, 16, 0, 0),
            ]
        );
        // --- DUMMY DATA FOR DASHBOARD SHOWCASE (25% - 50% of June's Revenue) ---
        
        $showcaseOrders = [
            // Jan 2026: ~Rp 150 Juta (approx 29%)
            ['id' => 10, 'month' => 1, 'subtotal' => 149980000.00, 'total' => 150000000.00],
            // Feb 2026: ~Rp 210 Juta (approx 41%)
            ['id' => 11, 'month' => 2, 'subtotal' => 209980000.00, 'total' => 210000000.00],
            // Mar 2026: ~Rp 180 Juta (approx 35%)
            ['id' => 12, 'month' => 3, 'subtotal' => 179980000.00, 'total' => 180000000.00],
            // Apr 2026: ~Rp 240 Juta (approx 47%)
            ['id' => 13, 'month' => 4, 'subtotal' => 239980000.00, 'total' => 240000000.00],
            // May 2026: ~Rp 195 Juta (approx 38%)
            ['id' => 14, 'month' => 5, 'subtotal' => 194980000.00, 'total' => 195000000.00],
        ];

        foreach ($showcaseOrders as $order) {
            DB::table('orders')->updateOrInsert(
                ['id' => $order['id']],
                [
                    'code' => 'PT-2026-0000' . $order['id'],
                    'user_id' => $customer->id,
                    'status' => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => null,
                    'payment_reference' => null,
                    'subtotal' => $order['subtotal'],
                    'shipping_fee' => 20000.00,
                    'tax' => 0.00, // Diset 0 agar perhitungan rapi
                    'total' => $order['total'],
                    'shipping_name' => 'Ash Ketchum',
                    'shipping_phone' => '+62 812-3456-7890',
                    'shipping_address' => 'Pallet Town Trading Post, 12 Route 1',
                    'shipping_city' => 'Pallet Town',
                    'shipping_postal_code' => '12345',
                    'notes' => 'Wholesale showcase order for dashboard testing.',
                    'paid_at' => \Carbon\Carbon::create(2026, $order['month'], 15, 12, 0, 0),
                    'shipped_at' => \Carbon\Carbon::create(2026, $order['month'], 16, 10, 0, 0),
                    'delivered_at' => \Carbon\Carbon::create(2026, $order['month'], 20, 14, 0, 0),
                    'created_at' => \Carbon\Carbon::create(2026, $order['month'], 15, 10, 0, 0),
                    'updated_at' => \Carbon\Carbon::create(2026, $order['month'], 20, 14, 0, 0),
                ]
            );
        }
    }
}
