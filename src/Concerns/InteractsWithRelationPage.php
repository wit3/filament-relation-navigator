<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

/**
 * Optional, on a relation manager: choose whether it gets its own page, the
 * slug of that page, and the "Open as page" action in its table.
 *
 *     protected static bool $hasRelationPage = false;
 *
 *     protected static ?string $relationPageSlug = 'bills';
 *
 *     protected static bool $hasRelationPageLink = false;
 *
 * Plain static methods with the same names work as well, without the trait.
 */
trait InteractsWithRelationPage
{
    public static function hasRelationPage(): bool
    {
        // The properties are optional and declared by the class, not by the
        // trait: a trait property could not be redeclared with another value.
        return (bool) (self::readRelationPageProperty('hasRelationPage') ?? true);
    }

    public static function getRelationPageSlug(): ?string
    {
        $slug = self::readRelationPageProperty('relationPageSlug');

        return is_string($slug) ? $slug : null;
    }

    public static function hasRelationPageLink(): bool
    {
        return (bool) (self::readRelationPageProperty('hasRelationPageLink') ?? true);
    }

    private static function readRelationPageProperty(string $property): mixed
    {
        if (! property_exists(static::class, $property)) {
            return null;
        }

        return (new \ReflectionProperty(static::class, $property))->getValue();
    }
}
