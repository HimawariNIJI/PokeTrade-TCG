<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ForumCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'accent', 'position'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function posts(): HasManyThrough
    {
        return $this->hasManyThrough(ForumPost::class, ForumThread::class);
    }
}
