<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\Agama;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgamaController extends Controller
{
    public function index()
    {
        $agamas = Agama::all();
        return view('admin_dashboard.pages.agama.index', compact('agamas'));
    }

    public function create()
    {
        $action_url = route('admin.agama.store');  // URL for storing new Agama
        $method = 'POST'; // Method POST for addition
        $agama = null;  // No data for Agama when creating
        return view('admin_dashboard.pages.agama.form', compact('action_url', 'method', 'agama'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_agama' => 'required',
        ]);

        Agama::create($request->all());
        return redirect()->route('admin.agama.index')
            ->with('success', 'Agama berhasil ditambahkan.');
    }

    public function show(Agama $agama)
    {
        $title = 'Edit Agama';
        $action_url = route('admin.agama.update', $agama->id);
        $method = 'PUT';  // Method PUT for update
        return view('admin_dashboard.pages.agama.form', compact('action_url', 'method', 'agama'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_agama' => 'required',
        ]);

        $agama = Agama::findOrFail($id);
        $agama->update($request->all());

        return redirect()->route('admin.agama.index')
            ->with('toastSuccess', 'Agama berhasil diupdate.');
    }

    public function destroy($id)
    {
        $agama = Agama::findOrFail($id);
        $agama->delete();

        return redirect()->route('agama.index')
            ->with('toastSuccess', 'Agama berhasil dihapus.');
    }
}
