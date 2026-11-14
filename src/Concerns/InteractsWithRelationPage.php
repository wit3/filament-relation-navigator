<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

/**
 * Optional, on a relation manager: choose whether it gets its own page and
 * the slug of that page.
 *
 *     protected static bool $hasRelationPage = false;
 *
 *     protected static ?string $relationPageSlug = 'bills';
 *
 * Plain static methods with the same names work as well, without the trait.
 */
trait InteractsWithRelationPage
{
    public static function hasRelationPage(): bool
    {
        return static::$hasRelationPage ?? true;
    }

    public static function getRelationPageSlug(): ?string
    {
        return static::$relationPageSlug ?? null;
    }
}
