@extends('dashboard.layout.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Ujian /</span> Daftar Tes Tulis Aktif</h4> --}}

    @if ($tesTulis->isEmpty())
    <div class="card">
        <div class="card-body text-center p-5">
            {{-- Ilustrasi untuk halaman kosong --}}

            <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/page-misc-error-light.png"
                alt="No Tests Available" height="180" class="mb-3">
            <h5 class="fw-bold">Belum Ada Tes yang Aktif</h5>
            <p class="text-muted">Silakan periksa kembali nanti, saat ini belum ada tes tulis yang tersedia untuk Anda.
            </p>
        </div>
    </div>
    @else
    @php
        $session = $queue?->session;
        $isOnlineSelectionOpen = (bool) $session?->isOnlineSelectionOpen();
    @endphp
    @unless($isOnlineSelectionOpen)
    <div class="alert alert-info mb-4">
        Tes tulis online dibuka {{ optional($session?->online_test_starts_at)->translatedFormat('d F Y H:i') ?? 'sesuai jadwal panitia' }}.
        @if($session?->online_test_ends_at)
            Ditutup {{ $session->online_test_ends_at->translatedFormat('d F Y H:i') }}.
        @endif
    </div>
    @endunless
    <div class="row g-4">
        @foreach ($tesTulis as $tes)
        @php
        // Logika PHP Anda tetap sama, sudah efisien
        $hasil = $tes->hasilTesUser;
        $sisaDetik = 0;
        $statusTes = 'belum_dikerjakan';

        if ($hasil) {
        if ($hasil->status === 'selesai') {
        $statusTes = 'selesai';
        } elseif ($hasil->status === 'berlangsung') {
        $batasWaktu = \Carbon\Carbon::parse($hasil->waktu_mulai)->addMinutes($tes->durasi_menit);
        $sisaDetik = max(0, now()->diffInSeconds($batasWaktu, false));
        $statusTes = ($sisaDetik > 0) ? 'berlangsung' : 'waktu_habis';
        }
        }
        @endphp

        <div class="col-md-6 col-lg-12">
            {{-- Card dengan shadow untuk efek 'mengambang' --}}
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    {{-- BAGIAN HEADER KARTU --}}
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bxs-file-doc fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title fw-bold mb-0">{{ $tes->nama_tes }}</h5>
                            <small class="text-muted">Tes Tulis Online</small>
                        </div>
                    </div>

                    {{-- BAGIAN DESKRIPSI --}}
                    <p class="text-muted small mb-0">
                        {{ $tes->deskripsi ?? 'Tidak ada deskripsi untuk tes ini.' }}
                    </p>

                    {{-- BAGIAN FOOTER KARTU (muncul di bawah) --}}
                    <div class="mt-auto pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-start">
                                <small class="text-muted d-block">Durasi</small>
                                <span class="fw-semibold"><i class='bx bx-time-five me-1'></i>{{ $tes->durasi_menit }}
                                    Menit</span>
                            </div>

                            @if($statusTes === 'berlangsung')
                            <div x-data="timerComponent({{ $sisaDetik }})" x-init="start()" class="text-end">
                                <small class="text-muted d-block">Sisa Waktu</small>
                                <span class="fw-semibold badge"
                                    :class="sisaDetik > 300 ? 'bg-label-warning' : 'bg-label-danger'">
                                    <span x-text="tampilanWaktu"></span>
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Tombol Aksi (Call to Action) --}}
                        @if ($statusTes === 'selesai')
                        <button class="btn btn-label-success w-100" disabled><i
                                class='bx bx-check-circle me-1'></i>Selesai</button>
                        @elseif ($statusTes === 'berlangsung')
                        <a href="{{ route('dashboard.tes-tulis.show', $tes->id) }}" class="btn btn-warning w-100"><i
                                class='bx bx-play-circle me-1'></i>Lanjutkan Tes</a>
                        @elseif ($statusTes === 'waktu_habis')
                        <button class="btn btn-label-secondary w-100" disabled><i class='bx bx-time me-1'></i>Waktu
                            Habis</button>
                        @elseif (!$isOnlineSelectionOpen)
                        <button class="btn btn-label-secondary w-100" disabled><i class='bx bx-lock-alt me-1'></i>Belum Dibuka</button>
                        @else
                        <a href="{{ route('dashboard.tes-tulis.show', $tes->id) }}" class="btn btn-primary w-100"><i
                                class='bx bxs-pencil me-1'></i>Mulai Kerjakan</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
<script>
    document.addEventListener('alpine:init', () => {
    Alpine.data('timerComponent', (initialSeconds) => ({
        sisaDetik: initialSeconds,
        tampilanWaktu: '00:00',

        start() {
            this.formatWaktu(); // Panggil sekali untuk format awal yang benar

            const interval = setInterval(() => {
                if (this.sisaDetik > 0) {
                    this.sisaDetik--;
                    this.formatWaktu();
                } else {
                    clearInterval(interval);
                    // Refresh halaman untuk memperbarui status tombol menjadi "Waktu Habis"
                    setTimeout(() => window.location.reload(), 1500);
                }
            }, 1000);
        },

        formatWaktu() {
            const menit = Math.floor(this.sisaDetik / 60);
            const detik = this.sisaDetik % 60;
            this.tampilanWaktu = `${String(menit).padStart(2, '0')}:${String(detik).padStart(2, '0')}`;
        }
    }));
});
</script>
