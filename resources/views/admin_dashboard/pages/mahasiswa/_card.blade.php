@php
    $rowNumber = isset($index) && isset($data) && method_exists($data, 'firstItem')
        ? $data->firstItem() + $index
        : ($row_index ?? ($loop->iteration ?? '-'));
@endphp

<article class="mahasiswa-mobile-card" data-mahasiswa-id="{{ $mahasiswa->id }}">
    <div class="mahasiswa-mobile-card__header">
        <div class="mahasiswa-mobile-card__identity">
            <span class="mahasiswa-mobile-card__number">#{{ $rowNumber }}</span>
            <h6 class="mahasiswa-mobile-card__name">{{ $mahasiswa->name }}</h6>
            <div class="mahasiswa-mobile-card__email">
                <i class="bx bx-envelope"></i>
                <span>{{ $mahasiswa->email ?: '-' }}</span>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-icon-action btn-outline-secondary dropdown-toggle hide-arrow" type="button"
                id="aksiDropdownCard{{ $mahasiswa->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aksiDropdownCard{{ $mahasiswa->id }}">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.mahasiswa-baru.detail', $mahasiswa->id) }}">
                        <i class="bx bx-show text-info me-2"></i> Detail Profil
                    </a>
                </li>
                <li>
                    <button type="button" class="dropdown-item js-quick-edit-mahasiswa" data-id="{{ $mahasiswa->id }}" data-index="{{ $rowNumber }}">
                        <i class="bx bx-edit text-primary me-2"></i> Quick Edit
                    </button>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.mahasiswa-baru.edit', $mahasiswa->id) }}">
                        <i class="bx bx-cog text-warning me-2"></i> Edit Lengkap
                    </a>
                </li>
                <li>
                    <form action="{{ route('admin.mahasiswa-baru.update-status', $mahasiswa->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="dropdown-item">
                            <i class="bx bx-check-shield text-success me-2"></i>
                            {{ \App\Enums\PmbStatus::fromValue($mahasiswa->status_pemb)->reviewActionLabel() }}
                        </button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.mahasiswa-baru.sendMessage', $mahasiswa->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-warning">
                            <i class="bx bx-envelope text-warning me-2"></i> Kirim Info Lulus
                        </button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.mahasiswa-baru.sendFailureMessage', $mahasiswa->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bx bx-x-circle text-danger me-2"></i> Kirim Info Gagal
                        </button>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('admin.mahasiswa-baru.destroy', $mahasiswa->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete(this.closest('form'), '{{ addslashes($mahasiswa->name) }}')">
                            <i class="bx bx-trash text-danger me-2"></i> Hapus
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="mahasiswa-mobile-card__status">
        @include('admin_dashboard.components.status-pmb-badge', ['value' => $mahasiswa->status_pemb])
    </div>

    <dl class="mahasiswa-mobile-card__meta">
        <div>
            <dt>Tanggal Daftar</dt>
            <dd><i class="bx bx-calendar"></i>{{ optional($mahasiswa->created_at)->translatedFormat('d M Y') ?? '-' }}</dd>
        </div>
        <div>
            <dt>Program Studi</dt>
            <dd>{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</dd>
        </div>
        <div>
            <dt>Berkas</dt>
            <dd>
                @include('admin_dashboard.components.pmb-document-upload-status', ['mahasiswa' => $mahasiswa])
            </dd>
        </div>
        <div>
            <dt>Plain Password</dt>
            <dd>
                @if ($mahasiswa->password_plaintext)
                    <code>{{ $mahasiswa->password_plaintext }}</code>
                @else
                    <span class="text-muted">-</span>
                @endif
            </dd>
        </div>
    </dl>
</article>
