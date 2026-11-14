{{--
    Preset "github": the last breadcrumb stays text, a caret next to it opens
    a panel with a heading and a check on the current page, like GitHub's
    repository switcher.

    Data: $label (string | Htmlable), $items (array<RelationNavigatorItem>),
    $heading, $searchPlaceholder, $emptyLabel, $maxHeight, $isSearchable.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<span class="fi-relation-navigator fi-relation-navigator-preset-github">
    <span class="fi-relation-navigator-label">{{ $label }}</span>

    <x-filament::dropdown
        placement="bottom-start"
        width="xs"
        teleport
    >
        <x-slot name="trigger">
            <button
                type="button"
                class="fi-relation-navigator-caret"
                aria-label="{{ __('filament-relation-navigator::navigator.open') }}"
                @if ($isSearchable)
                    x-on:click="setTimeout(() => $refs.panel?.querySelector('[data-relation-navigator-search]')?.focus(), 60)"
                @endif
            >
                <x-filament::icon
                    :icon="Heroicon::ChevronDown"
                    class="fi-relation-navigator-trigger-icon"
                />
            </button>
        </x-slot>

        <div class="fi-relation-navigator-heading">
            {{ $heading }}
        </div>

        @include('filament-relation-navigator::menu.partials.list', ['showCheck' => true])
    </x-filament::dropdown>
</span>
