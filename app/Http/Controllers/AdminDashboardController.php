<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Periode;
use App\Models\Gelombang;
use App\Models\Kuesioner;
use App\Models\ActivityLog;
use App\Models\PmbTestSession;
use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{

    public function index()
    {
        if ($redirect = $this->redirectOnlineSelectionManager()) {
            return $redirect;
        }

        if ($redirect = $this->redirectWrittenTestOnlyOfficer()) {
            return $redirect;
        }

        if ($redirect = $this->redirectInterviewOnlyOfficer()) {
            return $redirect;
        }

        if ($redirect = $this->redirectHealthOnlyOfficer()) {
            return $redirect;
        }

        // === 1. Ambil periode aktif ===
        $periodeAktif = Periode::where('status_periode', 'aktif')->first();

        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif yang ditemukan.');
        }

        // === 2. Ambil mahasiswa berdasarkan periode aktif ===
        $users = User::whereHas('periodeAktif')
            ->where('status_pemb', 4)
            ->orderBy('created_at', 'asc')
            ->get();

        // === 3. Total mahasiswa pada periode aktif ===
        $totalMahasiswa = User::whereHas('periodeAktif')->count();

        // === 4. Data kuesioner & hasil ===
        $kuesioners = Kuesioner::all();

        $kuesionerData = DB::table('users')
            ->select('kuesioner_id', DB::raw('count(*) as total'))
            ->where('status_pemb', 4)
            ->whereNotNull('kuesioner_id')
            ->groupBy('kuesioner_id')
            ->get();

        // === 5. Hitung mahasiswa per jurusan berdasarkan periode aktif ===
        $mahasiswaCounts = User::whereHas('periodeAktif')
            ->whereIn('jurusan_id', [1, 2, 3, 7])
            ->select('jurusan_id', DB::raw('count(*) as total'))
            ->groupBy('jurusan_id')
            ->pluck('total', 'jurusan_id');

        // === 6. Hitung mahasiswa berdasarkan status pembayaran ===
        $statusCounts = User::whereHas('periodeAktif')
            ->whereIn('status_pemb', [0, 1, 2, 3, 4])
            ->select('status_pemb', DB::raw('count(*) as total'))
            ->groupBy('status_pemb')
            ->pluck('total', 'status_pemb');

        // === 7. Data statistik status ===
        $statusStats = [
            [
                'nama'  => 'Belum Update Berkas',
                'count' => $statusCounts->get(0, 0),
                'icon'  => 'bx-folder-minus',
                'color' => 'warning'
            ],
            [
                'nama'  => 'Menunggu Validasi',
                'count' => $statusCounts->get(1, 0),
                'icon'  => 'bx-time-five',
                'color' => 'info'
            ],
            [
                'nama'  => 'Verified',
                'count' => $statusCounts->get(2, 0),
                'icon'  => 'bx-check-shield',
                'color' => 'success'
            ],
            [
                'nama'  => 'Lulus',
                'count' => $statusCounts->get(3, 0),
                'icon'  => 'bx-user-check',
                'color' => 'primary'
            ],
            [
                'nama'  => 'Tidak Lulus',
                'count' => $statusCounts->get(4, 0),
                'icon'  => 'bx-x-circle',
                'color' => 'danger'
            ]
        ];

        // === 8. Data statistik jurusan ===
        $jurusanStats = [
            [
                'nama'  => 'DIII Kebidanan',
                'count' => $mahasiswaCounts->get(3, 0),
                'icon'  => 'bxs-baby-carriage',
                'color' => 'primary'
            ],
            [
                'nama'  => 'S1 Farmasi',
                'count' => $mahasiswaCounts->get(1, 0),
                'icon'  => 'bxs-capsule',
                'color' => 'success'
            ],
            [
                'nama'  => 'S1 Gizi',
                'count' => $mahasiswaCounts->get(2, 0),
                'icon'  => 'bx-leaf',
                'color' => 'warning'
            ],
            [
                'nama'  => 'S1 Farmasi (Karyawan)',
                'count' => $mahasiswaCounts->get(7, 0),
                'icon'  => 'bxs-user-badge',
                'color' => 'info'
            ]
        ];

        // === 9. Data tambahan ===
        $periodes = Periode::all();
        $gelombangs = Gelombang::all();
        $jurusan = Jurusan::all();

        $bulanLabels = collect(range(1, 12))->map(function ($m) {
            return Carbon::create()->month($m)->translatedFormat('F');
        })->toArray();

        $recentActivities = ActivityLog::latest()
            ->limit(8)
            ->get();

        // === 10. Kirim ke view ===
        return view('admin.dashboard', compact(
            'users',
            'totalMahasiswa',
            'jurusanStats',
            'kuesioners',
            'kuesionerData',
            'periodes',
            'gelombangs',
            'statusStats',
            'jurusan',
            'periodeAktif',
            'bulanLabels',
            'recentActivities'
        ));
    }

    private function redirectOnlineSelectionManager()
    {
        $admin = auth('admin')->user();

        if (!$admin
            || !$admin->can(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)
            || $admin->can(AdminPermissions::PMB_EDIT)) {
            return null;
        }

        return redirect()->route('admin.pmb-queues.index');
    }

    private function redirectWrittenTestOnlyOfficer()
    {
        $admin = auth('admin')->user();

        if (!$admin
            || !$admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
            || $admin->can(AdminPermissions::PMB_EDIT)
            || $admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)
            || $admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)) {
            return null;
        }

        return redirect()->route('admin.pmb-queues.written-test.sessions');
    }

    private function redirectInterviewOnlyOfficer()
    {
        $admin = auth('admin')->user();

        if (!$admin
            || !$admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)
            || $admin->can(AdminPermissions::PMB_EDIT)
            || $admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
            || $admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)) {
            return null;
        }

        return redirect()->route('admin.pmb-queues.interview.sessions');
    }

    private function redirectHealthOnlyOfficer()
    {
        $admin = auth('admin')->user();

        if (!$admin
            || !$admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)
            || $admin->can(AdminPermissions::PMB_EDIT)
            || $admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
            || $admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)) {
            return null;
        }

        $session = PmbTestSession::query()
            ->whereIn('status', [PmbTestSession::STATUS_OPEN, PmbTestSession::STATUS_SCHEDULED])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [PmbTestSession::STATUS_OPEN])
            ->orderByDesc('starts_at')
            ->first();

        return $session
            ? redirect()->route('admin.pmb-queues.officer.kesehatan', $session)
            : redirect()->route('admin.tes-kesehatan.index');
    }

    public function getKuesionerChartData(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|integer|between:0,4',
                'periode' => 'nullable|integer|exists:periodes,id',
                'gelombang' => 'nullable|integer|exists:gelombangs,id',
                'jurusan' => 'nullable|integer|exists:jurusan,id',
            ]);

            $data = DB::table('kuesioners as k')
                ->leftJoin('users as u', 'k.id', '=', 'u.kuesioner_id')
                ->leftJoin('periodes as p', 'p.id', '=', 'u.periode_id')
                ->leftJoin('gelombangs as g', 'g.id', '=', 'u.gelombang_id')
                ->leftJoin('jurusan as j', 'j.id', '=', 'u.jurusan_id')
                ->when($request->filled('status'), fn ($query) => $query->where('u.status_pemb', $request->integer('status')))
                ->when($request->filled('periode'), fn ($query) => $query->where('p.id', $request->periode))
                ->when($request->filled('gelombang'), fn ($query) => $query->where('g.id', $request->gelombang))
                ->when($request->filled('jurusan'), fn ($query) => $query->where('j.id', $request->jurusan))
                ->select(
                    'k.id as kuesioner_id',
                    'k.nama_kusioner',
                    DB::raw('COUNT(u.id) as total')
                )
                ->groupBy('k.id', 'k.nama_kusioner')
                ->orderByDesc('total')
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Chart Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json([
                'error' => 'Server error',
                'message' => 'Gagal memuat data chart.'
            ], 500);
        }
    }

    public function getRegistrasiData(Request $request)
    {
        $start = $request->input('start'); // ex: 2025-01-01
        $end = $request->input('end');     // ex: 2025-06-30

        // dasar query: hanya user yang punya periodeAktif
        $query = User::whereHas('periodeAktif');

        // filter range jika ada
        if ($start && $end) {
            // pastikan format tanggal valid (optional: add validation)
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            // default: tahun berjalan
            $query->whereYear('created_at', now()->year);
        }

        // ambil count per bulan (1..12)
        $monthly = $query->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // labels fixed Jan..Dec
        $labels = collect(range(1, 12))->map(function ($m) {
            return Carbon::create()->month($m)->translatedFormat('F');
        })->toArray();

        // data array 12 bulan (index 0 => Jan ... 11 => Dec)
        $data = [];
        foreach (range(1, 12) as $m) {
            $data[] = $monthly[$m] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }
    public function getProdiChart(Request $request)
    {
        $gelombangId = $request->input('gelombang_id');

        $query = Jurusan::withCount(['mahasiswaBaru as total_pendaftar' => function ($q) use ($gelombangId) {
            if ($gelombangId) {
                $q->where('gelombang_id', $gelombangId);
            }
        }])->get();

        // Kuota per prodi (contoh — bisa dari tabel lain kalau ada)
        $kuota = [
            'S1 Gizi' => 120,
            'D3 Farmasi' => 100,
            'D3 Kebidanan' => 80,
        ];

        // 🎨 Warna berdasarkan kd_jurusan
        $colorMap = [
            '13211' => '#FFC107', // kuning
            '15401' => '#00CFE8', // biru
            '48201' => '#7367F0', // ungu
        ];

        $labels = [];
        $data = [];
        $colors = [];
        $kuotaData = [];

        foreach ($query as $j) {
            $labels[] = $j->nama_jurusan;
            $data[] = $j->total_pendaftar;
            $colors[] = $colorMap[$j->kd_jurusan] ?? '#CCCCCC'; // default abu-abu kalau ga ada
            $kuotaData[] = $kuota[$j->nama_jurusan] ?? '-';
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'kuota' => $kuotaData,
            'colors' => $colors,
        ]);
    }
}
