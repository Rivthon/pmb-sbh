@php
    $type = $type ?? 'text';
    $value = $value ?? null;
    $required = $required ?? false;
    $help = $help ?? null;
    $placeholder = $placeholder ?? null;
    $inputmode = $inputmode ?? null;
    $maxlength = $maxlength ?? null;
    $class = 'form-control' . ($errors->has($name) ? ' is-invalid' : '');
@endphp

<div class="student-field">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($inputmode) inputmode="{{ $inputmode }}" @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($required) required @endif
        class="{{ $class }}">
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        @if($help)<div class="form-text">{{ $help }}</div>@endif
    @enderror
</div>
