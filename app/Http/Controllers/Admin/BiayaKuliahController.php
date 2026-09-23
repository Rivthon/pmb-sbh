<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiayaKuliah;
use Illuminate\Http\Request;

class BiayaKuliahController extends Controller
{
    /**
     * Daftar prodi & ringkasan biaya kuliah.
     */
    public function index()
    {
        $prodis = BiayaKuliah::select('prodi_key', 'prodi_nama', 'jumlah_semester', 'is_visible')
            ->distinct()
            ->orderByRaw("FIELD(prodi_key, 'd3', 'farmasi', 'karyawan', 'gizi')")
            ->get();

        // Ambil data biaya gelombang 1 per prodi untuk preview
        $previewData = [];
        foreach ($prodis as $prodi) {
            $previewData[$prodi->prodi_key] = BiayaKuliah::where('prodi_key', $prodi->prodi_key)
                ->where('gelombang', 1)
                ->orderBy('semester')
                ->pluck('biaya')
                ->toArray();
        }

        return view('admin.biaya-kuliah.index', compact('prodis', 'previewData'));
    }

    /**
     * Form edit biaya per prodi (semua gelombang & semester).
     */
    public function edit(string $prodiKey)
    {
        $prodi = BiayaKuliah::where('prodi_key', $prodiKey)->firstOrFail();

        $biayaData = BiayaKuliah::where('prodi_key', $prodiKey)
            ->orderBy('gelombang')
            ->orderBy('semester')
            ->get()
            ->groupBy('gelombang');

        return view('admin.biaya-kuliah.edit', [
            'prodiKey'       => $prodiKey,
            'prodiNama'      => $prodi->prodi_nama,
            'jumlahSemester' => $prodi->jumlah_semester,
            'biayaData'      => $biayaData,
        ]);
    }

    /**
     * Bulk update biaya per prodi.
     */
    public function update(Request $request, string $prodiKey)
    {
        $request->validate([
            'biaya'     => 'required|array',
            'biaya.*.*' => 'required|numeric|min:0',
        ]);

        $biayaInput = $request->input('biaya'); // biaya[gelombang][semester] = value

        foreach ($biayaInput as $gelombang => $semesters) {
            foreach ($semesters as $semester => $nilai) {
                BiayaKuliah::where('prodi_key', $prodiKey)
                    ->where('gelombang', $gelombang)
                    ->where('semester', $semester)
                    ->update(['biaya' => $nilai]);
            }
        }

        return redirect()
            ->route('admin.biaya-kuliah.index')
            ->with('success', 'Biaya kuliah berhasil diperbarui.');
    }

    public function toggleVisibility(string $prodiKey)
    {
        $prodi = BiayaKuliah::where('prodi_key', $prodiKey)->firstOrFail();
        $isVisible = !$prodi->is_visible;

        BiayaKuliah::where('prodi_key', $prodiKey)->update(['is_visible' => $isVisible]);

        return redirect()->route('admin.biaya-kuliah.index')->with(
            'success',
            $prodi->prodi_nama . ($isVisible ? ' ditampilkan' : ' disembunyikan') . ' dari halaman utama.'
        );
    }
}
