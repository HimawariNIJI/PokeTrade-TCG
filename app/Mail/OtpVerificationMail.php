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
    public string $type; // 'password_reset' atau 'email_verification'
    public int $expiresIn; // minutes

    // Menambahkan parameter $type (default-nya 'password_reset')
    public function __construct(string $otp, string $type = 'password_reset', int $expiresIn = 10)
    {
        $this->otp = $otp;
        $this->type = $type;
        $this->expiresIn = $expiresIn;
    }

    public function envelope(): Envelope
    {
        // Menentukan subject secara dinamis berdasarkan type
        $subject = $this->type === 'email_verification' 
            ? 'Verify Your Email Address' 
            : 'Your Password Reset OTP';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
            with: [
                'otp' => $this->otp,
                'type' => $this->type,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }
}