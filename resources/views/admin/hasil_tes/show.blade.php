@extends('admin_dashboard.layout.master')
@section('content')
<div class="row">
    {{-- ====================== DETAIL PESERTA ====================== --}}
    <div class="col-lg-4 col-md-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Peserta</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <span class="fw-semibold">Nama:</span><br>
                        {{ $hasil->user->name ?? '-' }}
                    </li>
                    <li class="mb-2">
                        <span class="fw-semibold">Email:</span><br>
                        {{ $hasil->user->email ?? '-' }}
                    </li>
                    <li class="mb-2">
                        <span class.fw-semibold">Waktu Mulai:</span><br>
                        {{ $hasil->waktu_mulai?->format('d M Y H:i') ?? '-' }}
                    </li>
                    <li class="mb-2">
                        <span class="fw-semibold">Waktu Selesai:</span><br>
                        {{ $hasil->waktu_selesai?->format('d M Y H:i') ?? '-' }}
                    </li>
                    <li class="mb-2">
                        <span class="fw-semibold">Status:</span><br>
                        @if ($hasil->status === 'selesai')
                        <span class="badge bg-label-success">Selesai</span>
                        @else
                        <span class.badge bg-label-warning">Belum Selesai</span>
                        @endif
                    </li>
                    <li>
                        <span class="fw-semibold">Total Skor:</span><br>
                        <span class="badge bg-label-primary fs-6">{{ $hasil->skor }}%</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ====================== REKAP PER KATEGORI ====================== --}}
    <div class="col-lg-8 col-md-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class.mb-0">Hasil Per Kategori</h5>
                <a href="{{ route('admin.hasil-tes.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ti ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th>Jumlah Soal</th>
                                <th>Benar</th>
                                <th>Salah</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hasil->kategoriHasil as $kategori)
                            <tr>
                                <td>{{ $kategori->kategori->nama_kategori ?? '-' }}</td>
                                <td>{{ $kategori->jumlah_soal }}</td>
                                <td><span class.text-success fw-semibold">{{ $kategori->jawaban_benar }}</span></td>
                                <td><span class="text-danger fw-semibold">{{ $kategori->jawaban_salah }}</span></td>
                                <td><span class="fw-bold">{{ $kategori->skor }}%</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== DETAIL JAWABAN (Tabs) ====================== --}}
    <div class="col-12" x-data="{
            activeKategori: '{{ $hasil->jawaban->groupBy('soal.kategori.nama_kategori')->keys()->first() ?? '' }}',
            activePage: 1,
            soalPerPage: 10,
            setKategori(k) { this.activeKategori = k; this.activePage = 1 },
        }">

        <h5 class="mt-4 mb-3">Detail Jawaban per Kategori</h5>


        <div x-data="hasilTesApp()">
            {{-- Tabs Kategori --}}
            <ul class="nav nav-tabs mb-3">
                @foreach ($hasil->jawaban->groupBy('soal.kategori.nama_kategori') as $kategori => $listJawaban)
                <li class="nav-item">
                    <button class="nav-link" :class="{ 'active': activeTab === '{{ Str::slug($kategori) }}' }"
                        @click="changeTab('{{ Str::slug($kategori) }}')">
                        {{ $kategori }}
                    </button>
                </li>
                @endforeach
            </ul>

            {{-- Konten per kategori --}}
            <div class="tab-content">
                @foreach ($hasil->jawaban->groupBy('soal.kategori.nama_kategori') as $kategori => $listJawaban)
                @php
                $slugKategori = Str::slug($kategori);
                $chunks = $listJawaban->chunk(10); // tampil 10 soal per halaman
                @endphp

                <div x-show="activeTab === '{{ $slugKategori }}'" class="card mb-4" x-cloak>
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <strong>{{ $kategori }}</strong>
                        <span>{{ $listJawaban->count() }} soal</span>
                    </div>

                    <div class="card-body" x-data="{ page: 1, totalPages: {{ $chunks->count() }} }">
                        {{-- Soal per halaman --}}
                        @foreach ($chunks as $page => $chunk)
                        <div x-show="page === {{ $page + 1 }}">
                            @foreach ($chunk as $index => $jwb)
                            @php
                            $soal = $jwb->soal;
                            $pilihanUser = $jwb->pilihanJawaban;
                            $pilihanBenar = $soal->pilihanJawaban->firstWhere('benar', 1);
                            @endphp

                            <div
                                class="mb-4 p-3 border rounded {{ $jwb->benar ? 'border-success bg-light-success' : 'border-danger bg-light-danger' }}">
                                <h6>
                                    {{ ($page * 10) + $index + 1 }}. {!! $soal->pertanyaan !!}
                                    <span class="badge {{ $jwb->benar ? 'bg-success' : 'bg-danger' }}">
                                        {{ $jwb->benar ? 'Benar' : 'Salah' }}
                                    </span>
                                </h6>

                                <ul class="list-group">
                                    @foreach ($soal->pilihanJawaban as $i => $pil)
                                    <li class="list-group-item d-flex justify-content-between align-items-center
                                            @if($pilihanUser && $pil->id == $pilihanUser->id) list-group-item-primary @endif
                                            @if($pil->benar) list-group-item-success @endif">
                                        <div>
                                            {{ chr(65 + $i) }}. {{ $pil->teks_pilihan }}
                                        </div>
                                        <div>
                                            @if($pilihanUser && $pil->id == $pilihanUser->id)
                                            <span class="badge bg-info text-dark">Jawaban Peserta</span>
                                            @endif
                                            @if($pil->benar)
                                            <span class="badge bg-success">Benar</span>
                                            @endif
                                        </div>
                                    </li>
                                    @endforeach
                                    @if(!$pilihanUser)
                                    <li class="list-group-item text-center text-muted">Tidak ada jawaban</li>
                                    @endif
                                </ul>
                            </div>
                            @endforeach
                        </div>
                        @endforeach

                        {{-- Navigasi halaman per kategori --}}
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-secondary btn-sm" @click="if(page > 1) page--"
                                :disabled="page === 1">Sebelumnya</button>
                            <span>Halaman <strong x-text="page"></strong> dari <strong
                                    x-text="totalPages"></strong></span>
                            <button class="btn btn-secondary btn-sm" @click="if(page < totalPages) page++"
                                :disabled="page === totalPages">Berikutnya</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hasilTesApp', () => ({
            activeTab: null,

            init() {
                // Pilih tab pertama secara default
                this.activeTab = document.querySelector('.nav-link')?.innerText.trim().toLowerCase().replace(/\s+/g, '-');
            },

            changeTab(tab) {
                this.activeTab = tab;
            },
        }));
    });
</script>