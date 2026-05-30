<?php

use App\Models\Card;
use App\Models\CollectionCard;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Trainer-controlled profile: avatar/banner upload, pinned-card
 * showcase. The backend of these is the SettingsController +
 * PublicProfileController; this file pins down the contract.
 */

function pcMakeCard(string $slug, string $name = 'Pikachu'): Card
{
    return Card::create([
        'api_id'      => 'pc-'.$slug,
        'name'        => $name,
        'slug'        => 'pc-'.$slug,
        'supertype'   => 'Pokémon',
        'image_small' => 'https://example.com/'.$slug.'.png',
    ]);
}

function pcGiveCard(User $user, Card $card): void
{
    CollectionCard::create([
        'user_id'     => $user->id,
        'card_id'     => $card->id,
        'source'      => 'gacha',
        'obtained_at' => now(),
    ]);
}

test('settings page shows avatar, banner and pinned-card controls', function () {
    $user = User::factory()->create();
    $card = pcMakeCard('test-1', 'Showcase Pikachu');
    pcGiveCard($user, $card);

    $this->actingAs($user)
        ->get(route('settings.edit'))
        ->assertOk()
        ->assertSee('Avatar & banner', escape: false)
        ->assertSee('Pinned cards')
        ->assertSee('Showcase Pikachu');
});

test('avatar upload persists to the public disk and clears the old one', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $first = UploadedFile::fake()->image('me.jpg', 200, 200);

    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'avatar' => $first,
        ])
        ->assertRedirect(route('settings.edit'));

    $user->refresh();
    expect($user->avatar)->not->toBeNull()
        ->and(Storage::disk('public')->exists($user->avatar))->toBeTrue();

    $oldPath = $user->avatar;

    // Replacing the avatar should delete the previous file.
    $second = UploadedFile::fake()->image('me2.jpg', 200, 200);
    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'avatar' => $second,
        ])
        ->assertRedirect(route('settings.edit'));

    $user->refresh();
    expect($user->avatar)->not->toBe($oldPath)
        ->and(Storage::disk('public')->exists($oldPath))->toBeFalse()
        ->and(Storage::disk('public')->exists($user->avatar))->toBeTrue();
});

test('banner upload + remove_banner flag wipes the file', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $banner = UploadedFile::fake()->image('hero.jpg', 1500, 500);

    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'banner' => $banner,
        ])
        ->assertRedirect(route('settings.edit'));

    $stored = $user->refresh()->banner;
    expect($stored)->not->toBeNull()
        ->and(Storage::disk('public')->exists($stored))->toBeTrue();

    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'remove_banner' => 1,
        ])
        ->assertRedirect(route('settings.edit'));

    $user->refresh();
    expect($user->banner)->toBeNull()
        ->and(Storage::disk('public')->exists($stored))->toBeFalse();
});

test('only owned cards can be pinned via the settings form', function () {
    $user = User::factory()->create();
    $owned = pcMakeCard('owned', 'Owned Pikachu');
    $foreign = pcMakeCard('foreign', 'Forbidden Mew');
    pcGiveCard($user, $owned);

    // Owned card is accepted.
    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'pinned_cards' => [$owned->id],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('settings.edit'));

    expect($user->refresh()->pinned_cards)->toBe([$owned->id]);

    // Non-owned card is rejected by the in:owned_card_ids rule.
    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'pinned_cards' => [$foreign->id],
        ])
        ->assertSessionHasErrors('pinned_cards.0');
});

test('pinning is capped at MAX_PINNED', function () {
    $user = User::factory()->create();

    $cards = collect(range(1, 7))->map(fn ($i) => pcMakeCard("cap-$i", "Cap Pikachu $i"));
    $cards->each(fn ($c) => pcGiveCard($user, $c));

    $this->actingAs($user)
        ->patch(route('settings.update'), [
            'pinned_cards' => $cards->pluck('id')->all(),
        ])
        ->assertSessionHasErrors('pinned_cards');
});

test('toggle-pin route adds and removes a card', function () {
    $user = User::factory()->create();
    $card = pcMakeCard('toggle', 'Toggle Pikachu');
    pcGiveCard($user, $card);

    $this->actingAs($user)
        ->from(route('collection.index'))
        ->post(route('settings.pin', $card->id))
        ->assertRedirect(route('collection.index'));

    expect($user->refresh()->pinned_cards)->toBe([$card->id]);

    $this->actingAs($user)
        ->from(route('collection.index'))
        ->post(route('settings.pin', $card->id))
        ->assertRedirect(route('collection.index'));

    expect($user->refresh()->pinned_cards)->toBe([]);
});

test('toggle-pin refuses cards the trainer does not own', function () {
    $user = User::factory()->create();
    $foreign = pcMakeCard('not-mine', 'Foreign Pikachu');

    $this->actingAs($user)
        ->from(route('collection.index'))
        ->post(route('settings.pin', $foreign->id))
        ->assertRedirect(route('collection.index'));

    expect($user->refresh()->pinned_cards)->toBeNull();
});

test('public profile renders the pinned showcase section', function () {
    $owner = User::factory()->create(['name' => 'Ash Ketchum']);
    $card = pcMakeCard('public-1', 'Profile Showpiece');
    pcGiveCard($owner, $card);
    $owner->forceFill(['pinned_cards' => [$card->id]])->save();

    $this->get(route('profiles.show', $owner))
        ->assertOk()
        ->assertSee('Pinned cards')
        ->assertSee('Profile Showpiece');
});

test('public profile renders avatar and banner URLs when set', function () {
    $owner = User::factory()->create();
    $owner->forceFill([
        'avatar' => 'avatars/'.$owner->id.'/a.png',
        'banner' => 'banners/'.$owner->id.'/b.png',
    ])->save();

    $this->get(route('profiles.show', $owner))
        ->assertOk()
        ->assertSee('/storage/avatars/'.$owner->id.'/a.png', escape: false)
        ->assertSee('/storage/banners/'.$owner->id.'/b.png', escape: false);
});

test('http(s) avatars from oauth are not rewritten through Storage::url', function () {
    $owner = User::factory()->create();
    $owner->forceFill([
        'avatar' => 'https://example.com/google-pic.png',
    ])->save();

    expect($owner->avatar_url)->toBe('https://example.com/google-pic.png');
});
