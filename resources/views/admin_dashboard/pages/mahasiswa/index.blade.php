@extends('admin_dashboard.layout.master')
@section('title', 'Mahasiswa Baru')

@section('content')

<style>
    .mahasiswa-table-desktop .admin-table {
        min-width: 1100px;
    }
</style>

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Calon Mahasiswa Baru',
    'subtitle' => 'Kelola pendaftaran, validasi berkas, konfirmasi kelulusan, dan pengiriman notifikasi PMB.',
    'icon' => 'group',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Calon Mahasiswa Baru']
    ],
    'actions' => '
        <a href="' . route('admin.mahasiswa-baru.create') . '" class="btn btn-primary btn-action">
            <i class="bx bx-plus-circle"></i> Tambah Mahasiswa
        </a>
        <button type="button" class="btn btn-outline-warning btn-action" id="btn-generate-password-bulk">
            <i class="bx bx-key"></i> Generate Password Terpilih
        </button>
    '
])

{{-- Main Content --}}
<div class="row">
    <div class="col-12">
        @component('admin_dashboard.components.admin-card', [
            'title' => 'Daftar Calon Mahasiswa Baru',
            'icon' => 'bx bx-list-ul',
            'noPadding' => true
        ])
            @slot('headerActions')
                <button type="button" id="btn-cetak-pdf" class="btn btn-sm btn-outline-danger btn-action">
                    <i class="bx bxs-file-pdf"></i> PDF
                </button>
                <button type="button" id="btn-cetak-excel" class="btn btn-sm btn-outline-success btn-action">
                    <i class="bx bxs-file-export"></i> Excel
                </button>
            @endslot

            {{-- Filter Bar --}}
            <form id="filterForm" class="admin-filter-bar mb-0 border-bottom-0 pb-3 mt-1">
                <div class="filter-group">
                    <label for="search">Cari Nama</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="search" name="search" class="form-control form-control-sm"
                            value="{{ request('search') }}" placeholder="Cari nama..." autocomplete="off">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="periode">Periode</label>
                    <select id="periode" name="periode" class="form-select form-select-sm">
                        <option value="">Semua Periode</option>
                        @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}" @selected((string) request('periode') === (string) $periode->id)>
                            {{ $periode->deskripsi }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="gelombang">Gelombang</label>
                    <select id="gelombang" name="gelombang" class="form-select form-select-sm">
                        <option value="">Semua Gelombang</option>
                        @foreach ($gelombangs as $gelombang)
                        <option value="{{ $gelombang->id }}" @selected((string) request('gelombang') === (string) $gelombang->id)>
                            {{ $gelombang->nama_gelombang }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status_pemb">Status PMB</label>
                    <select id="status_pemb" name="status_pemb" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach ($pmbStatusOptions as $statusOption)
                            <option value="{{ $statusOption['value'] }}" @selected((string) request('status_pemb') === (string) $statusOption['value'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- Table Loader --}}
            <div id="table-loader" class="text-center py-4 d-none">
                <span class="spinner-border text-primary spinner-border-sm" role="status"></span>
                <span class="ms-2 text-muted" style="font-size: 0.875rem;">Memuat data...</span>
            </div>

            {{-- Desktop Table --}}
            <div class="mahasiswa-table-desktop d-none d-md-block">
                @component('admin_dashboard.components.admin-table', [
                    'headers' => ['', 'No', 'Tanggal Daftar', 'Nama / Email', 'Status PMB', 'Berkas', 'Program Studi', 'Akses Akun', 'Aksi'],
                    'bodyId' => 'mahasiswa-table-body'
                ])
                    @include('admin_dashboard.pages.mahasiswa._table', ['data' => $data])
                @endcomponent
            </div>

            {{-- Mobile Cards --}}
            <div id="mahasiswa-card-list" class="mahasiswa-mobile-list d-md-none">
                @include('admin_dashboard.pages.mahasiswa._cards', ['data' => $data])
            </div>

            {{-- Pagination --}}
            <div class="admin-pagination">
                <div id="pagination-links" class="d-flex justify-content-center">
                    @if(isset($data) && $data instanceof \Illuminate\Pagination\AbstractPaginator)
                    {{ $data->withQueryString()->onEachSide(1)->links('templates.sneat') }}
                    @endif
                </div>
            </div>
        @endcomponent
    </div>
</div>

<form id="bulk-password-form" action="{{ route('admin.mahasiswa-baru.generate-password-bulk') }}" method="POST" class="d-none">
    @csrf
</form>

<script>
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.getElementById('mahasiswa-table-body');
    const tableLoader = document.getElementById('table-loader');
    const searchInput = document.getElementById('search');
    const paginationContainer = document.getElementById('pagination-links');
    const cardList = document.getElementById('mahasiswa-card-list');

    document.getElementById('btn-generate-password-bulk')?.addEventListener('click', function () {
        const selected = [...document.querySelectorAll('.js-mahasiswa-select:checked')];
        if (!selected.length) {
            iziToast.warning({ title: 'Belum ada pilihan', message: 'Pilih minimal satu mahasiswa.', position: 'topRight' });
            return;
        }
        if (!confirm(`Generate password untuk ${selected.length} mahasiswa?`)) return;
        const form = document.getElementById('bulk-password-form');
        selected.forEach(input => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'ids[]';
            hidden.value = input.value;
            form.appendChild(hidden);
        });
        form.submit();
    });

    function getFormParams() {
        return new URLSearchParams(new FormData(filterForm)).toString();
    }

    function loadTable(params = '') {
        if (tableLoader) {
            tableLoader.classList.remove('d-none');
            tableBody?.closest('table')?.classList.add('opacity-50');
            cardList?.classList.add('opacity-50');
        }

        fetch(`{{ url('admin/mahasiswa-baru/filter') }}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (tableLoader) {
                tableLoader.classList.add('d-none');
                tableBody?.closest('table')?.classList.remove('opacity-50');
                cardList?.classList.remove('opacity-50');
            }
            if (tableBody) tableBody.innerHTML = data.table || '';
            if (cardList) cardList.innerHTML = data.cards || '';
            if (paginationContainer) paginationContainer.innerHTML = data.pagination || '';
            bindPaginationLinks();
        })
        .catch(error => {
            if (tableLoader) {
                tableLoader.classList.add('d-none');
                tableBody?.closest('table')?.classList.remove('opacity-50');
                cardList?.classList.remove('opacity-50');
            }
            iziToast.error({
                title: 'Error',
                message: error.message || 'Terjadi kesalahan saat memuat data.',
                position: 'topRight'
            });
        });
    }

    function bindPaginationLinks() {
        document.querySelectorAll('#pagination-links a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                loadTable(url.searchParams.toString());
            });
        });
    }

    // Debounce helper
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Event listeners
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            loadTable(getFormParams());
        });

        document.querySelectorAll('#filterForm select').forEach(el => {
            el.addEventListener('change', () => loadTable(getFormParams()));
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            loadTable(getFormParams());
        }, 400));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const pdfButton = document.getElementById('btn-cetak-pdf');
        const excelButton = document.getElementById('btn-cetak-excel');

        if (pdfButton) {
            pdfButton.addEventListener('click', function () {
                const params = getFormParams();
                const url = `{{ route('admin.mahasiswa.cetak') }}`;
                const fullUrl = params ? `${url}?${params}` : url;
                window.open(fullUrl, '_blank');
            });
        }

        if (excelButton) {
            excelButton.addEventListener('click', function () {
                const params = getFormParams();
                const url = `{{ route('admin.mahasiswa.cetak-excel') }}`;
                const fullUrl = params ? `${url}?${params}` : url;
                window.open(fullUrl, '_blank');
            });
        }
    });

    // Jalankan saat awal
    document.addEventListener('DOMContentLoaded', function () {
        bindPaginationLinks();
    });
</script>

<!-- ✅ Modal Quick Edit Mahasiswa -->
<div class="modal fade" id="quickEditMahasiswaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form id="quickEditMahasiswaForm" method="POST" action="" class="w-100">
            @csrf
            @method('PUT')
            <input type="hidden" id="quick_edit_row_index" name="row_index" value="">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold text-heading d-flex align-items-center"><i class="bx bx-edit me-2 text-primary fs-4"></i>Quick Edit Mahasiswa PMB</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                {{-- Skeleton Loader --}}
                <div id="quick-edit-skeleton" class="modal-body py-5 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Mengambil data calon mahasiswa...</p>
                </div>

                {{-- Actual Form Fields --}}
                <div id="quick-edit-fields" class="modal-body py-4 d-none" style="max-height: 70vh; overflow-y: auto;">
                    {{-- Error Summary --}}
                    <div id="quick-edit-error-summary" class="alert alert-danger d-none">
                        <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Perbaiki Kesalahan Berikut:</h6>
                        <ul class="mb-0" id="error-list"></ul>
                    </div>

                    <div class="row g-4">
                        {{-- Section 1: Informasi Utama --}}
                        <div class="col-lg-7 border-end-lg" style="border-right: 1px solid #e9ecef;">
                            <h6 class="fw-bold mb-3 text-heading border-bottom pb-2"><i class="bx bx-user me-2 text-primary"></i>Informasi Utama</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="admin-form-group">
                                        <label class="form-label required">Nama Lengkap</label>
                                        <input type="text" id="quick_name" name="name" class="form-control" placeholder="Nama mahasiswa baru" required>
                                        <div class="invalid-feedback text-danger" id="err-name"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="form-label required">Alamat Email</label>
                                        <input type="email" id="quick_email" name="email" class="form-control" placeholder="nama@contoh.com" required>
                                        <div class="invalid-feedback text-danger" id="err-email"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="form-label">Nomor HP</label>
                                        <input type="text" id="quick_phone" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                                        <div class="invalid-feedback text-danger" id="err-phone"></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="admin-form-group">
                                        <label class="form-label required">Program Studi / Jurusan</label>
                                        <select id="quick_jurusan_id" name="jurusan_id" class="form-select" required>
                                            <option value="">Pilih Program Studi</option>
                                        </select>
                                        <div class="invalid-feedback text-danger" id="err-jurusan_id"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="form-label required">Periode Akademik</label>
                                        <select id="quick_periode_id" name="periode_id" class="form-select" required>
                                            <option value="">Pilih Periode</option>
                                        </select>
                                        <div class="invalid-feedback text-danger" id="err-periode_id"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="form-label required">Gelombang PMB</label>
                                        <select id="quick_gelombang_id" name="gelombang_id" class="form-select" required>
                                            <option value="">Pilih Gelombang</option>
                                        </select>
                                        <div class="invalid-feedback text-danger" id="err-gelombang_id"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Status PMB --}}
                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3 text-heading border-bottom pb-2"><i class="bx bx-toggle-right me-2 text-warning"></i>Status PMB & Verifikasi</h6>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="admin-form-group">
                                        <label class="form-label">Status Kelengkapan Biodata</label>
                                        <select id="quick_status_biodata" name="status_biodata" class="form-select">
                                            <option value="0">Belum Lengkap / Draft</option>
                                            <option value="1">Lengkap / Terisi</option>
                                        </select>
                                        <div class="invalid-feedback text-danger" id="err-status_biodata"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="admin-form-group">
                                        <label class="form-label">Status Verifikasi Berkas</label>
                                        <select id="quick_status_berkas" name="status_berkas" class="form-select">
                                            <option value="0">Belum Diverifikasi</option>
                                            <option value="1">Terverifikasi / Valid</option>
                                            <option value="2">Ditolak / Tidak Valid</option>
                                        </select>
                                        <div class="invalid-feedback text-danger" id="err-status_berkas"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="admin-form-group">
                                        <label class="form-label">Status Pembayaran & Alur PMB</label>
                                        <select id="quick_status_pemb" name="status_pemb" class="form-select">
                                            <!-- Dynamically filled with PmbStatus options -->
                                        </select>
                                        <div class="invalid-feedback text-danger" id="err-status_pemb"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light-50 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <a href="" id="quick-edit-full-link" class="btn btn-label-warning"><i class="bx bx-cog me-1"></i> Edit Lengkap</a>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnQuickSave" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Cepat</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Global variable for options to handle dynamic gelombang changes
    let allGelombangs = [];

    // Trigger modal on Quick Edit button click (delegated)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-quick-edit-mahasiswa');
        if (btn) {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const index = btn.getAttribute('data-index');
            openQuickEditModal(id, index);
        }
    });

    function openQuickEditModal(id, rowIndex) {
        const modalEl = document.getElementById('quickEditMahasiswaModal');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('quickEditMahasiswaForm');
        
        // Setup Form actions
        form.action = `{{ url('admin/mahasiswa-baru') }}/${id}/quick-update`;
        document.getElementById('quick_edit_row_index').value = rowIndex;
        document.getElementById('quick-edit-full-link').href = `{{ url('admin/mahasiswa-baru') }}/${id}/edit`;

        // Clear errors & show skeleton
        clearValidationErrors();
        document.getElementById('quick-edit-skeleton').classList.remove('d-none');
        document.getElementById('quick-edit-fields').classList.add('d-none');
        document.getElementById('btnQuickSave').disabled = true;

        modal.show();

        // Fetch data
        fetch(`{{ url('admin/mahasiswa-baru') }}/${id}/quick-edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal mengambil data mahasiswa.');
            return response.json();
        })
        .then(res => {
            if (res.success) {
                // Populate options
                populateDropdown('quick_periode_id', res.options.periodes, res.data.periode_id, 'id', 'deskripsi');
                populateDropdown('quick_jurusan_id', res.options.jurusan, res.data.jurusan_id, 'id', 'nama_jurusan');
                
                // Store gelombangs globally
                allGelombangs = res.options.gelombangs;
                filterGelombangsByPeriode(res.data.periode_id, res.data.gelombang_id);

                // Status PMB
                const statusPembSelect = document.getElementById('quick_status_pemb');
                statusPembSelect.innerHTML = '';
                res.options.pmbStatusOptions.forEach(opt => {
                    const selected = opt.value === parseInt(res.data.status_pemb) ? 'selected' : '';
                    statusPembSelect.innerHTML += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                });

                // Populate values
                document.getElementById('quick_name').value = res.data.name || '';
                document.getElementById('quick_email').value = res.data.email || '';
                document.getElementById('quick_phone').value = res.data.phone || '';
                document.getElementById('quick_status_biodata').value = res.data.status_biodata !== null ? res.data.status_biodata : 0;
                document.getElementById('quick_status_berkas').value = res.data.status_berkas !== null ? res.data.status_berkas : 0;

                // Show fields, hide skeleton
                document.getElementById('quick-edit-skeleton').classList.add('d-none');
                document.getElementById('quick-edit-fields').classList.remove('d-none');
                document.getElementById('btnQuickSave').disabled = false;
            }
        })
        .catch(err => {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            iziToast.error({
                title: 'Error',
                message: err.message || 'Gagal memuat data pendaftar.',
                position: 'topRight'
            });
        });
    }

    function populateDropdown(selectId, items, selectedId, valueKey, labelKey) {
        const select = document.getElementById(selectId);
        select.innerHTML = `<option value="">Pilih...</option>`;
        items.forEach(item => {
            const selected = item[valueKey] == selectedId ? 'selected' : '';
            select.innerHTML += `<option value="${item[valueKey]}" ${selected}>${item[labelKey]}</option>`;
        });
    }

    // Filter gelombang dynamically inside modal based on selected periode
    document.getElementById('quick_periode_id').addEventListener('change', function() {
        filterGelombangsByPeriode(this.value);
    });

    function filterGelombangsByPeriode(periodeId, selectedGelombangId = null) {
        const gelombangSelect = document.getElementById('quick_gelombang_id');
        gelombangSelect.innerHTML = '';

        if (!periodeId) {
            gelombangSelect.innerHTML = '<option value="">Pilih Periode Terlebih Dahulu...</option>';
            return;
        }

        const filtered = allGelombangs.filter(g => g.periode_id == periodeId);

        if (filtered.length === 0) {
            gelombangSelect.innerHTML = '<option value="">Tidak ada gelombang pada periode ini.</option>';
            return;
        }

        gelombangSelect.innerHTML = '<option value="">Pilih Gelombang...</option>';
        filtered.forEach(g => {
            const selected = g.id == selectedGelombangId ? 'selected' : '';
            gelombangSelect.innerHTML += `<option value="${g.id}" ${selected}>${g.nama_gelombang}</option>`;
        });
    }

    function clearValidationErrors() {
        document.getElementById('quick-edit-error-summary').classList.add('d-none');
        document.getElementById('error-list').innerHTML = '';
        document.querySelectorAll('#quickEditMahasiswaModal .form-control, #quickEditMahasiswaModal .form-select').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#quickEditMahasiswaModal .invalid-feedback').forEach(el => {
            el.innerText = '';
        });
    }

    // Submit AJAX form
    document.getElementById('quickEditMahasiswaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btnSave = document.getElementById('btnQuickSave');
        const rowIndex = document.getElementById('quick_edit_row_index').value;

        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';
        clearValidationErrors();

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    return Promise.reject({ status: response.status, data });
                }
                return data;
            });
        })
        .then(res => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bx bx-save me-1"></i> Simpan Cepat';

            if (res.success) {
                // Update table row and mobile card DOM dynamically
                const tr = document.querySelector(`tr[data-mahasiswa-id="${res.data.id}"]`);
                const card = document.querySelector(`.mahasiswa-mobile-card[data-mahasiswa-id="${res.data.id}"]`);
                let updatedInline = false;

                if (tr && res.rowHtml) {
                    tr.outerHTML = res.rowHtml;
                    updatedInline = true;
                }

                if (card && res.cardHtml) {
                    card.outerHTML = res.cardHtml;
                    updatedInline = true;
                }

                if (!updatedInline) {
                    loadTable(getFormParams());
                }

                // Close modal
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('quickEditMahasiswaModal'));
                if (modalInstance) modalInstance.hide();

                iziToast.success({
                    title: 'Sukses',
                    message: res.message || 'Data mahasiswa berhasil diperbarui.',
                    position: 'topRight'
                });
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bx bx-save me-1"></i> Simpan Cepat';

            if (err.status === 422) {
                // Validation Error
                const summary = document.getElementById('quick-edit-error-summary');
                const list = document.getElementById('error-list');
                summary.classList.remove('d-none');
                
                for (const field in err.data.errors) {
                    const msg = err.data.errors[field][0];
                    list.innerHTML += `<li>${msg}</li>`;
                    
                    const input = document.getElementById('quick_' + field);
                    const feedback = document.getElementById('err-' + field);
                    if (input) input.classList.add('is-invalid');
                    if (feedback) feedback.innerText = msg;
                }

                // Scroll to top of modal body
                document.getElementById('quick-edit-fields').scrollTop = 0;
            } else {
                // Server Error 500
                iziToast.error({
                    title: 'Error',
                    message: err.data?.message || err.message || 'Gagal menyimpan perubahan. Terjadi kesalahan internal.',
                    position: 'topRight'
                });
            }
        });
    });
</script>

@include('admin_dashboard.components.confirm-delete-modal')
@endsection
