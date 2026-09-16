<form method="POST" action="{{ route('admin.pmb-queues.participants.store', $selectedSession) }}" id="participantAddForm">
    @csrf
    <input type="hidden" name="redirect_to" value="participant_picker">
    <div class="card">
        <div class="card-header d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Peserta Tersedia</h5>
                <small class="text-muted">Pilih mode seleksi dan jalur tes untuk peserta yang ditambahkan. Jalur ini akan tampil di kartu tes.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <select name="selection_mode" class="form-select form-select-sm" style="width: 210px">
                    <option value="{{ \App\Models\PmbOfflineQueue::MODE_OFFLINE }}">Offline / Datang Kampus</option>
                    <option value="{{ \App\Models\PmbOfflineQueue::MODE_ONLINE }}">Online / Jarak Jauh</option>
                </select>
                <select name="test_flow" class="form-select form-select-sm" style="width: 190px">
                    <option value="tes_tulis">Ikut Tes Tulis</option>
                    <option value="bebas_tes_tulis">Bebas Tes Tulis</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Tambah Terpilih</button>
                <button class="btn btn-sm btn-outline-primary" type="submit" name="check_in_after_add" value="1">Tambah & Check-in</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 42px;">
                            <input class="form-check-input" type="checkbox" id="selectAllParticipants" aria-label="Pilih semua peserta">
                        </th>
                        <th>Peserta</th>
                        <th>Program</th>
                        <th>Periode</th>
                        <th>Gelombang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $participant)
                        <tr>
                            <td>
                                <input class="form-check-input participant-checkbox" type="checkbox" name="user_ids[]" value="{{ $participant->id }}" aria-label="Pilih {{ $participant->name }}">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $participant->name }}</div>
                                <div class="text-muted small">{{ $participant->code ?? '-' }} - {{ $participant->email }}</div>
                                <div class="text-muted small">{{ $participant->phone ?? '-' }}</div>
                            </td>
                            <td>{{ $participant->jurusan->nama_jurusan ?? '-' }}</td>
                            <td>{{ $participant->periode->deskripsi ?? '-' }}</td>
                            <td>{{ $participant->gelombang->nama_gelombang ?? '-' }}</td>
                            <td>
                                <span class="badge bg-label-success">Verified</span>
                                <span class="badge bg-label-secondary">{{ $participant->status_berkas ? 'Berkas ' . $participant->status_berkas : 'Berkas -' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bx bx-user-x fs-2 d-block mb-2"></i>
                                Tidak ada peserta yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 border-top">
            <div class="text-muted small">
                Menampilkan {{ $participants->firstItem() ?? 0 }}-{{ $participants->lastItem() ?? 0 }} dari {{ $participants->total() }} peserta
            </div>
            <div class="participants-pagination">
                {{ $participants->links() }}
            </div>
        </div>
    </div>
</form>
