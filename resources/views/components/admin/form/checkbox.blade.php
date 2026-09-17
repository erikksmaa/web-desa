@props(['name', 'label', 'checked' => false, 'help' => null, 'value' => 1, 'errorKey' => null])

@php
    $field = $errorKey ?: $name;
    $id = $attributes->get('id', 'field-'.preg_replace('/[^a-z0-9_-]+/i', '-', $name));
    $isChecked = filter_var(old($field, $checked), FILTER_VALIDATE_BOOL);
    $hasError = $errors->has($field);
@endphp

<div class="mb-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <div class="form-check form-switch">
        <input name="{{ $name }}" id="{{ $id }}" type="checkbox" value="{{ $value }}"
               @class(['form-check-input', 'is-invalid' => $hasError])
               @checked($isChecked)
               @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($help) aria-describedby="{{ $id }}-help" @endif
               {{ $attributes->except('id') }}>
        <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
        @if ($help)<div class="form-text" id="{{ $id }}-help">{{ $help }}</div>@endif
        @error($field)<div class="invalid-feedback" id="{{ $id }}-error" role="alert">{{ $message }}</div>@enderror
    </div>
</div>
