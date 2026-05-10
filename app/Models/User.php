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

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'google_id', 'avatar', 'phone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlistedCards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'wishlists')->withTimestamps();
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function listedAuctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'seller_id');
    }

    public function tradesSent(): HasMany
    {
        return $this->hasMany(Trade::class, 'sender_id');
    }

    public function tradesReceived(): HasMany
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }
}
