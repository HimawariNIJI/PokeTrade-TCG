<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@poketrade.test'],
            [
                'name' => 'PokeTrade Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'trainer@poketrade.test'],
            [
                'name' => 'Ash Ketchum',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            CardSeeder::class,
            CardPriceHistorySeeder::class,
            ShopItemSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            AuctionSeeder::class,
            CommunitySeeder::class,
        ]);
    }
}
