<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\SoalTes;
use Illuminate\Http\Request;
use App\Models\PilihanJawaban;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePilihanJawabanRequest;
use App\Http\Requests\UpdatePilihanJawabanRequest;

class PilihanJawabanController extends Controller
{
    public function store(Request $request, SoalTes $soalTes)
    {
        $request->validate([
            'teks_pilihan' => 'required|string',
            'benar' => 'nullable|boolean',
        ]);

        // Ambil jumlah pilihan yang sudah ada
        $count = $soalTes->pilihanJawaban()->count();
        $kode = chr(65 + $count); // 65 = 'A' di ASCII → A, B, C, D, E...

        // Kalau ini pilihan benar, set semua pilihan lain jadi false
        if ($request->boolean('benar')) {
            $soalTes->pilihanJawaban()->update(['benar' => false]);
        }

        $soalTes->pilihanJawaban()->create([
            'kode_pilihan' => $kode,
            'teks_pilihan' => $request->teks_pilihan,
            'benar' => $request->boolean('benar'),
        ]);

        return back()->with('success', 'Pilihan berhasil ditambahkan!');
    }



    public function destroy(PilihanJawaban $pilihanJawaban)
    {
        $pilihanJawaban->delete();
        return back()->with('success', 'Pilihan jawaban berhasil dihapus.');
    }
}
