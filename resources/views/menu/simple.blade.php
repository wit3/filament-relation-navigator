{{--
    Preset "simple": the last breadcrumb itself opens the list.

    Data: $label (string | Htmlable), $items (array<RelationNavigatorItem>),
    $heading, $searchPlaceholder, $emptyLabel, $maxHeight, $isSearchable.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<x-filament::dropdown
    placement="bottom-start"
    :width="$isSearchable ? 'xs' : null"
    teleport
    class="fi-relation-navigator fi-relation-navigator-preset-simple"
>
    <x-slot name="trigger">
        <button
            type="button"
            class="fi-relation-navigator-trigger"
            aria-label="{{ __('filament-relation-navigator::navigator.open') }}"
            @if ($isSearchable)
                x-on:click="setTimeout(() => document.querySelector('[data-relation-navigator-search]')?.focus(), 60)"
            @endif
        >
            <span>{{ $label }}</span>

            <x-filament::icon
                :icon="Heroicon::ChevronDown"
                class="fi-relation-navigator-trigger-icon"
            />
        </button>
    </x-slot>

    @include('filament-relation-navigator::menu.partials.list', ['showCheck' => false])
</x-filament::dropdown>
