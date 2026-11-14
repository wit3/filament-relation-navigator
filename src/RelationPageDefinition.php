<?php

namespace Wit3\FilamentRelationNavigator;

use Closure;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * One entry of a resource's `getRelations()` that gets its own page.
 */
final class RelationPageDefinition
{
    private const MEMO_KEY = 'filament-relation-navigator.memo';

    /**
     * @var list<string>
     */
    private const LAZY_BADGE_METHODS = ['isBadgeLazyLoaded', 'isBadgeDeferred'];

    /**
     * @param  class-string<RelationManager> | RelationGroup | RelationManagerConfiguration  $manager
     */
    public function __construct(
        public readonly string $slug,
        public readonly string|int $key,
        public readonly string|RelationGroup|RelationManagerConfiguration $manager,
    ) {}

    public function isGroup(): bool
    {
        return $this->manager instanceof RelationGroup;
    }

    /**
     * @return class-string<RelationManager> | null
     */
    public function getManagerClass(): ?string
    {
        return RelationNavigator::normalizeManagerClass($this->manager);
    }

    public function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($this->manager instanceof RelationGroup) {
            return (bool) count($this->getGroup($ownerRecord, $pageClass)->getManagers());
        }

        return $this->managerClass()::canViewForRecord($ownerRecord, $pageClass);
    }

    public function getGroup(Model $ownerRecord, string $pageClass): RelationGroup
    {
        if (! $this->manager instanceof RelationGroup) {
            throw new LogicException("The relation page [{$this->slug}] is not a relation group.");
        }

        return (clone $this->manager)->ownerRecord($ownerRecord)->pageClass($pageClass);
    }

    /**
     * @return class-string<RelationManager>
     */
    private function managerClass(): string
    {
        $managerClass = $this->getManagerClass();

        if ($managerClass === null) {
            throw new LogicException("The relation page [{$this->slug}] is a relation group, not a relation manager.");
        }

        return $managerClass;
    }

    public function getTitle(Model $ownerRecord, string $pageClass): string
    {
        if ($this->manager instanceof RelationGroup) {
            return $this->getGroup($ownerRecord, $pageClass)->getLabel();
        }

        return $this->managerClass()::getTitle($ownerRecord, $pageClass);
    }

    public function getIcon(Model $ownerRecord, string $pageClass): mixed
    {
        if ($this->manager instanceof RelationGroup) {
            return $this->getGroup($ownerRecord, $pageClass)->getIcon();
        }

        return $this->managerClass()::getIcon($ownerRecord, $pageClass);
    }

    /**
     * Whether the relation manager's tab badge is lazy (Filament's
     * `$isBadgeLazyLoaded`, `$isBadgeDeferred` on Filament 4.13): the menu
     * then loads it when it opens, too. Never for a relation group.
     */
    public function isBadgeLazy(Model $ownerRecord, string $pageClass): bool
    {
        $managerClass = $this->getManagerClass();

        if ($managerClass === null) {
            return false;
        }

        // isBadgeLazyLoaded() from Filament 4.16, isBadgeDeferred() before.
        foreach (self::LAZY_BADGE_METHODS as $method) {
            if (method_exists($managerClass, $method)) {
                return (bool) $managerClass::$method($ownerRecord, $pageClass);
            }
        }

        return false;
    }

    /**
     * Badges often count records: within one request (menu, sub-navigation)
     * each one is computed once.
     */
    public function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $this->remember('badge', $ownerRecord, $pageClass, function () use ($ownerRecord, $pageClass): ?string {
            if ($this->manager instanceof RelationGroup) {
                return $this->getGroup($ownerRecord, $pageClass)->getBadge();
            }

            return $this->managerClass()::getBadge($ownerRecord, $pageClass);
        });
    }

    /**
     * @return string | array<string> | null
     */
    public function getBadgeColor(Model $ownerRecord, string $pageClass): string|array|null
    {
        return $this->remember('badgeColor', $ownerRecord, $pageClass, function () use ($ownerRecord, $pageClass): string|array|null {
            if ($this->manager instanceof RelationGroup) {
                return $this->getGroup($ownerRecord, $pageClass)->getBadgeColor();
            }

            return $this->managerClass()::getBadgeColor($ownerRecord, $pageClass);
        });
    }

    /**
     * A per-request memo, kept on the request so it never outlives it (Octane).
     */
    private function remember(string $what, Model $ownerRecord, string $pageClass, Closure $callback): mixed
    {
        $attributes = request()->attributes;
        $memo = $attributes->get(self::MEMO_KEY, []);
        $key = implode('|', [$what, $this->slug, $ownerRecord::class, $ownerRecord->getKey(), $pageClass]);

        if (! array_key_exists($key, $memo)) {
            $memo[$key] = $callback();
            $attributes->set(self::MEMO_KEY, $memo);
        }

        return $memo[$key];
    }
}
