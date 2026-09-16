@php
    $toast = collect([
        'success' => session('toastSuccess') ?? session('success'),
        'error' => session('toastError') ?? session('error'),
        'info' => session('toastInfo'),
        'warning' => session('toastWarning'),
    ])->filter()->first(fn ($message) => filled($message));

    $toastType = collect([
        'success' => session('toastSuccess') ?? session('success'),
        'error' => session('toastError') ?? session('error'),
        'info' => session('toastInfo'),
        'warning' => session('toastWarning'),
    ])->filter()->keys()->first();
@endphp

@if($toast)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            iziToast.{{ $toastType }}({
                title: @json(ucfirst($toastType)),
                message: @json($toast),
                position: 'topRight',
                timeout: 4500
            });
        });
    </script>
@endif
