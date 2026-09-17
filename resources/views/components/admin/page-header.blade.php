@props(['title', 'subtitle' => null])

<div {{ $attributes->class(['admin-page-header', 'd-sm-flex', 'align-items-start', 'justify-content-between', 'gap-3', 'mb-4']) }}>
    <div>
        <h1 class="admin-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="admin-page-subtitle mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="admin-page-actions mt-3 mt-sm-0">{{ $actions }}</div>
    @endisset
</div>
