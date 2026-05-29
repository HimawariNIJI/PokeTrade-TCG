<?php

namespace App\Notifications;

use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ForumReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ForumThread $thread,
        public User $replier,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->replier->name . ' replied to your thread "' . $this->thread->title . '".',
            'url' => route('forums.thread', $this->thread),
            'thread_id' => $this->thread->id,
        ];
    }
}
