{{--
    The list of the menu, shared by the presets: the optional search field,
    the items and the "no results" line.

    Data: $items, $maxHeight, $searchPlaceholder, $emptyLabel, $isSearchable,
    $showCheck (a check after the current page's label).
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div
    class="fi-relation-navigator-panel"
    x-data="{
        search: '',
        matches(label) {
            return label.toLowerCase().includes(this.search.trim().toLowerCase())
        },
        openFirstMatch() {
            [...this.$refs.list.querySelectorAll('[data-relation-navigator-label]')]
                .find((item) => this.matches(item.dataset.relationNavigatorLabel))
                ?.click()
        },
        get hasMatches() {
            return [...this.$refs.list.querySelectorAll('[data-relation-navigator-label]')]
                .some((item) => this.matches(item.dataset.relationNavigatorLabel))
        },
    }"
>
    @if ($isSearchable)
        <div class="fi-relation-navigator-search">
            <x-filament::input.wrapper :prefix-icon="Heroicon::MagnifyingGlass">
                <x-filament::input
                    type="search"
                    x-model="search"
                    data-relation-navigator-search
                    autocomplete="off"
                    :placeholder="$searchPlaceholder"
                    x-on:keydown.enter.prevent="openFirstMatch()"
                />
            </x-filament::input.wrapper>
        </div>
    @endif

    <x-filament::dropdown.list x-ref="list" :style="'max-height: ' . $maxHeight . '; overflow-y: auto;'">
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
                :data-relation-navigator-label="(string) $item->label"
                x-show="matches($el.dataset.relationNavigatorLabel)"
            >
                <span class="fi-relation-navigator-item-label">
                    {{ $item->label }}

                    @if ($showCheck && $item->isActive)
                        <x-filament::icon
                            :icon="Heroicon::Check"
                            class="fi-relation-navigator-check"
                        />
                    @endif
                </span>
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>

    @if ($isSearchable)
        <p
            class="fi-relation-navigator-empty"
            x-show="! hasMatches"
            x-cloak
        >
            {{ $emptyLabel }}
        </p>
    @endif
</div>
