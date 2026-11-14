{{--
    Attributes for the button that opens the menu: a tooltip with the
    heading and the shortcut keys, and the shortcut itself. Include it
    inside the button tag:

        <button type="button" @include('filament-relation-navigator::menu.partials.trigger-attributes')>

    Data: $heading, $shortcut (e.g. 'alt+shift+r', or null), $shortcutKeys
    (['mac' => [...], 'other' => [...]]), $withTooltip (default true).

    No Blade conditionals here: Livewire would add its block markers inside
    the tag. The shortcut matches the physical key, outside text fields.
--}}
@php
    use Illuminate\Support\Js;

    $withTooltip ??= true;
    $shortcut ??= null;
    $shortcutKeys ??= ['mac' => [], 'other' => []];

    $keycaps = fn (array $keys): string => collect($keys)
        ->map(fn (string $key): string => '<kbd class="fi-relation-navigator-kbd" style="display:inline-block;min-width:1.25em;margin-inline-start:0.25em;padding:0 0.35em;border-radius:0.25rem;font:inherit;font-size:0.85em;text-align:center;background:rgb(127 127 127 / 0.2)">'.e($key).'</kbd>')
        ->implode('');

    $triggerAttributes = [];

    if ($withTooltip) {
        // The tooltip allows HTML for the keycaps: the heading is always
        // escaped, even when it is an Htmlable.
        $tooltip = 'content: '.Js::from(e((string) $heading))
            .' + (/Mac|iPhone|iPad/.test(navigator.userAgent) ? '.Js::from($keycaps($shortcutKeys['mac'])).' : '.Js::from($keycaps($shortcutKeys['other'])).'),'
            .' theme: $store.theme, allowHTML: true';

        $triggerAttributes[] = 'x-tooltip="{ '.e($tooltip).' }"';
    }

    if (filled($shortcut)) {
        // `mod` is ⌘ on a Mac and Ctrl elsewhere: declare both.
        $ariaShortcut = collect(str_contains($shortcut, 'mod') ? [str_replace('mod', 'meta', $shortcut), str_replace('mod', 'ctrl', $shortcut)] : [$shortcut])
            ->map(fn (string $combination): string => collect(explode('+', $combination))
                ->map(fn (string $key): string => ['command' => 'Meta', 'cmd' => 'Meta', 'meta' => 'Meta', 'ctrl' => 'Control', 'control' => 'Control', 'alt' => 'Alt', 'option' => 'Alt', 'shift' => 'Shift'][$key] ?? strtoupper($key))
                ->implode('+'))
            ->implode(' ');

        // Every menu on the page (breadcrumbs, small screens) listens: only
        // the visible one opens.
        $condition = \Wit3\FilamentRelationNavigator\RelationNavigator::getShortcutCondition($shortcut);

        if ($condition !== null) {
            // Filament's dropdown opens on mousedown: call its own open()
            // (Filament 4 and 5), then focus the search field if there is one.
            $open = "(typeof \$data.open === 'function' ? \$data.open(\$el) : \$el.click()), setTimeout(() => \$refs.panel?.querySelector('[data-relation-navigator-search]')?.focus(), 80)";

            $triggerAttributes[] = 'x-on:keydown.window="'.e($condition.' && $el.offsetParent !== null && ($event.preventDefault(), '.$open.')').'"';
        }

        $triggerAttributes[] = 'aria-keyshortcuts="'.e($ariaShortcut).'"';
    }
@endphp
{!! implode(' ', $triggerAttributes) !!}
