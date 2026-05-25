<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'allow_comments'  => true,
    ];

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'google_id', 'avatar', 'phone', 'points',
        'bio', 'location', 'social_links', 'profile_settings',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'profile_settings' => 'array',
            'points' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
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
}
