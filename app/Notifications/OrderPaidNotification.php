<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing('items');

        return (new MailMessage)
            ->subject('Your PokeTrade order ' . $this->order->code . ' is confirmed')
            ->view('emails.order-paid', [
                'user'      => $notifiable,
                'order'     => $this->order,
                'actionUrl' => route('orders.show', $this->order->code),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'    => 'Payment confirmed for order ' . $this->order->code . '.',
            'url'        => route('orders.show', $this->order->code),
            'order_code' => $this->order->code,
            'total'      => (float) $this->order->total,
        ];
    }
}
