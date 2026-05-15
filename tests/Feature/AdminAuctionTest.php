<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Card;
use App\Models\User;

/**
 * Returns a freshly-created admin user. `role` is force-filled to bypass
 * mass-assignment guarding in case it is not in the User model's $fillable.
 */
function adminUser(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    return $user;
}

test('admin auctions index renders the auction table', function () {
    $this->actingAs(adminUser())
        ->get('/admin/auctions')
        ->assertOk()
        ->assertSee('New Auction')
        ->assertSee('Charizard ex');
});

test('admin auction create page renders with the card picker', function () {
    $this->actingAs(adminUser())
        ->get('/admin/auctions/create')
        ->assertOk()
        ->assertSee('Choose Card')
        ->assertSee('Publish auction');
});

test('admin auction edit page renders the bid highlight panel', function () {
    $this->actingAs(adminUser())
        ->get('/admin/auctions/1/edit')
        ->assertOk()
        ->assertSee('Highlighted bid')
        ->assertSee('Save changes');
});

test('card search stub returns json results', function () {
    $this->actingAs(adminUser())
        ->getJson('/admin/auctions/cards/search?q=char')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'image_small', 'set_name', 'rarity']]])
        ->assertJsonFragment(['name' => 'Charizard ex']);
});

test('non-admin users cannot reach the auction admin', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/auctions')
        ->assertForbidden();
});

test('public auction page renders the neon arena', function () {
    $seller = User::factory()->create();
    $leader = User::factory()->create(['name' => 'ashketchum_id']);

    $card = Card::create([
        'api_id'      => 'test-arena-001',
        'name'        => 'Charizard ex',
        'slug'        => 'charizard-ex-arena-test',
        'supertype'   => 'Pokémon',
        'image_small' => 'https://images.pokemontcg.io/sv3/6.png',
        'image_large' => 'https://images.pokemontcg.io/sv3/6_hires.png',
    ]);

    $auction = Auction::create([
        'card_id'           => $card->id,
        'seller_id'         => $seller->id,
        'current_leader_id' => $leader->id,
        'starting_bid'      => 500000,
        'current_bid'       => 4250000,
        'bid_increment'     => 50000,
        'starts_at'         => now()->subHour(),
        'ends_at'           => now()->addHours(2),
        'status'            => 'live',
    ]);

    Bid::create(['auction_id' => $auction->id, 'user_id' => $leader->id, 'amount' => 4250000]);

    $this->get("/auctions/{$auction->id}")
        ->assertOk()
        ->assertSee('Top Bidders')
        ->assertSee('Live Bid Feed')
        ->assertSee('ashketchum_id');
});
