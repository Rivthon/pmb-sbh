@extends('dashboard.layout.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y d-flex justify-content-center align-items-center"
    style="min-height: 80vh;">
    <div class="card shadow-lg w-100" style="max-width: 600px;">

        {{-- BANNER HEADER --}}
        <div class="card-header text-center bg-warning">
            <h2 class="text-white mb-1">Pengumuman Hasil Seleksi</h2>
            <p class="text-black-50 mb-0">
                PMB {{ config('app.institution_name', 'STIKes Bogor Husada') }} T.A {{ $tahunAkademik }}
            </p>
        </div>

        {{-- KONTEN UTAMA --}}
        <div class="card-body p-4">
            <p class="mb-2">Assalamualaikum Wr. Wb.</p>
            <p>
                Kepada Yth. Sdr/i <strong class="text-warning">{{ $nama }}</strong>,<br>
                Peserta Tes Seleksi PMB Program Studi <strong>{{ $jurusan }}</strong>.
            </p>

            <hr class="my-4">

            <p>
                Sehubungan dengan telah dilaksanakannya Tes Seleksi PMB pada hari
                {{-- Cek jika tanggalTes ada sebelum menampilkannya --}}
                @if($tanggalTes)
                <strong>{{ \Carbon\Carbon::parse($tanggalTes)->translatedFormat('l, d F Y') }}</strong>,
                @endif
                dengan gembira kami sampaikan hasil seleksi Anda:
            </p>

            {{-- KOTAK STATUS KELULUSAN --}}
            <div class="text-center p-3 my-4 bg-light-warning" style="border: 1px dashed #ffab00; border-radius: 8px;">
                <p class="mb-2">Anda dinyatakan:</p>
                <span class="badge bg-warning fs-5 rounded-pill px-4 py-2">LULUS</span>
            </div>

            <p>
                Dimohon kepada seluruh peserta yang dinyatakan <strong>LULUS</strong> untuk segera bergabung ke grup
                WhatsApp Mahasiswa Baru (PPSMB) dan melanjutkan ke tahap selanjutnya.
            </p>

            {{-- TOMBOL CALL TO ACTION --}}
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                <a href="{{ $linkPPSMB }}" target="_blank" rel="noopener" class="btn btn-success btn-lg">
                    <i class='bx bxl-whatsapp me-2'></i>Gabung Grup PPSMB
                </a>
                {{-- Anda bisa menambahkan tombol lain di sini jika perlu, contoh: --}}
                {{-- <a href="#" class="btn btn-primary btn-lg"><i class='bx bx-file me-2'></i>Lihat SK Kelulusan</a>
                --}}
            </div>

            <hr class="my-4">

            {{-- PENUTUP --}}
            <p class="mb-2">Terima kasih atas perhatian dan partisipasi Anda.</p>
            <p class="mb-2">Wassalamualaikum Wr. Wb.</p>

            <p class="mt-4 mb-0 fw-semibold">- Panitia PMB {{ config('app.institution_name', 'STIKes Bogor Husada') }} -
            </p>
        </div>
    </div>
</div>
@endsection