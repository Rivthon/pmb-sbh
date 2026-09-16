<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\TesTulis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTesTulisRequest;
use App\Http\Requests\UpdateTesTulisRequest;

class TesTulisController extends Controller
{
    public function index()
    {
        $tesTulis = TesTulis::latest()->paginate(10);
        return view('admin.tes_tulis.index', compact('tesTulis'));
    }

    public function create()
    {
        return view('admin.tes_tulis.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tes' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1',
            'skor_lulus' => 'required|numeric|min:0|max:100',
            'acak_soal' => 'nullable|boolean',
            'acak_pilihan' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);

        TesTulis::create([
            ...$validated,
            'acak_soal' => $request->boolean('acak_soal'),
            'acak_pilihan' => $request->boolean('acak_pilihan'),
            'status_aktif' => $request->boolean('status_aktif'),
        ]);

        return redirect()
            ->route('admin.tes-tulis.index')
            ->with('success', 'Tes tulis berhasil ditambahkan!');
    }
    public function edit(TesTulis $tesTulis)
    {
        return view('admin.tes_tulis.edit', compact('tesTulis'));
    }

    public function update(Request $request, TesTulis $tesTulis)
    {
        $validated = $request->validate([
            'nama_tes' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1',
            'skor_lulus' => 'required|numeric|min:0|max:100',
            'acak_soal' => 'nullable|boolean',
            'acak_pilihan' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);

        $tesTulis->update([
            ...$validated,
            'acak_soal' => $request->boolean('acak_soal'),
            'acak_pilihan' => $request->boolean('acak_pilihan'),
            'status_aktif' => $request->boolean('status_aktif'),
        ]);

        return redirect()
            ->route('admin.tes-tulis.index')
            ->with('success', 'Tes tulis berhasil diperbarui!');
    }

    public function destroy(TesTulis $tesTulis)
    {
        $tesTulis->delete();
        return back()->with('success', 'Tes Tulis berhasil dihapus.');
    }
}
