<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\PekerjaanIbu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PekerjaanIbuController extends Controller
{
    public function index()
    {
        $pekerjaanIbu = PekerjaanIbu::all();
        return view('admin_dashboard.pages.pekerjaanIbu.index', compact('pekerjaanIbu'));
    }

    public function create()
    {
        $action_url = route('admin.pekerjaan-ibu.store');  // URL for storing new Pekerjaan Ayah
        $method = 'POST'; // Method POST for addition
        $pekerjaanIbu = null;  // No data for Pekerjaan Ayah when creating
        return view('admin_dashboard.pages.pekerjaanIbu.form', compact('action_url', 'method', 'pekerjaanIbu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pek_ibu' => 'required',
        ]);

        PekerjaanIbu::create($request->all());
        return redirect()->route('admin.pekerjaan-ibu.index')
            ->with('success', 'Pekerjaan Ibu berhasil ditambahkan.');
    }

    public function show(pekerjaanIbu $pekerjaanIbu)
    {
        $title = 'Edit Pekerjaan Ibu';
        $action_url = route('admin.pekerjaan-ibu.update', $pekerjaanIbu->id);
        $method = 'PUT';  // Method PUT for update
        return view('admin_dashboard.pages.pekerjaanIbu.form', compact('action_url', 'method', 'pekerjaanIbu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pek_ibu' => 'required',
        ]);

        $pekerjaanIbu = PekerjaanIbu::findOrFail($id);
        $pekerjaanIbu->update($request->all());

        return redirect()->route('admin.pekerjaan-ibu.index')
            ->with('toastSuccess', 'Pekerjaan Ibu berhasil diupdate.');
    }

    public function destroy($id)
    {
        $pekerjaanIbu = PekerjaanIbu::findOrFail($id);
        $pekerjaanIbu->delete();

        return redirect()->route('admin.pekerjaan-ibu.index')
            ->with('toastSuccess', 'Pekerjaan Ibu berhasil dihapus.');
    }
}
