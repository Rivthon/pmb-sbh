<tr data-mahasiswa-id="{{ $mahasiswa->id }}">
    <td class="text-center"><input type="checkbox" class="form-check-input js-mahasiswa-select" value="{{ $mahasiswa->id }}"></td>
    <td class="text-center fw-medium">
        {{ isset($index) && isset($data) && method_exists($data, 'firstItem') ? $data->firstItem() + $index : ($row_index ?? ($loop->iteration ?? '-')) }}
    </td>
    <td class="text-nowrap" style="font-size: 0.8125rem;">
        <i class="bx bx-calendar text-muted me-1"></i>
        {{ optional($mahasiswa->created_at)->translatedFormat('d M Y') ?? '-' }}
    </td>
    <td>
        <div class="fw-semibold text-dark">{{ $mahasiswa->name }}</div>
        <small class="text-muted d-flex align-items-center gap-1">
            <i class="bx bx-envelope" style="font-size: 0.875rem;"></i> {{ $mahasiswa->email }}
        </small>
    </td>
    <td>
        @include('admin_dashboard.components.status-pmb-badge', ['value' => $mahasiswa->status_pemb])
    </td>
    <td>
        @include('admin_dashboard.components.pmb-document-upload-status', ['mahasiswa' => $mahasiswa])
    </td>
    <td class="fw-medium text-dark" style="font-size: 0.875rem;">
        {{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}
    </td>
    <td>
        @if ($mahasiswa->password_plaintext)
            <code class="px-2 py-0.5 rounded bg-label-secondary text-secondary" style="font-family: monospace; font-size: 0.8125rem;">{{ $mahasiswa->password_plaintext }}</code>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-center">
        <div class="dropdown">
            <button class="btn btn-sm btn-icon-action btn-outline-secondary dropdown-toggle hide-arrow" type="button"
                id="aksiDropdownRow{{ $mahasiswa->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aksiDropdownRow{{ $mahasiswa->id }}">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.mahasiswa-baru.detail', $mahasiswa->id) }}">
                        <i class="bx bx-show text-info me-2"></i> Detail Profil
                    </a>
                </li>
                <li>
                    <button type="button" class="dropdown-item js-quick-edit-mahasiswa" data-id="{{ $mahasiswa->id }}" data-index="{{ isset($index) && isset($data) && method_exists($data, 'firstItem') ? $data->firstItem() + $index : ($row_index ?? ($loop->iteration ?? '-')) }}">
                        <i class="bx bx-edit text-primary me-2"></i> Quick Edit
                    </button>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.mahasiswa-baru.edit', $mahasiswa->id) }}">
                        <i class="bx bx-cog text-warning me-2"></i> Edit Lengkap
                    </a>
                </li>
                <li>
                    <form action="{{ route('admin.mahasiswa-baru.generate-password', $mahasiswa->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Buat password baru untuk mahasiswa ini?')">
                            <i class="bx bx-key text-warning me-2"></i> Generate Password
                        </button>
                    </form>
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
    </td>
</tr>
