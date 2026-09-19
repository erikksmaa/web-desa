@props(['id', 'title', 'url' => null, 'linkLabel' => 'Lihat semua', 'description' => null, 'eyebrow' => null])
<div class="public-section-heading">
    <div>
        @if ($eyebrow)<p class="public-eyebrow">{{ $eyebrow }}</p>@endif
        <h2 id="{{ $id }}" class="h3 mb-0">{{ $title }}</h2>
        @if ($description)<p class="text-muted mb-0 mt-2">{{ $description }}</p>@endif
    </div>
    @if ($url)<a href="{{ $url }}" class="public-text-link">{{ $linkLabel }}<span class="visually-hidden"> {{ strtolower($title) }}</span><span aria-hidden="true"> →</span></a>@endif
</div>
