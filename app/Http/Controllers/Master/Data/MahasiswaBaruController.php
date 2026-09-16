<?php

namespace App\Http\Controllers\Master\Data;

use App\Exports\MahasiswaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUpdateMahasiswaBaruRequest;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Services\Audit\ActivityLogger;
use App\Services\Pmb\MahasiswaBaruService;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaBaruController extends Controller
{
    public function __construct(
        private readonly MahasiswaBaruService $mahasiswaBaruService,
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(Request $request)
    {
        $data = $this->mahasiswaBaruService->paginate($request->only([
            'search',
            'periode',
            'gelombang',
            'status_pemb',
        ]));

        if ($request->ajax()) {
            return view('admin_dashboard.pages.mahasiswa._table', compact('data'))->render();
        }

        return view('admin_dashboard.pages.mahasiswa.index', array_merge(
            ['data' => $data],
            $this->mahasiswaBaruService->formOptions()
        ));
    }

    public function filter(Request $request)
    {
        $data = $this->mahasiswaBaruService->paginate($request->only([
            'search',
            'periode',
            'gelombang',
            'status_pemb',
        ]));

        return response()->json([
            'table' => view('admin_dashboard.pages.mahasiswa._table', compact('data'))->render(),
            'cards' => view('admin_dashboard.pages.mahasiswa._cards', compact('data'))->render(),
            'pagination' => view('admin_dashboard.pages.mahasiswa._pagination', compact('data'))->render(),
        ]);
    }

    public function gelombang2(Request $request)
    {
        $data = $this->mahasiswaBaruService->paginateGelombangAktif(2, $request->only('search'));

        return view('admin_dashboard.pages.mahasiswa.index-gel-2', compact('data'));
    }

    public function gelombang3(Request $request)
    {
        $data = $this->mahasiswaBaruService->paginateGelombangAktif(3, $request->only('search'));

        return view('admin_dashboard.pages.mahasiswa.index-gel-3', compact('data'));
    }

    public function create()
    {
        return view('admin_dashboard.pages.mahasiswa.form', array_merge(
            $this->mahasiswaBaruService->formOptions(),
            [
                'action_url' => route('admin.mahasiswa-baru.store'),
                'method' => 'POST',
                'mahasiswa' => null,
            ]
        ));
    }

    public function store(StoreUpdateMahasiswaBaruRequest $request)
    {
        $this->mahasiswaBaruService->create($request->validated());

        return redirect()
            ->route('admin.mahasiswa-baru.index')
            ->with('toastSuccess', 'Mahasiswa baru berhasil ditambahkan.');
    }

    public function show($id)
    {
        return $this->detail($id);
    }

    public function detail($id)
    {
        $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);

        return view('admin_dashboard.pages.mahasiswa.detail', array_merge(
            $this->mahasiswaBaruService->formOptions($mahasiswa),
            [
                'action_url' => route('admin.mahasiswa-baru.update', $mahasiswa->id),
                'method' => 'PUT',
                'mahasiswa' => $mahasiswa,
            ]
        ));
    }

    public function edit($id)
    {
        $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);

        return view('admin_dashboard.pages.mahasiswa.form', array_merge(
            $this->mahasiswaBaruService->formOptions($mahasiswa),
            [
                'action_url' => route('admin.mahasiswa-baru.update', $mahasiswa->id),
                'method' => 'PUT',
                'mahasiswa' => $mahasiswa,
            ]
        ));
    }

    public function update(StoreUpdateMahasiswaBaruRequest $request, $id)
    {
        $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
        $this->mahasiswaBaruService->update($mahasiswa, $request->validated());

        return redirect()
            ->route('admin.mahasiswa-baru.index')
            ->with('toastSuccess', 'Data mahasiswa berhasil diperbarui.');
    }

    public function generatePassword($id)
    {
        $mahasiswa = User::where('role', User::USER_ROLE)->findOrFail($id);
        $password = $this->randomPassword();

        $mahasiswa->forceFill([
            'password' => Hash::make($password),
            'password_plaintext' => $password,
        ])->save();

        return redirect()->route('admin.mahasiswa-baru.detail', $id)
            ->with('toastSuccess', "Password baru untuk {$mahasiswa->name}: {$password}");
    }

    public function generatePasswordsBulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $students = User::where('role', User::USER_ROLE)
            ->whereIn('id', $validated['ids'])
            ->get();

        foreach ($students as $mahasiswa) {
            $password = $this->randomPassword();
            $mahasiswa->forceFill([
                'password' => Hash::make($password),
                'password_plaintext' => $password,
            ])->save();
        }

        return redirect()->route('admin.mahasiswa-baru.index')
            ->with('toastSuccess', "Password berhasil dibuat untuk {$students->count()} mahasiswa.");
    }

    private function randomPassword(): string
    {
        return Str::random(7) . random_int(0, 9) . '!';
    }

    public function quickEdit($id)
    {
        try {
            $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
            $mahasiswa->load(['periode', 'gelombang', 'jurusan']);

            $options = $this->mahasiswaBaruService->formOptions($mahasiswa);

            return response()->json([
                'success' => true,
                'data' => $mahasiswa,
                'options' => [
                    'periodes' => $options['periodes']->map(fn($p) => ['id' => $p->id, 'deskripsi' => $p->deskripsi]),
                    'gelombangs' => $options['gelombangs']->map(fn($g) => ['id' => $g->id, 'nama_gelombang' => $g->nama_gelombang, 'periode_id' => $g->periode_id]),
                    'jurusan' => $options['jurusan']->map(fn($j) => ['id' => $j->id, 'nama_jurusan' => $j->nama_jurusan]),
                    'pmbStatusOptions' => $options['pmbStatusOptions'],
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data mahasiswa: ' . $e->getMessage()
            ], 404);
        }
    }

    public function quickUpdate(\App\Http\Requests\Admin\QuickUpdateMahasiswaRequest $request, $id)
    {
        try {
            $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
            
            // Perbarui data via service layer
            $mahasiswa = $this->mahasiswaBaruService->update($mahasiswa, $request->validated());

            // Reload data pendaftar & relasi
            $mahasiswa->load(['periode', 'gelombang', 'jurusan']);

            // Render baris dan kartu DOM terupdate
            $rowHtml = view('admin_dashboard.pages.mahasiswa._row', [
                'mahasiswa' => $mahasiswa,
                'row_index' => $request->input('row_index')
            ])->render();
            $cardHtml = view('admin_dashboard.pages.mahasiswa._card', [
                'mahasiswa' => $mahasiswa,
                'row_index' => $request->input('row_index')
            ])->render();

            return response()->json([
                'success' => true,
                'message' => 'Data mahasiswa berhasil diperbarui secara cepat.',
                'data' => $mahasiswa,
                'rowHtml' => $rowHtml,
                'cardHtml' => $cardHtml
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data mahasiswa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
        $this->mahasiswaBaruService->delete($mahasiswa);

        return redirect()
            ->route('admin.mahasiswa-baru.index')
            ->with('toastSuccess', 'Mahasiswa baru berhasil dihapus.');
    }

    public function restore($id)
    {
        $this->mahasiswaBaruService->restore($id);

        return back()->with('toastSuccess', 'Data mahasiswa berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $this->mahasiswaBaruService->forceDelete($id);

        return back()->with('toastSuccess', 'Data mahasiswa berhasil dihapus permanen.');
    }

    public function updateStatus($id)
    {
        try {
            $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
            $this->mahasiswaBaruService->advanceReviewStatus($mahasiswa);

            return redirect()
                ->route('admin.mahasiswa-baru.index')
                ->with('toastSuccess', 'Status PMB berhasil diperbarui.');
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->with('toastError', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui status PMB', ['error' => $e->getMessage()]);

            return back()->with('toastError', 'Terjadi kesalahan saat memperbarui status.');
        }
    }

    private function buildMahasiswaQuery(Request $request)
    {
        return $this->mahasiswaBaruService->exportQuery($request->only([
            'search',
            'periode',
            'gelombang',
            'status_pemb'
        ]));
    }

    public function cetak(Request $request)
    {
        $query = $this->buildMahasiswaQuery($request);
        $data = $query->get();

        // Get filter labels for Kop Laporan
        $periodeName = 'Semua';
        if ($request->filled('periode')) {
            $p = \App\Models\Periode::find($request->periode);
            if ($p) $periodeName = $p->deskripsi;
        }

        $gelombangName = 'Semua';
        if ($request->filled('gelombang')) {
            $g = \App\Models\Gelombang::find($request->gelombang);
            if ($g) $gelombangName = $g->nama_gelombang;
        }

        $statusLabel = 'Semua';
        if ($request->filled('status_pemb')) {
            $s = \App\Enums\PmbStatus::fromValue((int) $request->status_pemb);
            if ($s) $statusLabel = $s->label();
        }

        $adminName = auth()->user()->name ?? auth()->guard('admin')->user()->name ?? 'Admin';

        $meta = [
            'periode' => $periodeName,
            'gelombang' => $gelombangName,
            'status' => $statusLabel,
            'admin' => $adminName,
            'date' => now()->translatedFormat('d F Y H:i')
        ];

        $this->activityLogger->log(
            'pmb',
            'export.pdf',
            'Admin mengekspor data mahasiswa PMB ke PDF',
            null,
            [],
            [],
            $request->only(['search', 'periode', 'gelombang', 'status_pemb'])
        );

        $pdf = Pdf::loadView('admin_dashboard.pages.mahasiswa.pdf', compact('data', 'meta'))
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ])
            ->setOption('font_size', 9);

        $filename = 'laporan-pmb-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function cetakExcel(Request $request)
    {
        $query = $this->buildMahasiswaQuery($request);

        // Get filter labels for metadata
        $periodeName = 'Semua';
        if ($request->filled('periode')) {
            $p = \App\Models\Periode::find($request->periode);
            if ($p) $periodeName = $p->deskripsi;
        }

        $gelombangName = 'Semua';
        if ($request->filled('gelombang')) {
            $g = \App\Models\Gelombang::find($request->gelombang);
            if ($g) $gelombangName = $g->nama_gelombang;
        }

        $statusLabel = 'Semua';
        if ($request->filled('status_pemb')) {
            $s = \App\Enums\PmbStatus::fromValue((int) $request->status_pemb);
            if ($s) $statusLabel = $s->label();
        }

        $adminName = auth()->user()->name ?? auth()->guard('admin')->user()->name ?? 'Admin';

        $filters = [
            'periode_name' => $periodeName,
            'gelombang_name' => $gelombangName,
            'status_label' => $statusLabel,
            'admin_name' => $adminName
        ];

        $this->activityLogger->log(
            'pmb',
            'export.excel',
            'Admin mengekspor data mahasiswa PMB ke Excel',
            null,
            [],
            [],
            $request->only(['search', 'periode', 'gelombang', 'status_pemb'])
        );

        $filename = 'laporan-pmb-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new MahasiswaExport($query, $filters),
            $filename
        );
    }


    public function sendEmailLulus(Request $request, $id)
    {
        try {
            $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
            $this->mahasiswaBaruService->sendKelulusanEmail($mahasiswa);

            return back()->with('toastSuccess', "Email pemberitahuan kelulusan untuk {$mahasiswa->name} masuk antrean pengiriman.");
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->with('toastError', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('toastError', 'Gagal mengirim email kelulusan.');
        }
    }

    public function sendEmailTidakLulus(Request $request, $id)
    {
        try {
            $mahasiswa = $this->mahasiswaBaruService->findOrFail($id);
            $this->mahasiswaBaruService->sendTidakLulusEmail($mahasiswa);

            return back()->with('toastSuccess', "Email pemberitahuan tidak lulus untuk {$mahasiswa->name} masuk antrean pengiriman.");
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->with('toastError', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('toastError', 'Gagal mengirim email tidak lulus.');
        }
    }

    public function getKabupaten($provinsiId)
    {
        return response()->json(Kabupaten::where('id_prov', $provinsiId)->orderBy('nama_kab')->get());
    }

    public function getKecamatan($kabupatenId)
    {
        return response()->json(Kecamatan::where('id_kab', $kabupatenId)->orderBy('nama_kec')->get());
    }

    public function getKelurahan($kecamatanId)
    {
        return response()->json(Kelurahan::where('id_kec', $kecamatanId)->orderBy('nama_kel')->get());
    }
}
