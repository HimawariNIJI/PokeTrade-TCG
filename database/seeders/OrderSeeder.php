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
    }
}
