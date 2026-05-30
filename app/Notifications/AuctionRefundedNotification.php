<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Auction $auction, public Bid $bid)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->auction->loadMissing('card');

        return (new MailMessage)
            ->subject('Your bid on ' . $this->auction->card->name . ' was refunded')
            ->view('emails.auction-refunded', [
                'user'      => $notifiable,
                'auction'   => $this->auction,
                'bid'       => $this->bid,
                'cardName'  => $this->auction->card->name,
                'amount'    => $this->bid->amount,
                'actionUrl' => route('auctions.show', $this->auction->id),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->auction->loadMissing('card');

        return [
            'message'    => 'Your bid on ' . $this->auction->card->name . ' was refunded (you did not win).',
            'url'        => route('auctions.show', $this->auction->id),
            'auction_id' => $this->auction->id,
            'bid_id'     => $this->bid->id,
            'amount'     => (float) $this->bid->amount,
        ];
    }
}
