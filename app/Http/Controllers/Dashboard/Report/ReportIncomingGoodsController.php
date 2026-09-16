<?php

namespace App\Http\Controllers\Dashboard\Report;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\IncomingGoodsItem;
use App\Http\Controllers\Controller;

class ReportIncomingGoodsController extends Controller
{
    public function incomingReport()
    {
        $data = IncomingGoodsItem::with(['incomingGoods', 'item'])
            ->whereDateRange('created_at', request()->query('start_date'), request()->query('end_date'))
            ->filterChainingByUser('incomingGoods')->orderBy('created_at', 'DESC')->get();

        return view('dashboard.pages.report.incoming-report', compact('data'));
    }

    public function filter()
    {
        $action_url = route('dashboard.report.incoming.index');
        return view('dashboard.components.filter-date-range', compact('action_url'));
    }

    public function exportToPDF()
    {
        $currentDate = now()->format('dmY');
        $start_date = request()->query('start_date');
        $end_date = request()->query('end_date');

        $data = IncomingGoodsItem::with(['incomingGoods', 'item'])
            ->whereDateRange('created_at', $start_date, $end_date)
            ->filterChainingByUser('incomingGoods')->orderBy('created_at', 'DESC')->get();
        
        $pdf = Pdf::loadView('templates.pdf.contents.incoming-report', compact('data', 'start_date', 'end_date'));

        return $pdf->download("{$currentDate}-stockflow-laporan-barang-masuk.pdf");
    }
}
