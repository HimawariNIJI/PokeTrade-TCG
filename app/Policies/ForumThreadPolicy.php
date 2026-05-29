<?php

namespace App\Policies;

use App\Models\ForumThread;
use App\Models\User;

class ForumThreadPolicy
{
    /** Author or admin may edit the opening post. */
    public function update(User $user, ForumThread $thread): bool
    {
        return $user->id === $thread->user_id || $user->isAdmin();
    }

    public function delete(User $user, ForumThread $thread): bool
    {
        return $user->id === $thread->user_id || $user->isAdmin();
    }

    /** Pin / lock are moderator-only. */
    public function moderate(User $user, ForumThread $thread): bool
    {
        return $user->isAdmin();
    }

    /** Replies are allowed unless the thread is locked (admins bypass). */
    public function reply(User $user, ForumThread $thread): bool
    {
        return ! $thread->locked || $user->isAdmin();
    }
}
