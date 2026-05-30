<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Auction $auction, public ?Order $order = null)
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
            ->subject('You won the auction for ' . $this->auction->card->name . '!')
            ->view('emails.auction-won', [
                'user'      => $notifiable,
                'auction'   => $this->auction,
                'order'     => $this->order,
                'cardName'  => $this->auction->card->name,
                'amount'    => $this->auction->winning_amount,
                'actionUrl' => $this->order
                    ? route('orders.show', $this->order->code)
                    : route('auctions.show', $this->auction->id),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->auction->loadMissing('card');

        return [
            'message'    => 'You won the auction for ' . $this->auction->card->name . '!',
            'auction_id' => $this->auction->id,
            'amount'     => (float) $this->auction->winning_amount,
            'order_code' => $this->order?->code,
            'url'        => $this->order
                ? route('orders.show', $this->order->code)
                : route('auctions.show', $this->auction->id),
        ];
    }
}
