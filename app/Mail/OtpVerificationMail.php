<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public int $expiresIn; // minutes

    public function __construct(string $otp, int $expiresIn = 10)
    {
        $this->otp = $otp;
        $this->expiresIn = $expiresIn;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Reset OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
            with: [
                'otp' => $this->otp,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }
}
