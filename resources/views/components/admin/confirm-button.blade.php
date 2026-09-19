@props(['action', 'item', 'message' => 'Data yang dihapus tidak dapat dipulihkan.'])

<button type="button"
        {{ $attributes->class(['btn', 'btn-outline-danger', 'btn-sm']) }}
        aria-label="Hapus {{ $item }}"
        data-delete-action="{{ $action }}"
        data-delete-item="{{ $item }}"
        data-delete-message="{{ $message }}">
    {{ $slot->isEmpty() ? 'Hapus' : $slot }}
</button>
