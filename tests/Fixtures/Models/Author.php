<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function publishedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('is_published', true);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
