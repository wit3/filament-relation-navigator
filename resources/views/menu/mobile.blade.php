{{--
    The menu on small screens, above the page heading: Filament hides the
    breadcrumbs there. Same data as the presets.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

{{-- Inline, so it works even when `filament:assets` was not run after an update. --}}
<style>
    .fi-relation-navigator-mobile {
        margin-bottom: 0.5rem;
    }

    .fi-relation-navigator-mobile-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        border-radius: 0.5rem;
        padding: 0.375rem 0.625rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: inherit;
        background-color: rgb(0 0 0 / 0.04);
        box-shadow: inset 0 0 0 1px rgb(0 0 0 / 0.08);
        cursor: pointer;
    }

    :is(.dark) .fi-relation-navigator-mobile-trigger {
        background-color: rgb(255 255 255 / 0.05);
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.1);
    }

    @media (min-width: 40rem) {
        .fi-relation-navigator-mobile {
            display: none;
        }
    }
</style>

<div class="fi-relation-navigator-mobile">
    <x-filament::dropdown
        placement="bottom-start"
        width="xs"
        teleport
    >
        <x-slot name="trigger">
            <button
                type="button"
                class="fi-relation-navigator-mobile-trigger"
                aria-label="{{ __('filament-relation-navigator::navigator.open') }}"
                @include('filament-relation-navigator::menu.partials.trigger-attributes', ['withTooltip' => false])
                @if ($isSearchable)
                    x-on:click="setTimeout(() => $refs.panel?.querySelector('[data-relation-navigator-search]')?.focus(), 60)"
                @endif
            >
                <span>{{ $label }}</span>

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
</div>
