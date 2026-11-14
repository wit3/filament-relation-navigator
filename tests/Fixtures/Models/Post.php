<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $title
 */
class Post extends Model
{
    protected $guarded = [];

    /** @return BelongsTo<Author, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /** @return HasMany<Revision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class);
    }
}
