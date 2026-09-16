@php
    $textareaId = $name . '-' . uniqid();
    $hasError = $errors->has($name);
@endphp

<div class="admin-form-group">
    <label for="{{ $textareaId }}" class="form-label {{ ($required ?? false) ? 'required' : '' }}">
        {{ $label }}
        @if(!($required ?? false))
            <span class="text-muted fw-normal ms-1" style="font-size: 0.75rem; text-transform: none;">(Opsional)</span>
        @endif
    </label>
    <textarea
        id="{{ $textareaId }}"
        name="{{ $name }}"
        class="form-control {{ $hasError ? 'is-invalid' : '' }}"
        placeholder="{{ $placeholder ?? '' }}"
        rows="{{ $rows ?? 4 }}"
        {{ ($required ?? false) ? 'required' : '' }}
        {{ ($disabled ?? false) ? 'disabled' : '' }}
    >{{ old($name, $value ?? '') }}</textarea>
    @if($hasError)
    <div class="invalid-feedback">
        <i class="bx bx-error-circle"></i>
        {{ $errors->first($name) }}
    </div>
    @endif
    @if($help ?? false)
    <div class="form-text">{{ $help }}</div>
    @endif
</div>
