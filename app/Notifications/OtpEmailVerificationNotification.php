<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpEmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $otp,
        public int $expiresIn = 5, // minutes
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->view('emails.otp-verification', [
                'otp' => $this->otp,
                'type' => 'email_verification',
                'expiresIn' => $this->expiresIn,
            ]);
    }
}
