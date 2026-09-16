<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\PekerjaanAyah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PekerjaanAyahController extends Controller
{
    public function index()
    {
        $pekerjaanAyah = PekerjaanAyah::all();
        return view('admin_dashboard.pages.pekerjaanAyah.index', compact('pekerjaanAyah'));
    }

    public function create()
    {
        $action_url = route('admin.pekerjaan-ayah.store');  // URL for storing new Pekerjaan Ayah
        $method = 'POST'; // Method POST for addition
        $pekerjaanAyah = null;  // No data for Pekerjaan Ayah when creating
        return view('admin_dashboard.pages.pekerjaanAyah.form', compact('action_url', 'method', 'pekerjaanAyah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pek_ayah' => 'required',
        ]);

        PekerjaanAyah::create($request->all());
        return redirect()->route('admin.pekerjaan-ayah.index')
            ->with('success', 'Pekerjaan Ayah berhasil ditambahkan.');
    }

    public function show(PekerjaanAyah $pekerjaanAyah)
    {
        $title = 'Edit Pekerjaan Ayah';
        $action_url = route('admin.pekerjaan-ayah.update', $pekerjaanAyah->id);
        $method = 'PUT';  // Method PUT for update
        return view('admin_dashboard.pages.pekerjaanAyah.form', compact('action_url', 'method', 'pekerjaanAyah'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pek_ayah' => 'required',
        ]);

        $pekerjaanAyah = PekerjaanAyah::findOrFail($id);
        $pekerjaanAyah->update($request->all());

        return redirect()->route('admin.pekerjaan-ayah.index')
            ->with('toastSuccess', 'Pekerjaan Ayah berhasil diupdate.');
    }

    public function destroy($id)
    {
        $pekerjaanAyah = PekerjaanAyah::findOrFail($id);
        $pekerjaanAyah->delete();

        return redirect()->route('admin.pekerjaan-ayah.index')
            ->with('toastSuccess', 'Pekerjaan Ayah berhasil dihapus.');
    }
}
