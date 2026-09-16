<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\SoalTes;
use App\Models\TesTulis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSoalTesRequest;
use App\Http\Requests\UpdateSoalTesRequest;
use App\Models\KategoriSoal;

class SoalTesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TesTulis $tesTulis)
    {
        $soal = $tesTulis->soal()->with(['pilihanJawaban', 'kategori'])->get();
        return view('admin.tes_tulis.soal.index', compact('tesTulis', 'soal'));
    }

    public function create(TesTulis $tesTulis)
    {
        // Ambil semua kategori untuk ditampilkan di form select
        $kategoriSoal = KategoriSoal::orderBy('nama_kategori')->get();

        return view('admin.tes_tulis.soal.create', compact('tesTulis', 'kategoriSoal'));
    }

    public function store(Request $request, TesTulis $tesTulis)
    {
        $validated = $request->validate([
            'pertanyaan'   => 'required|string',
            'kategori_id'  => 'nullable|exists:kategori_soal,id', // validasi kategori
            'skor'         => 'nullable|numeric|min:0',
            'cerita_bacaan' => 'nullable|string',
        ]);

        $tesTulis->soal()->create($validated);

        return redirect()
            ->route('admin.soal.index', $tesTulis->id)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(TesTulis $tesTulis, SoalTes $soalTes)
    {
        // Ambil semua kategori untuk ditampilkan di dropdown edit
        $kategoriSoal = KategoriSoal::orderBy('nama_kategori')->get();

        return view('admin.tes_tulis.soal.edit', compact('tesTulis', 'soalTes', 'kategoriSoal'));
    }

    public function update(Request $request, TesTulis $tesTulis, SoalTes $soalTes)
    {
        $validated = $request->validate([
            'pertanyaan'   => 'required|string',
            'kategori_id'  => 'nullable|exists:kategori_soal,id',
            'skor'         => 'nullable|numeric|min:0',
            'cerita_bacaan' => 'nullable|string',
        ]);

        $soalTes->update($validated);

        return redirect()
            ->route('admin.soal.index', $tesTulis->id)
            ->with('success', 'Soal berhasil diperbarui.');
    }


    public function destroy(TesTulis $tesTulis, SoalTes $soalTes)
    {
        $soalTes->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }
}
