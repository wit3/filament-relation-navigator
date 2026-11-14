{{-- A list partial as published before deferred badges: it renders $item->badge only. --}}
<div class="fi-relation-navigator-panel">
    @foreach ($items as $item)
        <a href="{{ $item->url }}" data-relation-navigator-label="{{ $item->label }}">{{ $item->label }} {{ $item->badge }}</a>
    @endforeach
</div>
