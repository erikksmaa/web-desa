@props(['action', 'clearUrl' => null])

<form method="GET" action="{{ $action }}" {{ $attributes->class(['admin-filter-form', 'row', 'g-3', 'align-items-end']) }}>
    @if (request()->filled('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    {{ $slot }}
    <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary">Terapkan</button>
        @if ($clearUrl)<a href="{{ $clearUrl }}" class="btn btn-outline-secondary">Atur ulang</a>@endif
    </div>
</form>
