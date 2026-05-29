<?php

namespace App\Policies;

use App\Models\ForumPost;
use App\Models\User;

class ForumPostPolicy
{
    public function update(User $user, ForumPost $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    public function delete(User $user, ForumPost $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }
}
