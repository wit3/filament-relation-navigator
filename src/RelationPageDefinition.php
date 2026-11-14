<?php

namespace Wit3\FilamentRelationNavigator;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Illuminate\Database\Eloquent\Model;

/**
 * One entry of a resource's `getRelations()` that gets its own page.
 */
final class RelationPageDefinition
{
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

        return $this->getManagerClass()::canViewForRecord($ownerRecord, $pageClass);
    }

    public function getGroup(Model $ownerRecord, string $pageClass): RelationGroup
    {
        /** @var RelationGroup $group */
        $group = clone $this->manager;

        return $group->ownerRecord($ownerRecord)->pageClass($pageClass);
    }

    public function getTitle(Model $ownerRecord, string $pageClass): string
    {
        if ($this->manager instanceof RelationGroup) {
            return $this->getGroup($ownerRecord, $pageClass)->getLabel();
        }

        return $this->getManagerClass()::getTitle($ownerRecord, $pageClass);
    }

    public function getIcon(Model $ownerRecord, string $pageClass): mixed
    {
        if ($this->manager instanceof RelationGroup) {
            return $this->getGroup($ownerRecord, $pageClass)->getIcon();
        }

        return $this->getManagerClass()::getIcon($ownerRecord, $pageClass);
    }

    public function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if ($this->manager instanceof RelationGroup) {
            return $this->getGroup($ownerRecord, $pageClass)->getBadge();
        }

        return $this->getManagerClass()::getBadge($ownerRecord, $pageClass);
    }

    /**
     * @return string | array<string> | null
     */
    public function getBadgeColor(Model $ownerRecord, string $pageClass): string|array|null
    {
        if ($this->manager instanceof RelationGroup) {
            return $this->getGroup($ownerRecord, $pageClass)->getBadgeColor();
        }

        return $this->getManagerClass()::getBadgeColor($ownerRecord, $pageClass);
    }
}
