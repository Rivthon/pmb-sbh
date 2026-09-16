<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\Periode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderBy('tgl_mulai', 'desc')->get();
        return view('admin_dashboard.pages.periode.index', compact('periodes'));
    }

    public function create()
    {
        $action_url = route('admin.periode.store');
        $method = 'POST';
        $periode = null;
        return view('admin_dashboard.pages.periode.form', compact('action_url', 'method', 'periode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_mulai' => 'required|date',
            'deskripsi' => 'nullable|string|max:255',
            'status_periode' => 'required|in:aktif,nonaktif',
            'tanggal_tes' => 'nullable|date',
            'linked' => 'nullable|string|max:255',
        ]);

        Periode::create($validated);
        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil ditambahkan.');
    }

    public function edit(Periode $periode)
    {
        return $this->show($periode);
    }

    public function show(Periode $periode)
    {
        $action_url = route('admin.periode.update', $periode->id);
        $method = 'PUT';
        return view('admin_dashboard.pages.periode.form', compact('action_url', 'method', 'periode'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tgl_mulai' => 'required|date',
            'deskripsi' => 'nullable|string|max:255',
            'status_periode' => 'required|in:aktif,nonaktif',
            'tanggal_tes' => 'nullable|date',
            'linked' => 'nullable|string|max:255',
        ]);

        $periode = Periode::findOrFail($id);
        $periode->update($validated);

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil diupdate.');
    }

    public function aktifkan($id)
    {
        // Find the periode to be activated
        $periode = Periode::findOrFail($id);

        // Deactivate any other active periode
        Periode::where('id', '!=', $id)
            ->where('status_periode', 'aktif')
            ->update(['status_periode' => 'nonaktif']);

        // Activate the selected periode
        $periode->update(['status_periode' => 'aktif']);

        return redirect()->route('admin.periode.index')
            ->with('toastSuccess', 'Periode berhasil diaktifkan.');
    }


    public function nonaktifkan($id)
    {
        $periode = Periode::findOrFail($id);
        $periode->update(['status_periode' => 'nonaktif']);

        return redirect()->route('admin.periode.index')
            ->with('toastSuccess', 'Periode berhasil dinonaktifkan.');
    }

    public function destroy($id)
    {
        $periode = Periode::findOrFail($id);
        $periode->delete();

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode berhasil dihapus.');
    }
}
