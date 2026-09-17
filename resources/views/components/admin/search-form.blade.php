@props(['action', 'placeholder' => 'Cari…', 'label' => 'Cari', 'id' => 'admin-search'])

@php
    $preserved = request()->except(['search', 'page']);
    $clearUrl = $action.($preserved ? '?'.http_build_query($preserved) : '');
@endphp

<form method="GET" action="{{ $action }}" {{ $attributes->class(['admin-search-form']) }}>
    @foreach ($preserved as $key => $value)
        @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach
    <label class="visually-hidden" for="{{ $id }}">{{ $label }}</label>
    <div class="input-group">
        <input class="form-control" id="{{ $id }}" type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}">
        <button class="btn btn-primary" type="submit">Cari</button>
        @if (request()->filled('search'))<a class="btn btn-outline-secondary" href="{{ $clearUrl }}">Hapus</a>@endif
    </div>
</form>
