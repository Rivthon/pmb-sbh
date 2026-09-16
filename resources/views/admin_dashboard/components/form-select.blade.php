@php
    $selectId = $name . '-' . uniqid();
    $hasError = $errors->has($name);
    $selectedValue = old($name, $selected ?? '');
@endphp

<div class="admin-form-group">
    <label for="{{ $selectId }}" class="form-label {{ ($required ?? false) ? 'required' : '' }}">
        {{ $label }}
        @if(!($required ?? false))
            <span class="text-muted fw-normal ms-1" style="font-size: 0.75rem; text-transform: none;">(Opsional)</span>
        @endif
    </label>
    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        class="form-select {{ $hasError ? 'is-invalid' : '' }} {{ $class ?? '' }}"
        {{ ($required ?? false) ? 'required' : '' }}
        {{ ($disabled ?? false) ? 'disabled' : '' }}
    >
        <option value="">{{ $placeholder ?? 'Pilih...' }}</option>
        @foreach($options as $option)
            @php
                // Support both array format and Eloquent collection
                $optValue = is_array($option) ? ($option['value'] ?? $option['id'] ?? '') : ($option->id ?? $option->value ?? '');
                $optLabel = is_array($option) ? ($option['label'] ?? $option['name'] ?? $option['nama'] ?? '') : ($option->name ?? $option->nama ?? $option->label ?? '');
            @endphp
            <option value="{{ $optValue }}" {{ (string) $selectedValue === (string) $optValue ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
    @if($hasError)
    <div class="invalid-feedback">
        <i class="bx bx-error-circle"></i>
        {{ $errors->first($name) }}
    </div>
    @endif
</div>
