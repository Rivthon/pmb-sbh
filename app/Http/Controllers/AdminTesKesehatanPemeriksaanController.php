<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TesKesehatanAnamnesa;
use Illuminate\Support\Facades\Auth;
use App\Models\TesKesehatanPemeriksaan;

class AdminTesKesehatanPemeriksaanController extends Controller
{
    private const OFFICIAL_PHYSICAL_EXAM_ITEMS = [
        'konjungtiva',
        'ikhterik',
        'buta_warna',
        'respon_pendengaran',
        'kelengkapan_jari_atas',
        'tremor',
        'bekas_luka_sayatan',
        'kidal',
        'labioschizis',
        'vokal',
        'cat_rambut',
        'tyroid',
        'bunyi_irama_jantung',
        'bunyi_paru',
        'respirasi',
        'simetris_bawah',
        'kelengkapan_jari_bawah',
        'motorik_cara_jalan',
        'bentuk_kaki',
        'tes_urine',
    ];

    public function index()
    {
        $pemeriksaans = TesKesehatanPemeriksaan::with(['anamnesa.user'])
            ->latest()
            ->paginate(10);

        return view('admin.tes_kesehatan.pemeriksaan_index', compact('pemeriksaans'));
    }

    /**
     * Form pemeriksaan baru
     */
    public function create($anamnesaId)
    {
        $anamnesa = TesKesehatanAnamnesa::with('user')->findOrFail($anamnesaId);
        return view('admin.tes_kesehatan.pemeriksaan_create', compact('anamnesa'));
    }

    /**
     * Simpan hasil pemeriksaan baru
     */
    public function store(Request $request)
    {
        $rules = [
            'tes_kesehatan_anamnesa_id' => 'required|exists:tes_kesehatan_anamnesa,id',
            'nama_pemeriksa' => 'required|string|max:100',
            'tanggal_pemeriksaan' => 'required|date',
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'tekanan_darah' => 'nullable|string|max:50',
            'konjungtiva' => 'nullable|string|max:100',
            'ikhterik' => 'nullable|string|max:100',
            'buta_warna' => 'nullable|string|max:100',
            'respon_pendengaran' => 'nullable|string|max:100',
            'kelengkapan_jari_atas' => 'nullable|string|max:100',
            'tremor' => 'nullable|string|max:100',
            'kidal' => 'nullable|string|max:100',
            'bekas_luka_sayatan' => 'nullable|string|max:100',
            'bunyi_jantung' => 'nullable|string|max:100',
            'bunyi_paru' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ];

        foreach (self::OFFICIAL_PHYSICAL_EXAM_ITEMS as $item) {
            $rules["{$item}_kondisi"] = ['nullable', 'in:normal,kelainan'];
            $rules["{$item}_keterangan"] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $anamnesa = TesKesehatanAnamnesa::with('user.jurusan')->findOrFail($validated['tes_kesehatan_anamnesa_id']);
        if (!$anamnesa->user?->jurusan?->isD3Kebidanan()) {
            $validated['tes_urine_kondisi'] = null;
            $validated['tes_urine_keterangan'] = null;
        }

        $validated['admin_id'] = Auth::guard('admin')->id();

        TesKesehatanPemeriksaan::create($validated);

        // Update status anamnesa agar tidak "belum diperiksa"
        TesKesehatanAnamnesa::where('id', $validated['tes_kesehatan_anamnesa_id'])
            ->update(['status' => 'lulus']);

        return redirect()->route('admin.tes-kesehatan.pemeriksaan.index')
            ->with('success', 'Hasil pemeriksaan berhasil disimpan!');
    }

    /**
     * Detail pemeriksaan
     */
    public function show($id)
    {
        $pemeriksaan = TesKesehatanPemeriksaan::with(['anamnesa.user'])->findOrFail($id);
        return view('admin.tes_kesehatan.pemeriksaan_show', compact('pemeriksaan'));
    }

    /**
     * Edit pemeriksaan
     */
    public function edit($id)
    {
        $pemeriksaan = TesKesehatanPemeriksaan::with(['anamnesa.user'])->findOrFail($id);
        return view('admin.tes_kesehatan.pemeriksaan_edit', compact('pemeriksaan'));
    }

    /**
     * Update hasil pemeriksaan
     */
    public function update(Request $request, $id)
    {
        $pemeriksaan = TesKesehatanPemeriksaan::findOrFail($id);

        $rules = [
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'tekanan_darah' => 'nullable|string|max:50',
            'catatan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ];

        foreach (self::OFFICIAL_PHYSICAL_EXAM_ITEMS as $item) {
            $rules["{$item}_kondisi"] = ['nullable', 'in:normal,kelainan'];
            $rules["{$item}_keterangan"] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $pemeriksaan->loadMissing('anamnesa.user.jurusan');
        if (!$pemeriksaan->anamnesa?->user?->jurusan?->isD3Kebidanan()) {
            $validated['tes_urine_kondisi'] = null;
            $validated['tes_urine_keterangan'] = null;
        }

        $pemeriksaan->update($validated);

        return redirect()->route('admin.tes-kesehatan.pemeriksaan.show', $pemeriksaan->id)
            ->with('success', 'Data pemeriksaan berhasil diperbarui!');
    }

    /**
     * Hapus pemeriksaan
     */
    public function destroy($id)
    {
        $pemeriksaan = TesKesehatanPemeriksaan::findOrFail($id);
        $pemeriksaan->delete();

        return redirect()->route('admin.tes-kesehatan.pemeriksaan.index')
            ->with('success', 'Data pemeriksaan berhasil dihapus.');
    }
}
