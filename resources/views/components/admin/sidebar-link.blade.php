@props(['label', 'icon' => 'circle', 'route' => null, 'patterns' => []])

@php
    $isActive = $route && request()->routeIs(...((array) $patterns ?: [$route]));
@endphp

@if ($route)
    <a {{ $attributes->class(['admin-nav-link', 'active' => $isActive]) }}
       href="{{ route($route) }}"
       title="{{ $label }}"
       @if ($isActive) aria-current="page" @endif>
        <x-admin.icon :name="$icon" /> <span class="admin-link-label">{{ $label }}</span>
    </a>
@else
    <span {{ $attributes->class(['admin-nav-link', 'disabled']) }} aria-disabled="true" title="{{ $label }} - belum tersedia">
        <x-admin.icon :name="$icon" /> <span class="admin-link-label">{{ $label }}</span>
        <span class="visually-hidden"> — belum tersedia</span>
    </span>
@endif
