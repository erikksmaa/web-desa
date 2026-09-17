@props(['name', 'label', 'options', 'value' => null, 'placeholder' => null, 'help' => null, 'errorKey' => null])

@php
    $field = $errorKey ?: $name;
    $id = $attributes->get('id', 'field-'.preg_replace('/[^a-z0-9_-]+/i', '-', $name));
    $selected = old($field, $value);
    $hasError = $errors->has($field);
    $describedBy = collect([$help ? $id.'-help' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $id }}">{{ $label }}@if ($attributes->has('required')) <span class="required-indicator" aria-hidden="true">*</span><span class="visually-hidden"> wajib</span>@endif</label>
    <select name="{{ $name }}" id="{{ $id }}"
            @class(['form-select', 'is-invalid' => $hasError])
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except('id') }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($help)<div class="form-text" id="{{ $id }}-help">{{ $help }}</div>@endif
    @error($field)<div class="invalid-feedback" id="{{ $id }}-error" role="alert">{{ $message }}</div>@enderror
</div>
