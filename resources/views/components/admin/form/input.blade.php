@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'errorKey' => null])

@php
    $field = $errorKey ?: $name;
    $id = $attributes->get('id', 'field-'.preg_replace('/[^a-z0-9_-]+/i', '-', $name));
    $hasError = $errors->has($field);
    $describedBy = collect([$help ? $id.'-help' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $id }}">
        {{ $label }}
        @if ($attributes->has('required'))<span class="required-indicator" aria-hidden="true">*</span><span class="visually-hidden"> wajib</span>@endif
    </label>
    <input name="{{ $name }}" id="{{ $id }}" type="{{ $type }}"
           value="{{ $type === 'password' ? '' : old($field, $value) }}"
           @class(['form-control', 'is-invalid' => $hasError])
           @if ($hasError) aria-invalid="true" @endif
           @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
           {{ $attributes->except('id') }}>
    @if ($help)<div class="form-text" id="{{ $id }}-help">{{ $help }}</div>@endif
    @error($field)<div class="invalid-feedback" id="{{ $id }}-error" role="alert">{{ $message }}</div>@enderror
</div>
