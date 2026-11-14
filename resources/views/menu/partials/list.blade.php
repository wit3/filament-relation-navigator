{{--
    The list of the menu, shared by the presets: the optional search field,
    the items and the "no results" line.

    Data: $items, $maxHeight, $searchPlaceholder, $emptyLabel, $isSearchable,
    $showCheck (a check after the current page's label), $hasSeparator
    (record pages and relation pages in two blocks; optional).

    Keyboard: from the search field, Down moves to the first item; Up and
    Down move between the items, Up from the first one goes back to the
    search field; Enter in the search field opens the first match.
--}}
@php
    use Filament\Support\Icons\Heroicon;

    $hasSeparator ??= false;
    $showCheck ??= false;

    $blocks = $hasSeparator
        ? array_values(array_filter([
            array_values(array_filter($items, fn ($item): bool => ! $item->isRelationPage())),
            array_values(array_filter($items, fn ($item): bool => $item->isRelationPage())),
        ]))
        : [$items];
@endphp

<div
    class="fi-relation-navigator-panel"
    x-data="{
        search: '',
        matches(label) {
            return label.toLowerCase().includes(this.search.trim().toLowerCase())
        },
        visibleItems() {
            return [...this.$refs.lists.querySelectorAll('[data-relation-navigator-label]')]
                .filter((item) => this.matches(item.dataset.relationNavigatorLabel))
        },
        openFirstMatch() {
            this.visibleItems()[0]?.click()
        },
        focusFirstMatch() {
            this.visibleItems()[0]?.focus()
        },
        move(step) {
            const items = this.visibleItems()
            const index = items.indexOf(document.activeElement)
            const next = index + step

            if (next < 0) {
                this.$refs.search?.focus()

                return
            }

            items[Math.min(next, items.length - 1)]?.focus()
        },
        hasMatchesIn(element) {
            return [...element.querySelectorAll('[data-relation-navigator-label]')]
                .some((item) => this.matches(item.dataset.relationNavigatorLabel))
        },
        get hasMatches() {
            return this.visibleItems().length > 0
        },
    }"
>
    @if ($isSearchable)
        <div class="fi-relation-navigator-search">
            <x-filament::input.wrapper :prefix-icon="Heroicon::MagnifyingGlass">
                <x-filament::input
                    type="search"
                    x-model="search"
                    x-ref="search"
                    data-relation-navigator-search
                    autocomplete="off"
                    :placeholder="$searchPlaceholder"
                    x-on:keydown.enter.prevent="openFirstMatch()"
                    x-on:keydown.down.prevent.stop="focusFirstMatch()"
                />
            </x-filament::input.wrapper>
        </div>
    @endif

    <div
        x-ref="lists"
        class="fi-relation-navigator-lists"
        style="max-height: {{ $maxHeight }}; overflow-y: auto;"
        x-on:keydown.down.prevent.stop="move(1)"
        x-on:keydown.up.prevent.stop="move(-1)"
    >
        @foreach ($blocks as $block)
            {{-- The line between blocks only when the block above shows something. --}}
            <x-filament::dropdown.list
                x-show="hasMatchesIn($el)"
                :x-bind:style="$loop->first ? null : '$el.previousElementSibling && hasMatchesIn($el.previousElementSibling) ? {} : { borderTopWidth: \'0px\' }'"
            >
                @foreach ($block as $item)
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
        @endforeach
    </div>

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
