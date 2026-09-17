@props(['status'])

@php
    $normalized = strtolower(trim((string) $status));
    [$label, $variant] = match ($normalized) {
        'draft' => ['Draf', 'secondary'],
        'published' => ['Terbit', 'success'],
        'active' => ['Aktif', 'success'],
        'inactive' => ['Nonaktif', 'secondary'],
        'expired' => ['Kedaluwarsa', 'warning'],
        default => [$status ?: 'Tidak diketahui', 'light'],
    };
@endphp

<span {{ $attributes->class(['badge', "text-bg-$variant", 'border' => $variant === 'light']) }}>{{ $label }}</span>
