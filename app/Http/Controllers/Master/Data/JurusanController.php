<?php

namespace App\Http\Controllers\Master\Data;
use App\Models\Jurusan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::all();
        return view('admin_dashboard.pages.jurusan.index', compact('jurusans'));
    }
    public function create()
    {
        $action_url = route('admin.jurusan.store');  // URL untuk menyimpan jurusan baru
        $method = 'POST'; // Method POST untuk penambahan
        $jurusan = null;  // Tidak ada data untuk jurusan saat create
        return view('admin_dashboard.pages.jurusan.form', compact('action_url', 'method', 'jurusan'));
    }
    // Menyimpan data jurusan baru ke database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kd_jurusan' => 'required|string|max:50|unique:jurusan,kd_jurusan',
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Jurusan::create($validated);
        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return $this->show($jurusan);
    }

    // Menampilkan detail jurusan
     public function show(Jurusan $jurusan)
    {
        $title = 'Edit Jurusan';
        $action_url = route('admin.jurusan.update', $jurusan->id);  // URL untuk mengupdate jurusan
        $method = 'PUT';  // Method PUT untuk update
        return view('admin_dashboard.pages.jurusan.form', compact('action_url', 'method', 'jurusan'));
    }

    // Mengupdate data jurusan yang sudah ada
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $jurusan = Jurusan::findOrFail($id);
        $jurusan->update($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('toastSuccess', 'Jurusan berhasil diupdate.');
    }

    // Menghapus data jurusan
    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('toastSuccess', 'Jurusan berhasil dihapus.');
    }
}
