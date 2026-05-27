<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WishlistAuctionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cardName;

    public function __construct($cardName)
    {
        $this->cardName = $cardName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "A card from your wishlist is on auction!");
    }

    public function content(): Content
    {
        // Ensure you create resources/views/emails/wishlist-auction.blade.php
        return new Content(view: 'emails.wishlist-auction'); 
    }
}