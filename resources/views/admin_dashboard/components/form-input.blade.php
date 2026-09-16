@php
    $inputId = $name . '-' . uniqid();
    $hasError = $errors->has($name);
@endphp

<div class="admin-form-group">
    <label for="{{ $inputId }}" class="form-label {{ ($required ?? false) ? 'required' : '' }}">
        {{ $label }}
        @if(!($required ?? false))
            <span class="text-muted fw-normal ms-1" style="font-size: 0.75rem; text-transform: none;">(Opsional)</span>
        @endif
    </label>
    <input
        type="{{ $type ?? 'text' }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value ?? '') }}"
        class="form-control {{ $hasError ? 'is-invalid' : '' }} {{ $class ?? '' }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ ($required ?? false) ? 'required' : '' }}
        {{ ($disabled ?? false) ? 'disabled' : '' }}
        @if($inputmode ?? false) inputmode="{{ $inputmode }}" @endif
        @if($maxlength ?? false) maxlength="{{ $maxlength }}" @endif
        @if($accept ?? false) accept="{{ $accept }}" @endif
        @if($min ?? false) min="{{ $min }}" @endif
        @if($max ?? false) max="{{ $max }}" @endif
    >
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
