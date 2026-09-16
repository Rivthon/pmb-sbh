<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Pass PMB - {{ $queue->user->name }}</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #eef3f8; color: #172033; }
        .pass-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .pass-card {
            width: min(760px, 100%);
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(15, 23, 42, .14);
        }
        .pass-header { padding: 28px 34px; background: #0f6fb8; color: white; }
        .pass-header h1 { margin: 0 0 8px; font-size: 28px; }
        .pass-header p { margin: 0; opacity: .88; }
        .pass-body { padding: 34px; display: grid; grid-template-columns: 240px 1fr; gap: 30px; }
        .qr-box {
            aspect-ratio: 1;
            border: 10px solid #172033;
            border-radius: 18px;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 18px;
            background:
                linear-gradient(90deg, #172033 12px, transparent 12px) 0 0 / 36px 36px,
                linear-gradient(#172033 12px, transparent 12px) 0 0 / 36px 36px,
                #fff;
            color: #172033;
        }
        .qr-inner {
            background: rgba(255,255,255,.94);
            border-radius: 12px;
            padding: 14px;
            font-size: 12px;
            word-break: break-all;
        }
        .number { font-size: 64px; font-weight: 800; margin-bottom: 8px; }
        .meta { display: grid; gap: 12px; }
        .meta-row { border-bottom: 1px solid #e5edf5; padding-bottom: 10px; }
        .label { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
        .value { font-size: 18px; font-weight: 700; margin-top: 3px; }
        .flow { padding: 0 34px 28px; }
        .flow h2 { font-size: 18px; margin: 0 0 12px; color: #172033; }
        .flow-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .flow-item { border: 1px solid #dbe5ef; border-radius: 12px; padding: 12px; background: #f8fafc; }
        .flow-item.free { border-color: #99f6e4; background: #ecfdf5; }
        .flow-label { font-size: 13px; font-weight: 800; margin-bottom: 6px; }
        .flow-status { font-size: 12px; color: #0f6fb8; font-weight: 700; }
        .flow-item.free .flow-status { color: #047857; }
        .pass-footer { padding: 18px 34px 30px; color: #64748b; font-size: 13px; }
        .actions { text-align: center; margin-top: 18px; }
        .actions button { border: 0; background: #0f6fb8; color: white; border-radius: 10px; padding: 12px 18px; font-weight: 700; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .pass-page { padding: 0; }
            .pass-card { box-shadow: none; border: 1px solid #dbe5ef; }
        }
        @media (max-width: 680px) {
            .pass-body { grid-template-columns: 1fr; }
            .qr-box { max-width: 260px; margin: 0 auto; width: 100%; }
            .flow-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    @php
        $checkInUrl = $queue->signedCheckInUrl();
        try {
            $qrDataUri = class_exists(\chillerlan\QRCode\QRCode::class)
                ? (new \chillerlan\QRCode\QRCode())->render($checkInUrl)
                : 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($checkInUrl);
        } catch (\Throwable $e) {
            $qrDataUri = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($checkInUrl);
        }
    @endphp
    <div class="pass-page">
        <div>
            <article class="pass-card">
                <header class="pass-header">
                    <h1>Kartu Check-in Kampus PMB</h1>
                    <p>STIKes Bogor Husada - {{ $queue->session->name }}</p>
                </header>
                <section class="pass-body">
                    <div>
                        <div class="qr-box" aria-label="QR check-in">
                            @if($qrDataUri)
                                <img src="{{ $qrDataUri }}" alt="QR Check-in PMB" style="width: 100%; height: auto; background: #fff; border-radius: 10px;">
                            @else
                                <div class="qr-inner">
                                    Scan payload:<br>
                                    {{ $checkInUrl }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="number">{{ $queue->queue_code ?? 'QR' }}</div>
                        <div class="meta">
                            <div class="meta-row">
                                <div class="label">Nama Peserta</div>
                                <div class="value">{{ $queue->user->name }}</div>
                            </div>
                            <div class="meta-row">
                                <div class="label">Program Studi</div>
                                <div class="value">{{ $queue->user->jurusan->nama_jurusan ?? '-' }}</div>
                            </div>
                            <div class="meta-row">
                                <div class="label">Jadwal</div>
                                <div class="value">{{ optional($queue->session->starts_at)->translatedFormat('d M Y H:i') ?? '-' }}</div>
                            </div>
                            <div class="meta-row">
                                <div class="label">Status</div>
                                <div class="value">{{ $queue->statusLabel() }}</div>
                            </div>
                            <div class="meta-row">
                                <div class="label">Jalur Tes</div>
                                <div class="value">{{ $queue->testFlowLabel() }}</div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="flow">
                    <h2>Tes yang Akan Diikuti</h2>
                    <div class="flow-grid">
                        @foreach($queue->plannedTestFlowItems() as $item)
                            <div class="flow-item {{ $item['label'] === 'Tes Tulis' && !$queue->requiresTesTulis() ? 'free' : '' }}">
                                <div class="flow-label">{{ $item['label'] }}</div>
                                <div class="flow-status">{{ $item['status'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>
                <footer class="pass-footer">
                    Tunjukkan kartu ini kepada panitia. Jika scanner tidak membaca visual, panitia dapat menyalin URL/UUID dari kartu ini.
                    <div style="word-break: break-all; margin-top: 8px;">UUID: {{ $queue->uuid }}</div>
                </footer>
            </article>
            <div class="actions">
                <button type="button" onclick="window.print()">Cetak Kartu</button>
            </div>
        </div>
    </div>
</body>
</html>
