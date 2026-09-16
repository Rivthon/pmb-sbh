@extends('admin_dashboard.layout.master')

@section('content')
<div class="container">
    <h4 class="fw-bold mb-4">Soal untuk: {{ $tesTulis->nama_tes }}</h4>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.soal.create', $tesTulis->id) }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Tambah Soal
        </a>

        <a href="{{ route('admin.tes-tulis.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
    // Kelompokkan soal berdasarkan kategori
    $soalByKategori = $soal->groupBy(function($item) {
    return $item->kategori->nama_kategori ?? 'Tanpa Kategori';
    });
    @endphp

    @forelse ($soalByKategori as $kategoriNama => $listSoal)
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bx bx-category me-1 text-primary"></i>
                {{ $kategoriNama }}
            </h5>
            <span class="badge bg-info">{{ $listSoal->count() }} Soal</span>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Pertanyaan</th>
                        <th style="width: 45%;">Pilihan Jawaban</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listSoal as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $item->pertanyaan }}</strong>
                            @if(filled($item->cerita_bacaan))
                            <div class="alert alert-info py-2 px-3 mt-2 mb-0 small">
                                <div class="fw-semibold">Awal bacaan/grup soal</div>
                                <div class="text-muted">{{ Str::limit($item->cerita_bacaan, 160) }}</div>
                            </div>
                            @endif
                            <div class="text-muted small mt-1">
                                Skor: {{ $item->skor ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <ul class="list-unstyled mb-2">
                                @forelse ($item->pilihanJawaban as $pj)
                                <li class="d-flex align-items-center mb-1">
                                    <span class="fw-bold me-2">
                                        {{ $pj->kode_pilihan ?? chr(64 + $loop->iteration) }}.
                                    </span>
                                    <span>{{ $pj->teks_pilihan }}</span>

                                    @if ($pj->benar)
                                    <span class="badge bg-success ms-2">Benar</span>
                                    @endif

                                    <form action="{{ route('admin.pilihan.destroy', $pj->id) }}" method="POST"
                                        class="ms-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus pilihan ini?')">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </li>
                                @empty
                                <li class="text-muted fst-italic">Belum ada pilihan jawaban.</li>
                                @endforelse
                            </ul>

                            {{-- Form Tambah Pilihan --}}
                            <form action="{{ route('admin.pilihan.store', $item->id) }}" method="POST" class="row g-2">
                                @csrf
                                <div class="col-md-7">
                                    <input type="text" name="teks_pilihan" class="form-control form-control-sm"
                                        placeholder="Teks pilihan baru" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" name="benar" value="1"
                                            id="benar_{{ $item->id }}">
                                        <label class="form-check-label small" for="benar_{{ $item->id }}">Benar</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </div>
                            </form>
                        </td>

                        <td class="text-nowrap">
                            <a href="{{ route('admin.soal.edit', [$tesTulis->id, $item->id]) }}"
                                class="btn btn-sm btn-warning me-1">
                                <i class="bx bx-edit"></i>
                            </a>

                            <form action="{{ route('admin.soal.destroy', [$tesTulis->id, $item->id]) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus soal ini?')">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-warning text-center">
        Belum ada soal yang dibuat untuk tes ini.
    </div>
    @endforelse
</div>
@endsection
