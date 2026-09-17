@props(['label', 'route' => null, 'patterns' => []])

@php
    $isActive = $route && request()->routeIs(...((array) $patterns ?: [$route]));
@endphp

@if ($route)
    <a {{ $attributes->class(['admin-nav-link', 'active' => $isActive]) }}
       href="{{ route($route) }}"
       @if ($isActive) aria-current="page" @endif>
        {{ $label }}
    </a>
@else
    <span {{ $attributes->class(['admin-nav-link', 'disabled']) }} aria-disabled="true">
        {{ $label }}
        <span class="visually-hidden"> — belum tersedia</span>
    </span>
@endif
