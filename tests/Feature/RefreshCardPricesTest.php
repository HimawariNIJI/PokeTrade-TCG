<?php

use App\Models\Card;
use Illuminate\Support\Facades\Http;

it('refreshes market_price from the API without touching house price or stock', function () {
    $card = Card::create([
        'api_id' => 'sv1-25',
        'name' => 'Pikachu',
        'slug' => 'sv1-pikachu-25',
        'supertype' => 'Pokémon',
        'price' => 120000,        // house price — admin-controllable
        'market_price' => 80000,  // stale market price
        'stock' => 7,
    ]);

    Http::fake([
        'api.pokemontcg.io/*' => Http::response([
            'data' => [[
                'id' => 'sv1-25',
                'tcgplayer' => ['prices' => ['normal' => ['market' => 10.0]]],
            ]],
            'totalCount' => 1,
        ]),
    ]);

    $this->artisan('cards:refresh-prices')->assertSuccessful();

    $card->refresh();

    // 10 USD * 16000 = 160000 (already a multiple of 500).
    expect((int) $card->market_price)->toBe(160000)
        ->and((int) $card->price)->toBe(120000)  // house price untouched
        ->and($card->stock)->toBe(7);            // stock untouched
});

it('leaves market_price alone when the API has no usable price', function () {
    $card = Card::create([
        'api_id' => 'sv1-26',
        'name' => 'Raichu',
        'slug' => 'sv1-raichu-26',
        'supertype' => 'Pokémon',
        'market_price' => 50000,
    ]);

    Http::fake([
        'api.pokemontcg.io/*' => Http::response([
            'data' => [[
                'id' => 'sv1-26',
                'tcgplayer' => ['prices' => []],
            ]],
            'totalCount' => 1,
        ]),
    ]);

    $this->artisan('cards:refresh-prices')->assertSuccessful();

    expect((int) $card->fresh()->market_price)->toBe(50000);
});

it('ignores API cards that are not in the local catalogue', function () {
    Http::fake([
        'api.pokemontcg.io/*' => Http::response([
            'data' => [[
                'id' => 'sv9-999',
                'tcgplayer' => ['prices' => ['normal' => ['market' => 5.0]]],
            ]],
            'totalCount' => 1,
        ]),
    ]);

    $this->artisan('cards:refresh-prices')->assertSuccessful();

    expect(Card::count())->toBe(0); // no new card created
});
