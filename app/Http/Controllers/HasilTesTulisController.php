<?php

namespace App\Http\Controllers;

use PDF; // tambahkan di atas
use App\Models\HasilTesTulis;
use App\Http\Requests\StoreHasilTesTulisRequest;
use App\Http\Requests\UpdateHasilTesTulisRequest;
use App\Support\AdminPermissions;
use App\Support\PmbOnlineSelectionAccess;

class HasilTesTulisController extends Controller
{
    public function index()
    {
        $query = HasilTesTulis::with('user')->latest();

        if ($this->isOnlineResultsOnly()) {
            PmbOnlineSelectionAccess::constrainToLatestOnlineUsers($query);
        }

        $hasilTes = $query->get();

        return view('admin.hasil_tes.index', compact('hasilTes'));
    }

    public function show($id)
    {
        $hasil = HasilTesTulis::with([
            'user:id,name,email',
            'kategoriHasil.kategori:id,nama_kategori',
            'jawaban' => function ($q) {
                $q->select('id', 'hasil_tes_id', 'soal_id', 'pilihan_jawaban_id', 'benar');
            },
            'jawaban.soal' => function ($q) {
                $q->select('id', 'pertanyaan', 'kategori_id');
            },
            'jawaban.soal.kategori:id,nama_kategori',
            'jawaban.soal.pilihanJawaban:id,soal_tes_id,teks_pilihan,benar',
            'jawaban.pilihanJawaban:id,teks_pilihan'
        ])->findOrFail($id);

        if ($this->isOnlineResultsOnly()) {
            PmbOnlineSelectionAccess::abortUnlessLatestOnlineUser($hasil->user_id);
        }

        // Urutkan biar grouping rapi
        $hasil->jawaban = $hasil->jawaban->sortBy('soal.kategori.nama_kategori')->values();

        // dd([
        //     'Total Jawaban' => $hasil->jawaban->count(),
        //     'Contoh Jawaban Pertama' => optional($hasil->jawaban->first())->toArray(),
        //     'Contoh Soal' => optional(optional($hasil->jawaban->first())->soal)->toArray(),
        //     'Kategori Soal' => optional(optional(optional($hasil->jawaban->first())->soal)->kategori)->toArray(),
        //     'Pilihan Jawaban Soal' => optional(optional($hasil->jawaban->first())->soal)->pilihanJawaban?->toArray(),
        // ]);

        return view('admin.hasil_tes.show', compact('hasil'));
    }





    public function cetakPdf($id)
    {
        $hasil = HasilTesTulis::with(['user', 'kategoriHasil.kategori'])
            ->findOrFail($id);

        if ($this->isOnlineResultsOnly()) {
            PmbOnlineSelectionAccess::abortUnlessLatestOnlineUser($hasil->user_id);
        }

        $pdf = PDF::loadView('admin.hasil_tes.pdf', compact('hasil'))
            ->setPaper('a4', 'portrait');

        $filename = 'Hasil_Tes_' . ($hasil->user->name ?? 'Peserta') . '.pdf';
        return $pdf->download($filename);
    }

    private function isOnlineResultsOnly(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_ONLINE_HASIL_TES_VIEW)
            && !$admin?->can(AdminPermissions::REPORT_VIEW)
            && !$admin?->can(AdminPermissions::REPORT_EXPORT));
    }
}
