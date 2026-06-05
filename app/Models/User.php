<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_ADMIN = 'admin';

    /**
     * Default public-profile visibility toggles. The settings page lets a
     * trainer flip each of these; merged over whatever is stored so new
     * keys get a sane default.
     */
    public const DEFAULT_PROFILE_SETTINGS = [
        'show_collection' => true,
        'show_chase'      => true,
        'show_socials'    => true,
        'show_bio'        => true,
        'show_auction'    => true,
        'allow_comments'  => true,
    ];

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'google_id', 'avatar', 'banner', 'phone', 'points', 'last_free_gacha_at',
        'bio', 'location', 'social_links', 'profile_settings', 'pinned_cards',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'profile_settings' => 'array',
            'pinned_cards' => 'array',
            'points' => 'integer',
            'last_free_gacha_at' => 'datetime',
        ];
    }

    /**
     * Public URL for the trainer's avatar. Three possible sources:
     *  - a Google OAuth URL (stored verbatim — starts with http)
     *  - a path on the public disk (uploaded via settings)
     *  - null (the view renders a prism initial fallback)
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->avatar));
    }

    /** Public URL for the trainer's profile banner, or null. */
    protected function bannerUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->banner));
    }

    private function resolveImageUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    /**
     * Cards the trainer has chosen to show off on their profile,
     * filtered to ones they actually own in their digital collection
     * (so unpulled cards can't get displayed even if the array goes
     * stale).
     */
    public function pinnedShowcase(int $limit = 6)
    {
        $ids = collect($this->pinned_cards ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $ownedIds = $this->collectionCards()
            ->whereIn('card_id', $ids)
            ->pluck('card_id')
            ->unique();

        $orderedOwnedIds = $ids->filter(fn ($id) => $ownedIds->contains($id))
            ->take($limit)
            ->values();

        if ($orderedOwnedIds->isEmpty()) {
            return collect();
        }

        return Card::query()
            ->whereIn('id', $orderedOwnedIds)
            ->get()
            ->sortBy(fn (Card $card) => $orderedOwnedIds->search($card->id))
            ->values();
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Whether this trainer still has their free daily gacha pull. The
     * first pull of each calendar day is free; the rest cost points.
     */
    public function freeGachaAvailable(): bool
    {
        return $this->last_free_gacha_at === null
            || ! $this->last_free_gacha_at->isToday();
    }

    /**
     * Profile visibility settings merged over defaults.
     */
    public function settings(): array
    {
        return array_merge(self::DEFAULT_PROFILE_SETTINGS, $this->profile_settings ?? []);
    }

    public function shows(string $key): bool
    {
        return (bool) ($this->settings()[$key] ?? false);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Cards this trainer is hunting for — the "chase cards" list.
     */
    public function wishlistedCards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'wishlists')
            ->withTimestamps();
    }

    /** Alias: chase cards are stored in the wishlist pivot. */
    public function chaseCards(): BelongsToMany
    {
        return $this->wishlistedCards();
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function listedAuctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'seller_id');
    }

    /**
     * Digital cards pulled from gacha — the trainer's collection.
     */
    public function digitalCards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'collection_cards')
            ->withPivot(['source', 'obtained_at'])
            ->withTimestamps();
    }

    public function collectionCards(): HasMany
    {
        return $this->hasMany(CollectionCard::class);
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    /** Comments left on this trainer's profile wall. */
    public function profileComments(): HasMany
    {
        return $this->hasMany(ProfileComment::class, 'profile_user_id')->latest();
    }

    // RegisteredUserController already sends OtpEmailVerificationNotification
    // on registration. Implementing MustVerifyEmail (so the `verified`
    // middleware kicks in) also enables Laravel's auto-discovered
    // SendEmailVerificationNotification listener, which would send a
    // second, signed-URL email on every Registered event. Suppress it.
    public function sendEmailVerificationNotification(): void
    {
        // intentionally a no-op — OTP flow owns email verification
    }
}
