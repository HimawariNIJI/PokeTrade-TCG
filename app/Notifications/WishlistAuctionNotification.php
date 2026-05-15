<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WishlistAuctionNotification extends Notification
{
    use Queueable;

    public function __construct(public Auction $auction)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->auction->card->name . ' is now up for auction!',
            'auction_id' => $this->auction->id,
            'card_slug' => $this->auction->card->slug,
        ];
    }
}