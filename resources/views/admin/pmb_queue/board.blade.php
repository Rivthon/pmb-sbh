<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Board Antrian - {{ $session->name }}</title>
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Public Sans", Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }
        .board-shell { min-height: 100vh; display: grid; grid-template-rows: auto 1fr auto; }
        .board-header {
            padding: 18px 26px;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            border-bottom: 1px solid #d9e2ec;
            background: #ffffff;
        }
        .board-title h1 { margin: 0; font-size: clamp(28px, 3.4vw, 50px); letter-spacing: 0; }
        .board-title p { margin: 6px 0 0; color: #64748b; font-size: clamp(14px, 1.5vw, 20px); }
        .board-tools { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end; }
        .sound-toggle {
            border: 1px solid #0f766e;
            background: #10b981;
            color: #04111d;
            border-radius: 8px;
            padding: 11px 14px;
            font-weight: 800;
            font-size: 15px;
            display: inline-flex;
            gap: 8px;
            align-items: center;
            cursor: pointer;
        }
        .sound-toggle.is-on { background: #d1fae5; }
        .sound-state { color: #475569; font-size: 14px; min-width: 104px; }
        .board-clock { text-align: right; font-size: clamp(24px, 3vw, 42px); font-weight: 900; }
        .board-content { padding: 18px 26px; min-height: 0; }
        .health-panel {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #ffffff;
            padding: 16px;
            margin-bottom: 16px;
        }
        .health-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }
        .health-title { font-size: clamp(22px, 2.2vw, 34px); font-weight: 950; line-height: 1.1; }
        .health-subtitle { color: #64748b; font-size: 14px; }
        .health-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(280px, .9fr);
            gap: 14px;
            align-items: stretch;
        }
        .health-room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }
        .health-room-card {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            padding: 14px;
            background: #f8fafc;
            min-height: 168px;
        }
        .health-room-card.is-ready { box-shadow: inset 0 0 0 1px rgba(16,185,129,.55); }
        .health-room-card.is-busy { box-shadow: inset 0 0 0 1px rgba(14,165,233,.55); }
        .health-room-card.is-full { box-shadow: inset 0 0 0 1px rgba(245,158,11,.75); }
        .health-room-card.is-muted { opacity: .62; }
        .health-room-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .health-room-name { font-size: 19px; font-weight: 900; line-height: 1.12; }
        .health-room-status {
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 900;
            color: #04111d;
            background: #d1fae5;
            white-space: nowrap;
        }
        .health-room-status.is-busy { background: #bae6fd; }
        .health-room-status.is-full { background: #fde68a; }
        .health-room-status.is-muted { background: #cbd5e1; }
        .health-room-capacity { margin-top: 8px; color: #475569; font-size: 14px; }
        .health-active-list { margin-top: 10px; display: grid; gap: 8px; }
        .health-active-item { display: grid; grid-template-columns: 70px 1fr; gap: 8px; align-items: center; }
        .health-active-code {
            border-radius: 6px;
            background: #dbeafe;
            padding: 8px 5px;
            text-align: center;
            font-weight: 950;
        }
        .health-waiting-card {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #f8fafc;
            padding: 14px;
            min-height: 168px;
        }
        .interview-groups { display: grid; gap: 16px; margin-bottom: 16px; }
        .interview-panel {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #ffffff;
            padding: 16px;
        }
        .interview-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(280px, .9fr);
            gap: 14px;
            align-items: stretch;
        }
        .interview-room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
        }
        .lane-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(220px, 1fr));
            gap: 14px;
            align-content: start;
        }
        .lane-card {
            min-height: 354px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #ffffff;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto auto 1fr;
        }
        .lane-card.is-latest { box-shadow: 0 0 0 2px rgba(34, 211, 238, .8), 0 22px 70px rgba(14, 165, 233, .26); }
        .lane-head {
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            border-bottom: 1px solid #e2e8f0;
        }
        .lane-title { font-size: clamp(20px, 2vw, 30px); font-weight: 900; line-height: 1.1; }
        .lane-count { color: #475569; font-size: 14px; white-space: nowrap; }
        .lane-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
        .lane-room-status {
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 900;
            color: #04111d;
            background: #d1fae5;
            white-space: nowrap;
        }
        .lane-room-status.is-busy { background: #bae6fd; }
        .lane-room-status.is-full { background: #fde68a; }
        .lane-room-status.is-muted { background: #cbd5e1; }
        .current-call {
            padding: 18px 16px;
            background: #eaf4ff;
            border-bottom: 1px solid #bfdbfe;
        }
        .current-label { font-size: 13px; text-transform: uppercase; letter-spacing: 0; opacity: .9; }
        .current-code { margin-top: 6px; font-size: clamp(48px, 5vw, 82px); line-height: .92; font-weight: 950; }
        .current-public-name {
            display: block;
            margin-top: 10px;
            font-size: clamp(20px, 2vw, 30px);
            line-height: 1.1;
            font-weight: 850;
            color: #075985;
        }
        .current-name { margin-top: 10px; font-size: clamp(20px, 2vw, 30px); font-weight: 900; line-height: 1.12; }
        .current-room { margin-top: 8px; color: #075985; font-size: 16px; }
        .empty-call {
            padding: 20px 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #64748b;
            min-height: 176px;
            display: flex;
            align-items: center;
        }
        .waiting-list { padding: 12px 16px 16px; min-height: 0; }
        .waiting-title { color: #475569; font-size: 14px; margin-bottom: 8px; }
        .waiting-item {
            display: grid;
            grid-template-columns: 72px 1fr;
            gap: 10px;
            align-items: center;
            padding: 9px 0;
            border-top: 1px solid #e2e8f0;
        }
        .waiting-item:first-of-type { border-top: 0; }
        .waiting-code {
            border-radius: 6px;
            background: #e8eef5;
            padding: 9px 6px;
            text-align: center;
            font-size: 18px;
            font-weight: 900;
        }
        .waiting-name { font-size: 17px; font-weight: 800; line-height: 1.1; }
        .waiting-meta { color: #64748b; font-size: 12px; margin-top: 3px; }
        .board-footer {
            padding: 14px 26px;
            border-top: 1px solid #d9e2ec;
            background: #ffffff;
            color: #334155;
            font-size: clamp(15px, 1.8vw, 22px);
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        @media (max-width: 1400px) {
            .lane-grid { grid-template-columns: repeat(2, minmax(260px, 1fr)); }
        }
        @media (max-width: 900px) {
            .board-header, .board-footer { flex-direction: column; align-items: flex-start; }
            .board-tools { justify-content: flex-start; }
            .board-header, .board-content, .board-footer { padding-left: 14px; padding-right: 14px; }
            .health-layout { grid-template-columns: 1fr; }
            .interview-layout { grid-template-columns: 1fr; }
            .lane-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @php
        $health = $boardPayload['health'] ?? null;
        $lanes = collect($boardPayload['stages'] ?? [])
            ->reject(fn ($lane) => ($lane['key'] ?? null) === \App\Models\PmbQueueStage::KEY_TES_KESEHATAN);
    @endphp

    <div class="board-shell">
        <header class="board-header">
            <div class="board-title">
                <h1> Sistem Antrian Tes Offline STIKes Bogor Husada</h1>
                <p>{{ $session->name }} - {{ $session->periode->deskripsi ?? 'PMB SBH' }}</p>
            </div>
            <div class="board-tools">
                <button type="button" class="sound-toggle" id="soundToggle">
                    <i class="bx bx-volume-full"></i>
                    Aktifkan Suara
                </button>
                <span class="sound-state" id="soundState">Suara nonaktif</span>
                <div class="board-clock" id="boardClock">{{ now()->format('H:i') }}</div>
            </div>
        </header>

        <main class="board-content">
            <section id="healthPanel" class="health-panel" aria-label="Loket Tes Kesehatan">
                @if($health)
                    @php
                        $healthRooms = collect($health['rooms'] ?? []);
                        $healthWaiting = collect($health['waiting'] ?? [])->take(5);
                    @endphp
                    <div class="health-head">
                        <div>
                            <div class="health-title">TES KESEHATAN</div>
                            <div class="health-subtitle">Silakan menuju {{ $health['location'] ?? 'Ruang Tes Kesehatan, Lantai 2' }}</div>
                        </div>
                        <div class="lane-count">{{ $health['waiting_count'] ?? $healthWaiting->count() }} menunggu</div>
                    </div>
                    <div class="health-layout">
                        <div class="health-room-grid">
                            @forelse($healthRooms as $room)
                                @php
                                    $active = collect($room['current_calls'] ?? [])->first();
                                    $tone = $room['occupancy_tone'] ?? 'ready';
                                @endphp
                                <article class="health-room-card is-{{ $tone }}">
                                    <div class="health-room-top">
                                        <div class="health-room-name">{{ $room['name'] ?? 'Loket Tes Kesehatan' }}</div>
                                        <span class="health-room-status is-{{ $tone }}">{{ $room['occupancy_status'] ?? 'Kosong' }}</span>
                                    </div>
                                    @if($active)
                                        <div class="current-code">
                                            {{ $active['queue_code'] ?? '-' }}
                                            @if(!empty($active['display_name']))
                                                <span class="current-public-name">- {{ $active['display_name'] }}</span>
                                            @endif
                                        </div>
                                        <div class="health-room-capacity">Silakan menuju {{ $room['name'] ?? 'Loket Tes Kesehatan' }}</div>
                                    @else
                                        <div class="current-name">{{ $tone === 'muted' ? 'Loket tidak melayani' : 'Siap menerima peserta' }}</div>
                                        <div class="health-room-capacity">{{ $room['active_count'] ?? 0 }}/{{ $room['capacity'] ?? 1 }} sedang diperiksa</div>
                                    @endif
                                </article>
                            @empty
                                <article class="health-room-card is-muted">
                                    <div class="health-room-name">Belum ada loket kesehatan</div>
                                    <div class="waiting-meta mt-2">Tambahkan loket dari pengaturan sesi.</div>
                                </article>
                            @endforelse
                        </div>
                        <aside class="health-waiting-card">
                            <div class="waiting-title">Menunggu Tes Kesehatan</div>
                            @forelse($healthWaiting as $queue)
                                <div class="waiting-item">
                                    <div class="waiting-code">{{ $queue['queue_code'] ?? '-' }}</div>
                                    <div>
                                        <div class="waiting-name">Menunggu panggilan</div>
                                        <div class="waiting-meta">Tes Kesehatan</div>
                                    </div>
                                </div>
                            @empty
                                <div class="waiting-item">
                                    <div class="waiting-code">-</div>
                                    <div>
                                        <div class="waiting-name">Belum ada antrean</div>
                                        <div class="waiting-meta">Peserta akan masuk otomatis sesuai alur sesi</div>
                                    </div>
                                </div>
                            @endforelse
                        </aside>
                    </div>
                @endif
            </section>

            <section id="interviewPanel" class="interview-groups" aria-label="Meja Wawancara"></section>

            <section class="lane-grid" id="laneGrid" aria-label="Lane antrian PMB">
                @forelse($lanes as $lane)
                    @php
                        $currentCalls = collect($lane['current_calls'] ?? []);
                        $mainCall = $currentCalls->first();
                        $waiting = collect($lane['waiting'] ?? [])->take(4);
                        $laneRoom = collect($lane['rooms'] ?? [])->first();
                        $callKey = $mainCall ? (($mainCall['queue_code'] ?? '-') . '|' . ($mainCall['called_at'] ?? '') . '|' . ($lane['name'] ?? '-')) : null;
                    @endphp
                    <article class="lane-card" data-lane-key="{{ $callKey }}">
                        <div class="lane-head">
                            <div class="lane-title">{{ strtoupper($lane['name'] ?? '-') }}</div>
                            <div class="lane-meta">
                                @if($laneRoom)
                                    <span class="lane-room-status is-{{ $laneRoom['display_tone'] ?? 'ready' }}">{{ $laneRoom['display_status'] ?? '-' }}</span>
                                @endif
                                <div class="lane-count">{{ data_get($lane, 'counts.waiting', 0) }} menunggu</div>
                            </div>
                        </div>

                        @if($mainCall)
                            <div class="current-call">
                                <div class="current-label">Sedang Dipanggil</div>
                                <div class="current-code">
                                    {{ $mainCall['queue_code'] ?? '-' }}
                                    @if(!empty($mainCall['display_name']))
                                        <span class="current-public-name">- {{ $mainCall['display_name'] }}</span>
                                    @endif
                                </div>
                                <div class="current-room">
                                    <i class="bx bx-door-open"></i>
                                    {{ $mainCall['room'] ?? ($lane['name'] ?? '-') }}
                                </div>
                            </div>
                        @else
                            <div class="empty-call">
                                <div>
                                    <div class="current-label">Sedang Dipanggil</div>
                                    <div class="current-name">Menunggu panggilan berikutnya</div>
                                </div>
                            </div>
                        @endif

                        <div class="waiting-list">
                            <div class="waiting-title">Berikutnya</div>
                            @forelse($waiting as $queue)
                                <div class="waiting-item">
                                        <div class="waiting-code">{{ $queue['queue_code'] ?? '-' }}</div>
                                        <div>
                                            <div class="waiting-name">Menunggu panggilan</div>
                                            <div class="waiting-meta">{{ $lane['name'] ?? 'Wawancara' }}</div>
                                        </div>
                                    </div>
                            @empty
                                <div class="waiting-item">
                                    <div class="waiting-code">-</div>
                                    <div>
                                        <div class="waiting-name">Belum ada antrean</div>
                                        <div class="waiting-meta">Data diperbarui otomatis</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <article class="lane-card">
                        <div class="lane-head">
                            <div class="lane-title">Belum ada lane aktif</div>
                            <div class="lane-count">0 menunggu</div>
                        </div>
                        <div class="empty-call">
                            <div class="current-name">Pastikan tahap Tes Kesehatan dan Wawancara aktif.</div>
                        </div>
                    </article>
                @endforelse
            </section>
        </main>

        <footer class="board-footer">
            <span>Harap tetap berada di area tunggu. Peserta yang dipanggil segera menuju ruangan sesuai arahan panitia.</span>
            <span>Selesai: {{ $completedCount }}</span>
        </footer>
    </div>

    <script>
        const boardEndpoint = @json(route('api.pmb.queue-public.board', $session));
        const initialPayload = @json($boardPayload);
        const seenStorageKey = 'pmb-board-seen-calls-{{ $session->uuid }}';
        const soundStorageKey = 'pmb-board-sound-enabled';
        const healthPanel = document.getElementById('healthPanel');
        const interviewPanel = document.getElementById('interviewPanel');
        const laneGrid = document.getElementById('laneGrid');
        const soundToggle = document.getElementById('soundToggle');
        const soundState = document.getElementById('soundState');
        let soundEnabled = localStorage.getItem(soundStorageKey) === 'true';
        let audioContext = null;
        let announcementQueue = Promise.resolve();
        const announcementRepeatCount = 3;

        function tickClock() {
            const now = new Date();
            document.getElementById('boardClock').textContent = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function callKey(lane, call) {
            return [
                call?.queue_code || '-',
                call?.called_at || '',
                lane?.name || '-'
            ].join('|');
        }

        function currentCallEntries(payload) {
            const entries = [];

            if ((payload?.interviews || []).length) {
                payload.interviews.forEach((group) => {
                    (group.rooms || []).forEach((room) => {
                        (room.current_calls || []).forEach((call) => entries.push({
                            lane: { name: group.name },
                            call: { ...call, room: call.room || room.name },
                        }));
                    });
                });
            } else {
                (payload?.stages || []).forEach((lane) => {
                    (lane.current_calls || []).forEach((call) => entries.push({ lane, call }));
                });
            }

            (payload?.health?.current_calls || []).forEach((call) => {
                entries.push({
                    lane: { name: 'Tes Kesehatan' },
                    call: { ...call, room: call.room || payload?.health?.location },
                });
            });

            return entries;
        }

        function readSeenCalls() {
            try {
                return new Set(JSON.parse(localStorage.getItem(seenStorageKey) || '[]'));
            } catch (error) {
                return new Set();
            }
        }

        function writeSeenCalls(seen) {
            localStorage.setItem(seenStorageKey, JSON.stringify(Array.from(seen).slice(-80)));
        }

        function renderLanes(payload, latestKey = null) {
            if ((payload?.interviews || []).length) {
                laneGrid.innerHTML = '';
                return;
            }

            const lanes = (payload?.stages || []).filter((lane) => lane.key !== 'tes_kesehatan');

            if (!lanes.length) {
                laneGrid.innerHTML = `
                    <article class="lane-card">
                        <div class="lane-head">
                            <div class="lane-title">Belum ada lane aktif</div>
                            <div class="lane-count">0 menunggu</div>
                        </div>
                        <div class="empty-call">
                            <div class="current-name">Pastikan tahap Tes Kesehatan dan Wawancara aktif.</div>
                        </div>
                    </article>
                `;
                return;
            }

            laneGrid.innerHTML = lanes.map((lane) => {
                const mainCall = (lane.current_calls || [])[0] || null;
                const waiting = (lane.waiting || []).slice(0, 4);
                const key = mainCall ? callKey(lane, mainCall) : '';
                const currentHtml = mainCall ? `
                    <div class="current-call">
                        <div class="current-label">Sedang Dipanggil</div>
                        <div class="current-code">
                            ${escapeHtml(mainCall.queue_code || '-')}
                            ${mainCall.display_name ? `<span class="current-public-name">- ${escapeHtml(mainCall.display_name)}</span>` : ''}
                        </div>
                        <div class="current-room">
                            <i class="bx bx-door-open"></i>
                            ${escapeHtml(mainCall.room || lane.name || '-')}
                        </div>
                    </div>
                ` : `
                    <div class="empty-call">
                        <div>
                            <div class="current-label">Sedang Dipanggil</div>
                            <div class="current-name">Menunggu panggilan berikutnya</div>
                        </div>
                    </div>
                `;
                const waitingHtml = waiting.length ? waiting.map((queue) => `
                    <div class="waiting-item">
                        <div class="waiting-code">${escapeHtml(queue.queue_code || '-')}</div>
                        <div>
                            <div class="waiting-name">Menunggu panggilan</div>
                            <div class="waiting-meta">${escapeHtml(lane.name || 'Wawancara')}</div>
                        </div>
                    </div>
                `).join('') : `
                    <div class="waiting-item">
                        <div class="waiting-code">-</div>
                        <div>
                            <div class="waiting-name">Belum ada antrean</div>
                            <div class="waiting-meta">Data diperbarui otomatis</div>
                        </div>
                    </div>
                `;

                return `
                    <article class="lane-card ${latestKey && key === latestKey ? 'is-latest' : ''}" data-lane-key="${escapeHtml(key)}">
                        <div class="lane-head">
                            <div class="lane-title">${escapeHtml(lane.name || '-')}</div>
                            <div class="lane-meta">
                                ${lane.rooms?.[0] ? `<span class="lane-room-status is-${escapeHtml(lane.rooms[0].display_tone || 'ready')}">${escapeHtml(lane.rooms[0].display_status || '-')}</span>` : ''}
                                <div class="lane-count">${escapeHtml(lane.counts?.waiting || 0)} menunggu</div>
                            </div>
                        </div>
                        ${currentHtml}
                        <div class="waiting-list">
                            <div class="waiting-title">Berikutnya</div>
                            ${waitingHtml}
                        </div>
                    </article>
                `;
            }).join('');
        }

        function renderHealthPanel(payload) {
            const health = payload?.health || null;
            if (!healthPanel || !health) {
                if (healthPanel) healthPanel.innerHTML = '';
                return;
            }

            const rooms = health.rooms || [];
            const waiting = (health.waiting || []).slice(0, 5);
            const roomHtml = rooms.length ? rooms.map((room) => {
                const item = room.current_calls?.[0] || null;
                const tone = room.occupancy_tone || 'ready';
                const content = item ? `
                    <div class="current-code">
                        ${escapeHtml(item.queue_code || '-')}
                        ${item.display_name ? `<span class="current-public-name">- ${escapeHtml(item.display_name)}</span>` : ''}
                    </div>
                    <div class="health-room-capacity">Silakan menuju ${escapeHtml(room.name || 'Loket Tes Kesehatan')}</div>
                ` : `
                    <div class="current-name">${tone === 'muted' ? 'Loket tidak melayani' : 'Siap menerima peserta'}</div>
                    <div class="health-room-capacity">${escapeHtml(room.active_count || 0)}/${escapeHtml(room.capacity || 1)} sedang diperiksa</div>
                `;

                return `
                    <article class="health-room-card is-${escapeHtml(tone)}">
                        <div class="health-room-top">
                            <div class="health-room-name">${escapeHtml(room.name || 'Loket Tes Kesehatan')}</div>
                            <span class="health-room-status is-${escapeHtml(tone)}">${escapeHtml(room.occupancy_status || 'Kosong')}</span>
                        </div>
                        ${content}
                    </article>
                `;
            }).join('') : `
                <article class="health-room-card is-muted">
                    <div class="health-room-name">Belum ada loket kesehatan</div>
                    <div class="waiting-meta mt-2">Tambahkan loket dari pengaturan sesi.</div>
                </article>
            `;

            const waitingHtml = waiting.length ? waiting.map((queue) => `
                <div class="waiting-item">
                        <div class="waiting-code">${escapeHtml(queue.queue_code || '-')}</div>
                        <div>
                            <div class="waiting-name">Menunggu panggilan</div>
                            <div class="waiting-meta">Tes Kesehatan</div>
                        </div>
                    </div>
            `).join('') : `
                <div class="waiting-item">
                    <div class="waiting-code">-</div>
                    <div>
                        <div class="waiting-name">Belum ada antrean</div>
                        <div class="waiting-meta">Peserta akan masuk otomatis sesuai alur sesi</div>
                    </div>
                </div>
            `;

            healthPanel.innerHTML = `
                <div class="health-head">
                    <div>
                        <div class="health-title">TES KESEHATAN</div>
                        <div class="health-subtitle">Silakan menuju ${escapeHtml(health.location || 'Ruang Tes Kesehatan, Lantai 2')}</div>
                    </div>
                    <div class="lane-count">${escapeHtml(health.waiting_count ?? waiting.length)} menunggu</div>
                </div>
                <div class="health-layout">
                    <div class="health-room-grid">${roomHtml}</div>
                    <aside class="health-waiting-card">
                        <div class="waiting-title">Menunggu Tes Kesehatan</div>
                        ${waitingHtml}
                    </aside>
                </div>
            `;
        }

        function renderInterviewPanel(payload) {
            const groups = payload?.interviews || [];

            if (!interviewPanel || !groups.length) {
                if (interviewPanel) interviewPanel.innerHTML = '';
                return;
            }

            interviewPanel.innerHTML = groups.map((group) => {
                const rooms = group.rooms || [];
                const waiting = (group.waiting || []).slice(0, 5);
                const roomHtml = rooms.map((room) => {
                    const call = room.current_calls?.[0] || null;
                    const tone = room.occupancy_tone || 'ready';
                    const content = call ? `
                        <div class="current-code">
                            ${escapeHtml(call.queue_code || '-')}
                            ${call.display_name ? `<span class="current-public-name">- ${escapeHtml(call.display_name)}</span>` : ''}
                        </div>
                        <div class="health-room-capacity">Silakan menuju ${escapeHtml(room.name || 'Meja Wawancara')}</div>
                    ` : `
                        <div class="current-name">${tone === 'muted' ? 'Meja tidak melayani' : 'Siap menerima peserta'}</div>
                        <div class="health-room-capacity">Menunggu dosen memanggil peserta</div>
                    `;

                    return `
                        <article class="health-room-card is-${escapeHtml(tone)}">
                            <div class="health-room-top">
                                <div class="health-room-name">${escapeHtml(room.name || 'Meja Wawancara')}</div>
                                <span class="health-room-status is-${escapeHtml(tone)}">${escapeHtml(room.occupancy_status || 'Kosong')}</span>
                            </div>
                            ${content}
                        </article>
                    `;
                }).join('');
                const waitingHtml = waiting.length ? waiting.map((queue) => `
                    <div class="waiting-item">
                        <div class="waiting-code">${escapeHtml(queue.queue_code || '-')}</div>
                        <div>
                            <div class="waiting-name">Menunggu panggilan</div>
                            <div class="waiting-meta">Wawancara ${escapeHtml(group.name || '')}</div>
                        </div>
                    </div>
                `).join('') : `
                    <div class="waiting-item">
                        <div class="waiting-code">-</div>
                        <div>
                            <div class="waiting-name">Belum ada antrean</div>
                            <div class="waiting-meta">Peserta masuk setelah Tes Kesehatan selesai</div>
                        </div>
                    </div>
                `;

                return `
                    <article class="interview-panel">
                        <div class="health-head">
                            <div>
                                <div class="health-title">WAWANCARA ${escapeHtml(group.name || '').toUpperCase()}</div>
                                <div class="health-subtitle">Silakan menuju nomor meja yang disebutkan</div>
                            </div>
                            <div class="lane-count">${escapeHtml(group.waiting_count || 0)} menunggu</div>
                        </div>
                        <div class="interview-layout">
                            <div class="interview-room-grid">${roomHtml}</div>
                            <aside class="health-waiting-card">
                                <div class="waiting-title">Menunggu Wawancara</div>
                                ${waitingHtml}
                            </aside>
                        </div>
                    </article>
                `;
            }).join('');
        }

        function markCurrentCallsSeen(payload) {
            const seen = readSeenCalls();
            currentCallEntries(payload).forEach(({ lane, call }) => seen.add(callKey(lane, call)));
            writeSeenCalls(seen);
        }

        function beep() {
            try {
                audioContext ??= new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gain = audioContext.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.value = 880;
                gain.gain.setValueAtTime(0.0001, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.25, audioContext.currentTime + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + 0.35);
                oscillator.connect(gain).connect(audioContext.destination);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.38);
            } catch (error) {
                // Speech synthesis remains the primary announcement path.
            }
        }

        function wait(milliseconds) {
            return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
        }

        function speakOnce(message) {
            return new Promise((resolve) => {
                if (!('speechSynthesis' in window)) {
                    resolve();
                    return;
                }

                const utterance = new SpeechSynthesisUtterance(message);
                utterance.lang = 'id-ID';
                utterance.rate = 0.88;
                utterance.pitch = 1;
                utterance.volume = 1;
                utterance.onend = resolve;
                utterance.onerror = resolve;
                window.speechSynthesis.speak(utterance);
            });
        }

        function spokenQueueCode(queueCode) {
            const digitWords = {
                '0': 'nol',
                '1': 'satu',
                '2': 'dua',
                '3': 'tiga',
                '4': 'empat',
                '5': 'lima',
                '6': 'enam',
                '7': 'tujuh',
                '8': 'delapan',
                '9': 'sembilan',
            };
            const value = String(queueCode || '').trim().toUpperCase();
            const match = value.match(/^([A-Z]+)(\d+)$/);

            if (!match) return value;

            return `${match[1]} ${Array.from(match[2]).map((digit) => digitWords[digit] || digit).join(' ')}`;
        }

        async function speakCall(lane, call) {
            if (!soundEnabled || !call) return;

            const destination = call.room || lane.name || 'ruangan yang tersedia';
            const participant = call.spoken_name ? `, atas nama ${call.spoken_name}` : '';
            const message = `Nomor antrian ${spokenQueueCode(call.queue_code)}${participant}, menuju ${destination}`;

            for (let repeat = 0; repeat < announcementRepeatCount; repeat++) {
                if (!soundEnabled) return;
                beep();
                await wait(450);
                await speakOnce(message);
                if (repeat < announcementRepeatCount - 1) await wait(900);
            }
        }

        function queueCallAnnouncement(lane, call) {
            announcementQueue = announcementQueue
                .then(() => speakCall(lane, call))
                .catch((error) => console.warn('Queue announcement failed', error));
        }

        function announceNewCalls(payload) {
            if (!soundEnabled) return null;

            const seen = readSeenCalls();
            let latestKey = null;

            currentCallEntries(payload).forEach(({ lane, call }) => {
                const key = callKey(lane, call);
                if (!seen.has(key)) {
                    seen.add(key);
                    latestKey = key;
                    queueCallAnnouncement(lane, call);
                }
            });

            writeSeenCalls(seen);
            return latestKey;
        }

        async function refreshBoard() {
            try {
                const response = await fetch(boardEndpoint, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) throw new Error('Failed fetching board payload');
                const payload = await response.json();
                const latestKey = announceNewCalls(payload);
                renderHealthPanel(payload);
                renderInterviewPanel(payload);
                renderLanes(payload, latestKey);
            } catch (error) {
                console.warn('Board refresh failed; retrying on the next interval.', error);
            }
        }

        function setSoundEnabled(enabled, persist = true) {
            soundEnabled = enabled;
            if (persist) localStorage.setItem(soundStorageKey, enabled ? 'true' : 'false');
            soundToggle.classList.toggle('is-on', enabled);
            soundToggle.innerHTML = enabled
                ? '<i class="bx bx-volume-full"></i> Suara Aktif'
                : '<i class="bx bx-volume-full"></i> Aktifkan Suara';
            soundState.textContent = enabled ? 'Suara aktif' : 'Suara nonaktif';
        }

        soundToggle.addEventListener('click', async () => {
            if (soundEnabled) {
                window.speechSynthesis?.cancel();
                setSoundEnabled(false);
                return;
            }

            try {
                audioContext ??= new (window.AudioContext || window.webkitAudioContext)();
                if (audioContext.state === 'suspended') await audioContext.resume();
            } catch (error) {
                // Some browsers may not support Web Audio; speech can still work.
            }

            setSoundEnabled(true);
            markCurrentCallsSeen(initialPayload);
            beep();

            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance('Suara panggilan antrian aktif');
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                window.speechSynthesis.speak(utterance);
            }
        });

        setSoundEnabled(soundEnabled, false);
        markCurrentCallsSeen(initialPayload);
        renderHealthPanel(initialPayload);
        renderInterviewPanel(initialPayload);
        renderLanes(initialPayload);
        setInterval(tickClock, 1000);
        setInterval(refreshBoard, 10000);
    </script>
</body>
</html>
