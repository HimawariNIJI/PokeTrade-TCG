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

        // E2E trainer for the Playwright suite (auth.setup.ts). Guarded
        // so the Playwright user never appears in production.
        if (app()->environment(['local', 'testing'])) {
            User::firstOrCreate(
                ['email' => 'e2e@poketrade.test'],
                [
                    'name' => 'E2E Trainer',
                    'password' => Hash::make('password123'),
                    'role' => User::ROLE_CUSTOMER,
                    'email_verified_at' => now(),
                    'points' => 500,
                ]
            );
        }

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
