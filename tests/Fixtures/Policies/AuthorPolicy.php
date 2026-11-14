<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Policies;

use Illuminate\Database\Eloquent\Model;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;

/**
 * Registered by the tests that need it: an author named "Secret" cannot be
 * viewed or edited, one named "Read only" cannot be edited.
 */
class AuthorPolicy
{
    public function viewAny(Model $user): bool
    {
        return true;
    }

    public function view(Model $user, Author $author): bool
    {
        return $author->name !== 'Secret';
    }

    public function create(Model $user): bool
    {
        return true;
    }

    public function update(Model $user, Author $author): bool
    {
        return ! in_array($author->name, ['Secret', 'Read only'], true);
    }

    public function delete(Model $user, Author $author): bool
    {
        return true;
    }

    public function deleteAny(Model $user): bool
    {
        return true;
    }
}
