{{--
    Preset "searchable": the last breadcrumb stays text, a caret next to it
    opens a panel with a heading, a search field and a check on the current
    page.

    Data: $label (string | Htmlable), $items (array<RelationNavigatorItem>),
    $maxHeight, $heading, $searchPlaceholder, $emptyLabel, $isSearchable.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<span class="fi-relation-navigator fi-relation-navigator-preset-searchable">
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
                x-on:click="setTimeout(() => document.querySelector('[data-relation-navigator-search]:not([hidden])')?.focus(), 60)"
            >
                <x-filament::icon
                    :icon="Heroicon::ChevronDown"
                    class="fi-relation-navigator-trigger-icon"
                />
            </button>
        </x-slot>

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
            <div class="fi-relation-navigator-heading">
                {{ $heading }}
            </div>

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

                            @if ($item->isActive)
                                <x-filament::icon
                                    :icon="Heroicon::Check"
                                    class="fi-relation-navigator-check"
                                />
                            @endif
                        </span>
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>

            <p
                class="fi-relation-navigator-empty"
                x-show="! hasMatches"
                x-cloak
            >
                {{ $emptyLabel }}
            </p>
        </div>
    </x-filament::dropdown>
</span>
