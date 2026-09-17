@props(['action', 'item', 'message' => 'Data yang dihapus tidak dapat dipulihkan.'])

<button type="button"
        {{ $attributes->class(['btn', 'btn-outline-danger', 'btn-sm']) }}
        data-bs-toggle="modal"
        data-bs-target="#confirmation-modal"
        data-confirm-action="{{ $action }}"
        data-confirm-item="{{ $item }}"
        data-confirm-message="{{ $message }}">
    {{ $slot->isEmpty() ? 'Hapus' : $slot }}
</button>
