@props(['name', 'label', 'help' => null, 'existing' => null, 'preview' => false, 'errorKey' => null])

@php
    $field = $errorKey ?: $name;
    $id = $attributes->get('id', 'field-'.preg_replace('/[^a-z0-9_-]+/i', '-', $name));
    $hasError = $errors->has($field);
    $describedBy = collect([$help ? $id.'-help' : null, $existing ? $id.'-existing' : null, $id.'-filename', $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $id }}">{{ $label }}@if ($attributes->get('required', false)) <span class="required-indicator" aria-hidden="true">*</span><span class="visually-hidden"> wajib</span>@endif</label>
    <input name="{{ $name }}" id="{{ $id }}" type="file"
           @class(['form-control', 'is-invalid' => $hasError])
           data-file-input
           data-file-name-target="#{{ $id }}-filename"
           @if ($preview) data-image-preview-target="#{{ $id }}-preview" @endif
           @if ($hasError) aria-invalid="true" @endif
           aria-describedby="{{ $describedBy }}"
           {{ $attributes->except('id') }}>
    <div class="form-text" id="{{ $id }}-filename" aria-live="polite">Belum ada file baru dipilih.</div>
    @if ($help)<div class="form-text" id="{{ $id }}-help">{{ $help }}</div>@endif
    @if ($existing)<div class="form-text" id="{{ $id }}-existing">File saat ini: {{ $existing }}</div>@endif
    @error($field)<div class="invalid-feedback" id="{{ $id }}-error" role="alert">{{ $message }}</div>@enderror
    @if ($preview)
        <img class="admin-image-preview mt-3" id="{{ $id }}-preview" alt="Pratinjau gambar yang dipilih" hidden>
    @endif
</div>
