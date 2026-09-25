@extends('admin_dashboard.layout.master')
@section('title', 'Mahasiswa Baru')

@section('content')
<!-- Tabel Mahasiswa Baru -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Mahasiswa Baru SBH Gelombang 3</h5>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.mahasiswa-baru.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Mahasiswa"
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary ms-2">Cari</button>
            </form>
            <a href="{{ route('admin.mahasiswa-baru.create') }}" class="btn btn-sm btn-success">
                <i class="bx bx-plus-circle"></i> Tambah
            </a>
        </div>
    </div>

    @if ($errors->has('update_error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first('update_error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif


    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Foto Profil</th>
                    <th>Tgl. Daftar</th>
                    <th>Nama</th>
                    <th>Status Akun</th>
                    <th>Berkas</th>
                    <th>Prog. Studi</th>
                    <th>Password</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $mahasiswa)
                <tr>
                    <td>{{ $data->firstItem() + $index }}</td>
                    <td>
                        <img src="{{ $mahasiswa->image ? asset('storage/user/avatar/' . $mahasiswa->image) : asset('dashboard_assets/assets/img/avatars/1.png') }}"
                            alt="Foto Profil" class="rounded-circle"
                            style="width: 45px; height: 45px; object-fit: cover;">
                    </td>
                    <td>{{ $mahasiswa->created_at->translatedFormat('l, d F Y') }}</td>
                    <td>{{ $mahasiswa->name }}</td>
                    <td>
                        @switch($mahasiswa->status_pemb)
                        @case(0)
                        <span class="badge bg-warning">Belum Update Berkas</span>
                        @break
                        @case(1)
                        <span class="badge bg-info">Menunggu Validasi Admin</span>
                        @break
                        @case(2)
                        <span class="badge bg-success">Verified</span>
                        @break
                        @case(3)
                        <span class="badge bg-info">Lulus</span>
                        @break
                        @default
                        <span class="badge bg-secondary">Tidak Diketahui</span>
                        @endswitch
                    </td>
                    <td>
                        @include('admin_dashboard.components.pmb-document-upload-status', ['mahasiswa' => $mahasiswa])
                    </td>
                    <td>{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</td>
                    <td><span class="text-muted">Password terenkripsi</span></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                id="aksiDropdown{{ $mahasiswa->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                Aksi
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="aksiDropdown{{ $mahasiswa->id }}">
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.mahasiswa-baru.detail', $mahasiswa->id) }}">Detail</a>
                                </li>
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.mahasiswa-baru.edit', $mahasiswa->id) }}">Ubah</a></li>
                                <li>
                                    <form
                                        action="{{ route('admin.mahasiswa-baru.update-status', $mahasiswa->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="dropdown-item">
                                            @if((int) $mahasiswa->status_pemb === 0)
                                            Setujui PMB
                                            @elseif((int) $mahasiswa->status_pemb === 1)
                                            Validasi Berkas
                                            @else
                                            Batalkan PMB
                                            @endif
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('admin.mahasiswa-baru.sendMessage', $mahasiswa->id) }}"
                                        method="POST" class="watzap-form" onsubmit="return showLoadingMessage(this)">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-success">
                                            Kirim Pesan WhatsApp
                                        </button>
                                    </form>


                                </li>
                                <li>
                                    <form action="{{ route('admin.mahasiswa-baru.destroy', $mahasiswa->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($data->hasPages())
    <div class="d-flex justify-content-center mt-3 pagination-links">
        {{ $data->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
<script>
    function confirmSendMessage(form) {
        // Konfirmasi sebelum kirim
        const confirmSend = confirm('Apakah Anda yakin ingin mengirim pesan WhatsApp ke mahasiswa ini?');

        if (confirmSend) {
            // Kalau user pilih OK, jalankan loading
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';

            return true; // Lanjut submit
        } else {
            return false; // Batalkan submit
        }
    }
</script>
<!-- End Tabel Mahasiswa Baru -->
@endsection
