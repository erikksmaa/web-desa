@props(['title', 'message' => null, 'icon' => null])

<div {{ $attributes->class(['admin-empty-state', 'text-center']) }}>
    @if ($icon)<div class="admin-empty-icon" aria-hidden="true">{{ $icon }}</div>@endif
    <h3 class="admin-empty-title">{{ $title }}</h3>
    @if ($message)<p class="admin-empty-message mb-0">{{ $message }}</p>@endif
    @isset($action)<div class="mt-3">{{ $action }}</div>@endisset
</div>
