<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $guarded = [];

    /** @return HasMany<Author, $this> */
    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
