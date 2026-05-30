<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WishlistAuctionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Auction $auction)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('A card from your wishlist is on auction!')
            ->view('emails.wishlist-auction', [
                'user'      => $notifiable,
                'auction'   => $this->auction,
                'cardName'  => $this->auction->card->name,
                'actionUrl' => route('auctions.show', $this->auction->id),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'    => $this->auction->card->name . ' is now up for auction!',
            'auction_id' => $this->auction->id,
            'card_slug'  => $this->auction->card->slug,
        ];
    }
}
