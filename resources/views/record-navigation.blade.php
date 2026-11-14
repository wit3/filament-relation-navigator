{{--
    Previous and next record, before the header actions.

    Data: $previous, $next (RelationNavigatorItem or null; label is the
    record title), $previousLabel, $nextLabel.
--}}
@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div class="fi-relation-navigator-record-navigation" style="display: flex; align-items: center; gap: 0.25rem;">
    @foreach ([[$previous, $previousLabel, Heroicon::ChevronLeft], [$next, $nextLabel, Heroicon::ChevronRight]] as [$neighbour, $label, $icon])
        <x-filament::icon-button
            :tag="$neighbour ? 'a' : 'button'"
            :href="$neighbour?->url"
            :icon="$icon"
            color="gray"
            :disabled="! $neighbour"
            :label="$label"
            :tooltip="$neighbour ? $label.': '.$neighbour->label : $label"
            :data-relation-navigator-neighbour="$neighbour ? $neighbour->key : null"
        />
    @endforeach
</div>
