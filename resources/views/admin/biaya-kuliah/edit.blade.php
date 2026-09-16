@extends('admin_dashboard.layout.master')
@section('title', 'Edit Biaya - ' . $prodiNama)

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class='bx bx-edit text-primary me-2'></i>Edit Biaya Kuliah
                </h4>
                <p class="text-muted mb-0">{{ $prodiNama }} &mdash; {{ $jumlahSemester }} Semester</p>
            </div>
            <a href="{{ route('admin.biaya-kuliah.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.biaya-kuliah.update', $prodiKey) }}" method="POST" id="formBiaya">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bx bx-table me-1"></i> Tabel Biaya Per Gelombang & Semester
                    </h5>
                    <small class="text-muted">Masukkan nilai dalam Rupiah (contoh: 12.000.000)</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-start" style="width: 200px;">Semester</th>
                                    <th>Gelombang I</th>
                                    <th>Gelombang II</th>
                                    <th>Gelombang III</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($sem = 1; $sem <= $jumlahSemester; $sem++)
                                    <tr>
                                        <td class="text-start fw-semibold">
                                            <span class="badge bg-primary rounded-pill me-2">{{ $sem }}</span>
                                            {{ $sem === 1 ? 'Biaya Awal Masuk' : 'Semester ' . $sem }}
                                        </td>
                                        @for ($gel = 1; $gel <= 3; $gel++)
                                            @php
                                                $currentValue = '';
                                                if (isset($biayaData[$gel])) {
                                                    $semItem = $biayaData[$gel]->firstWhere('semester', $sem);
                                                    $currentValue = $semItem ? $semItem->biaya : '';
                                                }
                                            @endphp
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    {{-- Input tampilan (dengan titik separator) --}}
                                                    <input type="text" class="form-control text-end biaya-display"
                                                        value="{{ old("biaya.{$gel}.{$sem}", $currentValue) }}" required
                                                        data-gel="{{ $gel }}" data-sem="{{ $sem }}" placeholder="0"
                                                        autocomplete="off">
                                                    {{-- Hidden input (nilai asli tanpa titik, dikirim ke server) --}}
                                                    <input type="hidden" name="biaya[{{ $gel }}][{{ $sem }}]" class="biaya-hidden"
                                                        value="{{ old("biaya.{$gel}.{$sem}", $currentValue) }}"
                                                        data-gel="{{ $gel }}">
                                                </div>
                                            </td>
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td class="text-start">Total </td>
                                    <td id="total-gel-1">-</td>
                                    <td id="total-gel-2">-</td>
                                    <td id="total-gel-3">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /**
             * Format angka ke format Rupiah dengan titik pemisah ribuan
             * 12000000 → "12.000.000"
             */
            function formatRupiah(angka) {
                var number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return rupiah || '0';
            }

            /**
             * Hapus semua titik dari string rupiah → angka murni
             * "12.000.000" → 12000000
             */
            function parseRupiah(str) {
                return parseInt(str.replace(/\./g, '')) || 0;
            }

            // ===== Format semua input saat halaman dimuat =====
            document.querySelectorAll('.biaya-display').forEach(function (input) {
                var raw = input.value;
                if (raw) {
                    input.value = formatRupiah(raw);
                }
            });

            // ===== Event: ketika user mengetik =====
            document.querySelectorAll('.biaya-display').forEach(function (input) {
                input.addEventListener('input', function () {
                    var cursorPos = this.selectionStart;
                    var oldLen = this.value.length;

                    // Ambil angka murni, format ulang
                    var raw = parseRupiah(this.value);
                    var formatted = formatRupiah(raw);
                    this.value = formatted;

                    // Update hidden input
                    var gel = this.getAttribute('data-gel');
                    var sem = this.getAttribute('data-sem');
                    var hidden = document.querySelector('input.biaya-hidden[name="biaya[' + gel + '][' + sem + ']"]');
                    if (hidden) {
                        hidden.value = raw;
                    }

                    // Perbaiki posisi cursor setelah formatting
                    var newLen = this.value.length;
                    var newPos = cursorPos + (newLen - oldLen);
                    this.setSelectionRange(newPos, newPos);

                    // Update totals
                    updateTotals();
                });

                // Juga sync hidden saat load
                var gel = input.getAttribute('data-gel');
                var sem = input.getAttribute('data-sem');
                var hidden = document.querySelector('input.biaya-hidden[name="biaya[' + gel + '][' + sem + ']"]');
                if (hidden) {
                    hidden.value = parseRupiah(input.value);
                }
            });

            // ===== Hitung total per gelombang =====
            function updateTotals() {
                for (var gel = 1; gel <= 3; gel++) {
                    var hiddens = document.querySelectorAll('input.biaya-hidden[data-gel="' + gel + '"]');
                    var total = 0;
                    hiddens.forEach(function (h) {
                        total += parseInt(h.value) || 0;
                    });
                    var el = document.getElementById('total-gel-' + gel);
                    if (el) {
                        el.textContent = 'Rp ' + formatRupiah(total);
                    }
                }
            }

            // Hitung total saat load
            updateTotals();
        });
    </script>
@endpush