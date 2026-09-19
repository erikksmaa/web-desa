@props(['label', 'total', 'published', 'draft', 'publishedLabel' => 'Terbit', 'draftLabel' => 'Draf', 'url' => null])

<div {{ $attributes->class(['admin-stat-card', 'card', 'h-100']) }}>
    <div class="card-body">
        <p class="admin-metadata text-uppercase mb-2">@if ($url)<a href="{{ $url }}">{{ $label }}</a>@else{{ $label }}@endif</p>
        <p class="admin-stat-value mb-3">{{ number_format($total, 0, ',', '.') }}</p>
        <div class="d-flex flex-wrap gap-3 admin-stat-details">
            <span><strong>{{ number_format($published, 0, ',', '.') }}</strong> {{ $publishedLabel }}</span>
            <span><strong>{{ number_format($draft, 0, ',', '.') }}</strong> {{ $draftLabel }}</span>
        </div>
    </div>
</div>
