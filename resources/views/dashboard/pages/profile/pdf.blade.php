@extends('templates.pdf.layouts.master')
@section('title', 'Cetak Kartu Ujian PMB')

@push('style')
<style>
    .pass-card {
        border: 2px solid #0f6fb8;
        border-radius: 12px;
        padding: 20px;
        background: #fff;
        margin-top: 10px;
    }

    .pass-header {
        background-color: #0f6fb8;
        color: #ffffff;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        margin-bottom: 20px;
        text-align: center;
    }

    .pass-header h2 {
        margin: 0;
        font-size: 20px;
    }

    .pass-header p {
        margin: 5px 0 0 0;
        font-size: 13px;
        opacity: 0.9;
    }

    .pass-content-table {
        width: 100%;
        border-collapse: collapse;
    }

    .qr-container {
        width: 170px;
        text-align: center;
        vertical-align: top;
        padding-right: 20px;
    }

    .qr-image {
        width: 150px;
        height: 150px;
        border: 4px solid #172033;
        border-radius: 8px;
        padding: 5px;
        background: #fff;
    }

    .queue-code {
        font-size: 32px;
        font-weight: 800;
        color: #0f6fb8;
        margin-top: 10px;
        text-align: center;
    }

    .details-container {
        vertical-align: top;
    }

    .detail-table {
        width: 100%;
    }

    .detail-table th {
        text-align: left;
        width: 30%;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        padding: 6px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-table td {
        padding: 6px 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .flow-box {
        margin-top: 18px;
        border: 1px solid #dbeafe;
        border-radius: 8px;
        overflow: hidden;
    }

    .flow-title {
        background: #eff6ff;
        color: #0f6fb8;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 8px 12px;
    }

    .flow-table {
        width: 100%;
        border-collapse: collapse;
    }

    .flow-table th,
    .flow-table td {
        border-top: 1px solid #e2e8f0;
        padding: 8px 12px;
        font-size: 12px;
        text-align: left;
    }

    .flow-table th {
        color: #64748b;
        text-transform: uppercase;
        font-size: 10px;
    }

    .flow-status {
        font-weight: 800;
        color: #0f6fb8;
    }

    .flow-status-free {
        color: #047857;
    }

    .pass-footer {
        margin-top: 25px;
        padding-top: 15px;
        border-top: 1px dashed #cbd5e1;
        color: #64748b;
        font-size: 11px;
        text-align: center;
        line-height: 1.4;
    }

    .uuid-box {
        font-family: monospace;
        margin-top: 5px;
        font-size: 10px;
        word-break: break-all;
    }

    .alert-no-session {
        background-color: #fef3c7;
        border: 1px solid #f59e0b;
        color: #92400e;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        margin-top: 30px;
    }

    .pdf-letterhead {
        width: 100%;
        border-bottom: 2px solid #172033;
        padding-bottom: 14px;
        margin-bottom: 18px;
        text-align: center;
    }

    .pdf-letterhead-logo {
        width: 296px;
        max-width: 100%;
        height: auto;
        margin-bottom: 8px;
    }

    .pdf-letterhead-address {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.4;
    }
</style>
@endpush

@section('content')
<div class="pdf-letterhead">
    <img src="{{ public_path('dashboard_assets/assets/img/logo/logo_sbh_panjang.png') }}" alt="Logo STIKes Bogor Husada" class="pdf-letterhead-logo" />
    <p class="pdf-letterhead-address">Jl. Sholeh Iskandar No.4, RT.02/RW.03, Kedungbadak, Tanah Sareal, Kota Bogor, Jawa Barat 16164</p>
</div>

@if($queue)
@php
    $flowItems = $queue->plannedTestFlowItems();
    $sessionStart = $queue->session->starts_at;
@endphp
<div class="pass-card">
    <div class="pass-header">
        <h2>KARTU UJIAN TES PMB</h2>
        <p>STIKes Bogor Husada - Sesi: {{ $queue->session->name }}</p>
    </div>

    <table class="pass-content-table">
        <tr>
            <td class="qr-container">
                <img src="{{ $tempPath ?? $qrDataUri }}" alt="QR Kartu Ujian" class="qr-image" />
                <div class="queue-code">{{ $queue->queue_code ?? 'QR' }}</div>
            </td>
            <td class="details-container">
                <table class="detail-table">
                    <tr>
                        <th>Nama Peserta</th>
                        <td>: {{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Program Studi</th>
                        <td>: {{ $user->jurusan->nama_jurusan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Sekolah Asal</th>
                        <td>: {{ $user->asal_sekolah ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Tes</th>
                        <td>: {{ optional($sessionStart)->translatedFormat('d F Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Mulai Tes</th>
                        <td>: {{ optional($sessionStart)->format('H:i') ? optional($sessionStart)->format('H:i') . ' WIB - sampai selesai' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Antrian</th>
                        <td>: {{ $queue->statusLabel() }}</td>
                    </tr>
                    <tr>
                        <th>Jalur Tes</th>
                        <td>: {{ $queue->testFlowLabel() }}</td>
                    </tr>
                    @if($queue->room)
                    <tr>
                        <th>Lokasi / Ruang</th>
                        <td>: {{ $queue->room->name }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="flow-box">
        <div class="flow-title">Rincian Tes yang Diikuti</div>
        <table class="flow-table">
            <thead>
                <tr>
                    <th style="width: 36px;">No</th>
                    <th>Tahap / Tes</th>
                    <th>Status Peserta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flowItems as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item['label'] }}</td>
                        <td>
                            <span class="flow-status {{ $item['label'] === 'Tes Tulis' && !$queue->requiresTesTulis() ? 'flow-status-free' : '' }}">
                                {{ $item['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pass-footer">
        Tunjukkan kartu ujian ini kepada panitia saat tiba di kampus.
        <div class="uuid-box">UUID: {{ $queue->uuid }}</div>
    </div>
</div>
@else
<div class="alert-no-session">
    Sesi antrian belum dibuat oleh panitia.<br>
    <span style="font-weight: normal; font-size: 13px;">Silakan hubungi panitia PMB untuk penjadwalan sesi tes offline
        Anda.</span>
</div>
@endif
@endsection
