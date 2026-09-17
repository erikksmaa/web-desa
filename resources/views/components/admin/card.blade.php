@props(['title' => null])

<section {{ $attributes->class(['admin-card', 'card', 'h-100']) }}>
    @if ($title || isset($actions))
        <div class="card-header d-flex align-items-center justify-content-between gap-3">
            @if ($title)<h2 class="admin-card-title mb-0">{{ $title }}</h2>@endif
            @isset($actions)<div>{{ $actions }}</div>@endisset
        </div>
    @endif
    <div class="card-body">{{ $slot }}</div>
    @isset($footer)<div class="card-footer">{{ $footer }}</div>@endisset
</section>
