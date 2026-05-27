<?php

namespace App\Jobs;

use App\Mail\WishlistAuctionMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWishlistAuctionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $cardName;

    /**
     * Pass the required data into the Job when it is dispatched.
     */
    public function __construct(User $user, $cardName)
    {
        $this->user = $user;
        $this->cardName = $cardName;
    }

    /**
     * The worker executes this method in the background.
     */
    public function handle(): void
    {
        // Send the email
        Mail::to($this->user->email)->send(new WishlistAuctionMail($this->cardName));
    }
}