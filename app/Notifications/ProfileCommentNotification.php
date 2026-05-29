<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfileCommentNotification extends Notification
{
    use Queueable;

    public function __construct(public User $author)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->author->name . ' left a comment on your trainer wall.',
            'url' => route('profiles.show', $notifiable),
            'profile_comment' => true,
        ];
    }
}
