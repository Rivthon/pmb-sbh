<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\Gelombang;
use App\Models\Periode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GelombangController extends Controller
{
    public function index(Request $request)
    {
        \Carbon\Carbon::setLocale('id');

        $periodes = Periode::orderBy('tgl_mulai', 'desc')->get();

        $query = Gelombang::with('periode');

        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        $gelombangs = $query->orderBy('tgl_mulai', 'desc')->get();
        
        return view('admin_dashboard.pages.gelombang.index', compact('gelombangs', 'periodes'));
    }

    public function create()
    {
        $action_url = route('admin.gelombang.store');
        $method = 'POST';
        $gelombang = null;
        $periodes = Periode::orderBy('tgl_mulai', 'desc')->get();
        
        return view('admin_dashboard.pages.gelombang.form', compact('action_url', 'method', 'gelombang', 'periodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'nama_gelombang' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gelombangs')->where('periode_id', $request->periode_id)
            ],
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'status_gelombang' => 'required|in:aktif,nonaktif',
        ], [
            'nama_gelombang.unique' => 'Nama Gelombang sudah ada dalam Periode yang dipilih.'
        ]);

        Gelombang::create($validated);
        
        return redirect()->route('admin.gelombang.index')
            ->with('success', 'Gelombang berhasil ditambahkan.');
    }

    public function edit(Gelombang $gelombang)
    {
        $action_url = route('admin.gelombang.update', $gelombang->id);
        $method = 'PUT';
        $periodes = Periode::orderBy('tgl_mulai', 'desc')->get();
        
        return view('admin_dashboard.pages.gelombang.form', compact('action_url', 'method', 'gelombang', 'periodes'));
    }

    public function show(Gelombang $gelombang)
    {
        return $this->edit($gelombang);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'nama_gelombang' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gelombangs')->where('periode_id', $request->periode_id)->ignore($id)
            ],
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'status_gelombang' => 'required|in:aktif,nonaktif',
        ], [
            'nama_gelombang.unique' => 'Nama Gelombang sudah ada dalam Periode yang dipilih.'
        ]);

        $gelombang = Gelombang::findOrFail($id);
        $gelombang->update($validated);

        return redirect()->route('admin.gelombang.index')
            ->with('success', 'Gelombang berhasil diupdate.');
    }

    public function aktifkan($id)
    {
        $gelombang = Gelombang::findOrFail($id);

        // Scoped Deactivation: Deactivate other gelombangs only in the SAME period
        Gelombang::where('periode_id', $gelombang->periode_id)
            ->where('id', '!=', $id)
            ->where('status_gelombang', 'aktif')
            ->update(['status_gelombang' => 'nonaktif']);

        // Activate the selected gelombang
        $gelombang->update(['status_gelombang' => 'aktif']);

        return redirect()->route('admin.gelombang.index')
            ->with('toastSuccess', 'Gelombang berhasil diaktifkan.');
    }

    public function nonaktifkan($id)
    {
        $gelombang = Gelombang::findOrFail($id);
        $gelombang->update(['status_gelombang' => 'nonaktif']);

        return redirect()->route('admin.gelombang.index')
            ->with('toastSuccess', 'Gelombang berhasil dinonaktifkan.');
    }

    public function destroy($id)
    {
        $gelombang = Gelombang::findOrFail($id);
        $gelombang->delete();

        return redirect()->route('admin.gelombang.index')
            ->with('success', 'Gelombang berhasil dihapus.');
    }

    /**
     * AJAX endpoint to fetch gelombang by period.
     */
    public function getGelombangByPeriode($periodeId)
    {
        $gelombangs = Gelombang::where('periode_id', $periodeId)
            ->orderBy('tgl_mulai', 'asc')
            ->get()
            ->map(function ($gel) {
                return [
                    'id' => $gel->id,
                    'nama_gelombang' => $gel->nama_gelombang,
                    'tanggal_mulai' => $gel->tgl_mulai,
                    'tanggal_selesai' => $gel->tgl_selesai,
                    'status' => $gel->status_gelombang == 'aktif' ? 1 : 0
                ];
            });

        return response()->json($gelombangs);
    }
}
