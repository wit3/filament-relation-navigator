{{--
    Preset "dropdown": the last breadcrumb opens a plain list.

    Data: $label (string | Htmlable), $items (array<RelationNavigatorItem>),
    $maxHeight, $heading, $searchPlaceholder, $emptyLabel, $isSearchable.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<x-filament::dropdown
    placement="bottom-start"
    :max-height="$maxHeight"
    teleport
    class="fi-relation-navigator fi-relation-navigator-preset-dropdown"
>
    <x-slot name="trigger">
        <button
            type="button"
            class="fi-relation-navigator-trigger"
            aria-label="{{ __('filament-relation-navigator::navigator.open') }}"
        >
            <span>{{ $label }}</span>

            <x-filament::icon
                :icon="Heroicon::ChevronDown"
                class="fi-relation-navigator-trigger-icon"
            />
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @foreach ($items as $item)
            <x-filament::dropdown.list.item
                tag="a"
                :href="$item->url"
                :icon="$item->icon"
                :badge="$item->badge"
                :badge-color="$item->badgeColor ?? 'primary'"
                :color="$item->isActive ? 'primary' : 'gray'"
                :aria-current="$item->isActive ? 'page' : null"
                :class="$item->isActive ? 'fi-relation-navigator-item fi-active' : 'fi-relation-navigator-item'"
            >
                {{ $item->label }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
