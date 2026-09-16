@extends('admin_dashboard.layout.master')

@section('title', 'Scanner Kedatangan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Scanner Kedatangan</h4>
            <p class="text-muted mb-0">{{ $session->name }}</p>
        </div>
        <a href="{{ route('admin.pmb-queues.scan.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Pilih Sesi</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <div class="alert alert-info">Scan kartu peserta, kemudian cocokkan dengan berkas fisik yang dibawa. Data calon mahasiswa tidak ditampilkan di halaman ini.</div>
                    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    <form method="POST" action="{{ route('admin.pmb-queues.check-in', $session) }}">
                        @csrf
                        <label class="form-label" for="scanCode">Barcode / QR kartu tes</label>
                        <input id="scanCode" type="text" name="code" class="form-control form-control-lg mb-3" placeholder="Arahkan scanner barcode atau ketik kode" autocomplete="off" autofocus required>
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-outline-primary" type="button" id="startCamera"><i class="bx bx-camera me-1"></i> Buka Kamera</button>
                            <button class="btn btn-outline-secondary d-none" type="button" id="stopCamera">Tutup Kamera</button>
                        </div>
                        <div id="cameraReader" class="d-none mb-3"></div>
                        <button class="btn btn-primary btn-lg w-100" type="submit"><i class="bx bx-check-circle me-1"></i> Catat Kedatangan</button>
                    </form>
                    <div class="text-muted small mt-3">Setelah berhasil, peserta diarahkan ke tahap tes sesuai jalurnya. Berkas fisik diperiksa di loket oleh petugas.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('head')
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('scanCode');
    const reader = document.getElementById('cameraReader');
    const start = document.getElementById('startCamera');
    const stop = document.getElementById('stopCamera');
    let scanner = null;

    async function stopScanner() {
        if (!scanner) return;
        await scanner.stop();
        scanner.clear();
        scanner = null;
        reader.classList.add('d-none');
        stop.classList.add('d-none');
        start.classList.remove('d-none');
        input.focus();
    }

    start.addEventListener('click', async function () {
        if (!window.Html5Qrcode) {
            alert('Scanner kamera belum siap. Gunakan scanner barcode atau ketik kode peserta.');
            return;
        }

        reader.classList.remove('d-none');
        start.classList.add('d-none');
        stop.classList.remove('d-none');
        scanner = new Html5Qrcode('cameraReader');

        try {
            await scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, async function (decodedText) {
                input.value = decodedText;
                await stopScanner();
                input.form.submit();
            });
        } catch (error) {
            await stopScanner();
            alert('Kamera tidak dapat dibuka. Pastikan izin kamera sudah diberikan.');
        }
    });

    stop.addEventListener('click', stopScanner);
});
</script>
@endpush
@endsection
