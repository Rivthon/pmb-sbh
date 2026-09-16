<?php

namespace App\Http\Controllers\Dashboard\Report;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel;

class StockController extends Controller
{
    public function stockReport()
    {
        $data = Item::with('category')->filterByUser()->orderBy('stock', 'ASC')->get();
        return view('dashboard.pages.report.stock-report', compact('data'));
    }

    public function exportToPDF()
    {
        $currentDate = now()->format('dmY');
        $data = Item::with('category')->filterByUser()->orderBy('stock', 'ASC')->get();
        $pdf = Pdf::loadView('templates.pdf.contents.stock-report', compact('data'));

        return $pdf->download("{$currentDate}-stockflow-laporan-stok-barang.pdf");
    }
}
