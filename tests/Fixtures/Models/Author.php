<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property ?int $team_id
 */
class Author extends Model
{
    protected $guarded = [];

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return HasMany<Comment, $this> */
    public function publishedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('is_published', true);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
