<?php

namespace App\Exports\Transaction;

use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomingGoodsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    public function __construct
    (
        protected $data
    ) {}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }

    public function map($item): array
    {
        return [
            $item->code,
            $item->supplier?->name,
            $item->items->map(fn ($incomingGoodsItem) => $incomingGoodsItem->item->name)->implode("\n"),
            $item->items->map(fn ($incomingGoodsItem) => "{$incomingGoodsItem->initial_stock} {$incomingGoodsItem->item->unit}")->implode("\n"),
            $item->items->map(fn ($incomingGoodsItem) => "{$incomingGoodsItem->total_stock} {$incomingGoodsItem->item->unit}")->implode("\n"),
            $item->items->map(fn ($incomingGoodsItem) => "{$incomingGoodsItem->current_stock} {$incomingGoodsItem->item->unit}")->implode("\n"),
            $item->notes,
            $item->incoming_date?->format('d-m-Y')
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Barang Masuk',
            'Nama Supplier',
            'Barang',
            'Stok Awal',
            'Jumlah Stok',
            'Stok Akhir',
            'Catatan',
            'Tanggal Barang Masuk'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => '30',
            'B' => '30',
            'C' => '30',
            'D' => '15',
            'E' => '15',
            'F' => '15',
            'G' => '30',
            'H' => '30'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1     => ['font' => ['bold' => true]],
            'A'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'B'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'C'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'D'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'E'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'F'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'G'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'H'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'I'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'J'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'K'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'L'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'M'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'N'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'O'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'P'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'Q'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'R'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'S'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
            'T'   => ['alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,]],
        ];
    }
}
