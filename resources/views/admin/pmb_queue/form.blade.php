@extends('admin_dashboard.layout.master')

@section('title', $mode === 'create' ? 'Buat Sesi Seleksi' : 'Edit Sesi Seleksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $mode === 'create' ? 'Buat Sesi Seleksi' : 'Edit Sesi Seleksi' }}</h4>
        <p class="text-muted mb-0">Atur jadwal seleksi, mode peserta, dan soal online untuk peserta jarak jauh.</p>
    </div>
    <a href="{{ $mode === 'edit' ? route('admin.pmb-queues.show', $session) : route('admin.pmb-queues.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

@php
    $hasOnlineSettings = (bool) (
        old('tes_tulis_id', $session->tes_tulis_id)
        || old('online_test_starts_at')
        || old('online_test_ends_at')
        || old('health_starts_at')
        || old('health_ends_at')
        || old('interview_starts_at')
        || old('interview_ends_at')
        || old('zoom_url')
        || $session->online_test_starts_at
        || $session->online_test_ends_at
        || $session->health_starts_at
        || $session->health_ends_at
        || $session->interview_starts_at
        || $session->interview_ends_at
        || $session->zoom_url
    );
    $sessionDate = old('session_date', optional($session->starts_at)->format('Y-m-d\TH:i'));
    $checkinDate = old('checkin_date', optional($session->checkin_open_at)->format('Y-m-d\TH:i'));
    $onlineStarts = old('online_test_starts_at', $hasOnlineSettings ? optional($session->online_test_starts_at)->format('Y-m-d\TH:i') : null);
    $onlineEnds = old('online_test_ends_at', $hasOnlineSettings ? optional($session->online_test_ends_at)->format('Y-m-d\TH:i') : null);
@endphp

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $mode === 'create' ? route('admin.pmb-queues.store') : route('admin.pmb-queues.update', $session) }}" class="row g-4">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="col-md-6">
                <label class="form-label">Nama Sesi</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $session->name) }}" placeholder="Contoh: Tes Offline Gelombang 1 - Pagi" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Periode</label>
                <select name="periode_id" class="form-select">
                    <option value="">Pilih Periode</option>
                    @foreach($periodes as $periode)
                        <option value="{{ $periode->id }}" @selected(old('periode_id', $session->periode_id) == $periode->id)>
                            {{ $periode->deskripsi ?? 'Periode #' . $periode->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Gelombang</label>
                <select name="gelombang_id" class="form-select">
                    <option value="">Pilih Gelombang</option>
                    @foreach($gelombangs as $gelombang)
                        <option value="{{ $gelombang->id }}" @selected(old('gelombang_id', $session->gelombang_id) == $gelombang->id)>
                            {{ $gelombang->nama_gelombang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tahapan Offline</label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $session->type ?: 'campuran') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Jadwal Sesi Kampus</label>
                <input type="datetime-local" name="session_date" class="form-control @error('session_date') is-invalid @enderror" value="{{ $sessionDate }}">
                <div class="form-text">Khusus peserta offline di kampus.</div>
                @error('session_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Jadwal Check-in Kampus</label>
                <input type="datetime-local" name="checkin_date" class="form-control @error('checkin_date') is-invalid @enderror" value="{{ $checkinDate }}">
                @error('checkin_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Status Sesi</label>
                <select name="status" class="form-select" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $session->status ?: 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <details class="border rounded p-3" @if($hasOnlineSettings) open @endif>
                    <summary class="fw-semibold text-primary">Pengaturan Online / Jarak Jauh</summary>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Soal Tes Online</label>
                            <select name="tes_tulis_id" class="form-select">
                                <option value="">Tidak memakai ujian online di sistem</option>
                                @foreach($tesTulis as $tes)
                                    <option value="{{ $tes->id }}" @selected(old('tes_tulis_id', $session->tes_tulis_id) == $tes->id)>
                                        {{ $tes->nama_tes }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tes Online Dibuka</label>
                            <input type="datetime-local" name="online_test_starts_at" class="form-control @error('online_test_starts_at') is-invalid @enderror" value="{{ $onlineStarts }}">
                            <div class="form-text">Tidak mengubah jadwal offline kampus.</div>
                            @error('online_test_starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tes Online Ditutup</label>
                            <input type="datetime-local" name="online_test_ends_at" class="form-control @error('online_test_ends_at') is-invalid @enderror" value="{{ $onlineEnds }}">
                            @error('online_test_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Kesehatan Online Dibuka</label>
                            <input type="datetime-local" name="health_starts_at" class="form-control @error('health_starts_at') is-invalid @enderror" value="{{ old('health_starts_at', optional($session->health_starts_at)->format('Y-m-d\TH:i')) }}">
                            @error('health_starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Kesehatan Online Ditutup</label>
                            <input type="datetime-local" name="health_ends_at" class="form-control @error('health_ends_at') is-invalid @enderror" value="{{ old('health_ends_at', optional($session->health_ends_at)->format('Y-m-d\TH:i')) }}">
                            @error('health_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Wawancara Online Dibuka</label>
                            <input type="datetime-local" name="interview_starts_at" class="form-control @error('interview_starts_at') is-invalid @enderror" value="{{ old('interview_starts_at', optional($session->interview_starts_at)->format('Y-m-d\TH:i')) }}">
                            @error('interview_starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Wawancara Online Ditutup</label>
                            <input type="datetime-local" name="interview_ends_at" class="form-control @error('interview_ends_at') is-invalid @enderror" value="{{ old('interview_ends_at', optional($session->interview_ends_at)->format('Y-m-d\TH:i')) }}">
                            @error('interview_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Link Zoom Utama</label>
                            <input type="url" name="zoom_url" class="form-control @error('zoom_url') is-invalid @enderror" value="{{ old('zoom_url', $session->zoom_url) }}" placeholder="https://zoom.us/j/...">
                            @error('zoom_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </details>
            </div>

            <div class="col-12">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Instruksi internal panitia">{{ old('notes', $session->notes) }}</textarea>
            </div>

            <div class="col-12">
                <div class="alert alert-info mb-0">
                    Peserta offline memakai check-in kampus. Peserta online mengerjakan ujian dan upload berkas lewat dashboard.
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.pmb-queues.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button class="btn btn-primary" type="submit">
                    <i class="bx bx-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
