<?php

namespace App\Http\Controllers\Dashboard\Report;

use App\Models\SaleItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class ReportSaleController extends Controller
{
    public function saleReport()
    {
        $data = SaleItem::with(['sale', 'item'])
            ->whereDateRange('created_at', request()->query('start_date'), request()->query('end_date'))
            ->filterChainingByUser('sale')->orderBy('created_at', 'DESC')->get();

        return view('dashboard.pages.report.sale-report', compact('data'));
    }

    public function filter()
    {
        $action_url = route('dashboard.report.sale.index');
        return view('dashboard.components.filter-date-range', compact('action_url'));
    }

    public function exportToPDF()
    {
        $currentDate = now()->format('dmY');
        $start_date = request()->query('start_date');
        $end_date = request()->query('end_date');

        $data = SaleItem::with(['sale', 'item'])
            ->whereDateRange('created_at', $start_date, $end_date)
            ->filterChainingByUser('sale')->orderBy('created_at', 'DESC')->get();
        $pdf = Pdf::loadView('templates.pdf.contents.sale-report', compact('data', 'start_date', 'end_date'));

        return $pdf->download("{$currentDate}-stockflow-laporan-transaksi-penjualan.pdf");
    }
}
