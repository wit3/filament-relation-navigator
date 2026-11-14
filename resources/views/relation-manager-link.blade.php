{{--
    "Open as page" above a relation manager's table on the record pages.

    Data: $url, $label.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div class="fi-relation-navigator-rm-link" style="display: flex; justify-content: flex-end;">
    <x-filament::link
        :href="$url"
        :icon="Heroicon::ArrowsPointingOut"
        icon-position="after"
        size="sm"
        color="gray"
    >
        {{ $label }}
    </x-filament::link>
</div>
