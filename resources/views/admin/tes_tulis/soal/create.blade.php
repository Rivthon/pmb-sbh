@extends('admin_dashboard.layout.master')
@section('content')
<div class="container">
    <h4 class="fw-bold mb-3">Tambah Soal untuk: {{ $tesTulis->judul_tes }}</h4>

    <form action="{{ route('admin.soal.store', $tesTulis->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Pertanyaan</label>
            <textarea name="pertanyaan" class="form-control" required>{{ old('pertanyaan') }}</textarea>
        </div>

        <div class="mb-3">
            <label>Skor Soal</label>
            <input type="number" name="skor" class="form-control" maxlength="2" value="{{ old('skor') }}" required>
        </div>
        <div class="mb-3">
            <label for="kategori_id" class="form-label">Kategori Soal</label>
            <select name="kategori_id" id="kategori_id" class="form-select">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategoriSoal as $kategori)
                <option value="{{ $kategori->id }}" {{ old('kategori_id', $soalTes->kategori_id ?? '') == $kategori->id
                    ?
                    'selected' : '' }}>
                    {{ $kategori->nama_kategori }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="cerita_bacaan" class="form-label">Cerita/Bacaan untuk Grup Soal</label>
            <textarea name="cerita_bacaan" id="cerita_bacaan" class="form-control" rows="5" placeholder="Isi hanya pada soal pertama dari bacaan ini. Contoh: isi di nomor 41 untuk soal 41-43, lalu isi lagi di nomor 44 untuk soal 44-45.">{{ old('cerita_bacaan') }}</textarea>
            <small class="text-muted">Opsional. Jika diisi, bacaan akan tampil mulai dari soal ini sampai ada soal berikutnya yang memiliki bacaan baru.</small>
        </div>


        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.soal.index', $tesTulis->id) }}" class="btn btn-secondary me-2">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
