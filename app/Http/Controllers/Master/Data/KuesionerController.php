<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\Kuesioner; // Menggunakan model Kuesioner
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KuesionerController extends Controller
{
    public function index()
    {
        $kuesioners = Kuesioner::all();
        return view('admin_dashboard.pages.kusioner.index', compact('kuesioners'));
    }

    public function create()
    {
        $action_url = route('admin.kuesioner.store');  // URL untuk menyimpan kuesioner baru
        $method = 'POST'; // Method POST untuk penambahan
        $kuesioner = null;  // Tidak ada data untuk kuesioner saat create
        return view('admin_dashboard.pages.kusioner.form', compact('action_url', 'method', 'kuesioner'));
    }

    // Menyimpan data kuesioner baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nama_kusioner' => 'required', // Fixed the spelling here
        ]);

        Kuesioner::create($request->all());
        return redirect()->route('admin.kuesioner.index')
            ->with('success', 'Kuesioner berhasil ditambahkan.');
    }

    // Menampilkan detail kuesioner
    public function show($id) // Changed the parameter to $id
    {
        $kuesioner = Kuesioner::findOrFail($id); // Retrieve the Kuesioner instance using the ID
        $title = 'Edit Kuesioner';
        $action_url = route('admin.kuesioner.update', $kuesioner->id);  // URL untuk mengupdate kuesioner
        $method = 'PUT';  // Method PUT untuk update
        return view('admin_dashboard.pages.kusioner.form', compact('action_url', 'method', 'kuesioner'));
    }

    // Mengupdate data kuesioner yang sudah ada
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kusioner' => 'required',
        ]);

        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->update($request->all());

        return redirect()->route('admin.kuesioner.index')
            ->with('toastSuccess', 'Kuesioner berhasil diupdate.');
    }

    // Menghapus data kuesioner
    public function destroy($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->delete();

        return redirect()->route('admin.kuesioner.index')
            ->with('toastSuccess', 'Kuesioner berhasil dihapus.');
    }
}
