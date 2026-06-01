<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpPasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $otp,
        public int $expiresIn = 10, // minutes
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Reset OTP')
            ->view('emails.otp-verification', [
                'otp' => $this->otp,
                'type' => 'password_reset',
                'expiresIn' => $this->expiresIn,
            ]);
    }
}
