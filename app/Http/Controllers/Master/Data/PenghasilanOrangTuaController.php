<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\PenghasilanOrangTua;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PenghasilanOrangTuaController extends Controller
{
    public function index()
    {
        $penghasilanOrtu = PenghasilanOrangTua::all();
        return view('admin_dashboard.pages.penghasilanOrtu.index', compact('penghasilanOrtu'));
    }

    public function create()
    {
        $action_url = route('admin.penghasilan-orang-tua.store');  // URL for storing new Penghasilan Orang Tua
        $method = 'POST'; // Method POST for addition
        $penghasilanOrtu = null;  // No data for Penghasilan Orang Tua when creating
        return view('admin_dashboard.pages.penghasilanOrtu.form', compact('action_url', 'method', 'penghasilanOrtu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peng' => 'required',
        ]);

        PenghasilanOrangTua::create($request->all());
        return redirect()->route('admin.penghasilan-orang-tua.index')
            ->with('success', 'Penghasilan Orang Tua berhasil ditambahkan.');
    }

    public function show(PenghasilanOrangTua $penghasilanOrtu)
    {
        $title = 'Edit Penghasilan Orang Tua';
        $action_url = route(' admin.penghasilan-orang-tua.update', $penghasilanOrtu->id);
        $method = 'PUT';  // Method PUT for update
        return view('admin_dashboard.pages.penghasilanOrtu.form', compact('action_url', 'method', 'penghasilanOrtu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_peng' => 'required',
        ]);

        $penghasilanOrtu = PenghasilanOrangTua::findOrFail($id);
        $penghasilanOrtu->update($request->all());

        return redirect()->route('admin.penghasilan-orang-tua.index')
            ->with('toastSuccess', 'Penghasilan Orang Tua berhasil diupdate.');
    }

    public function destroy($id)
    {
        $penghasilanOrtu = PenghasilanOrangTua::findOrFail($id);
        $penghasilanOrtu->delete();

        return redirect()->route('admin.penghasilan-orang-tua.index')
            ->with('toastSuccess', 'Penghasilan Orang Tua berhasil dihapus.');
    }
}
