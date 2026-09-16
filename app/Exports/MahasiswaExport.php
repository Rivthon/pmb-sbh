<?php

namespace App\Exports;

use App\Models\User;
use App\Enums\PmbStatus;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MahasiswaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents, WithCustomStartCell
{
    protected $query;
    protected $filters;
    private $rowNumber = 1;

    public function __construct($query, array $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function query()
    {
        return $this->query;
    }

    public function title(): string
    {
        return 'Data PMB';
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Alamat Email',
            'No. HP (WhatsApp)',
            'NIK',
            'NISN',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Alamat Lengkap',
            'Asal Sekolah',
            'Program Studi',
            'Periode Akademik',
            'Gelombang PMB',
            'Status PMB',
            'Status Verifikasi',
            'Status Pembayaran',
            'Tanggal Daftar'
        ];
    }

    public function map($mahasiswa): array
    {
        $gender = match ($mahasiswa->jenis_kelamin) {
            'L' => 'Laki-Laki',
            'P' => 'Perempuan',
            default => '-'
        };

        $statusVerifikasi = match ($mahasiswa->status_berkas) {
            1 => 'Terverifikasi',
            2 => 'Ditolak',
            default => 'Belum Verifikasi'
        };

        $statusPembayaran = ($mahasiswa->status_pemb == 3 || $mahasiswa->status_pemb == 2) ? 'Sudah Bayar' : 'Belum Bayar';

        $fullAddress = sprintf(
            '%s, Kel. %s, Kec. %s, %s, Prov. %s',
            $mahasiswa->address ?? '-',
            $mahasiswa->kelurahan->nama_kel ?? '-',
            $mahasiswa->kecamatan->nama_kec ?? '-',
            $mahasiswa->kabupaten->nama_kab ?? '-',
            $mahasiswa->provinsi->nama ?? '-'
        );

        return [
            $this->rowNumber++,
            $mahasiswa->name,
            $mahasiswa->email,
            $mahasiswa->phone ? "'" . $mahasiswa->phone : '-', // prepend single quote to prevent leading zero strip
            $mahasiswa->nik ? "'" . $mahasiswa->nik : '-',
            $mahasiswa->nisn ? "'" . $mahasiswa->nisn : '-',
            $gender,
            $mahasiswa->tempat_lahir ?? '-',
            $mahasiswa->tgl_lahir ? \Carbon\Carbon::parse($mahasiswa->tgl_lahir)->format('d-m-Y') : '-',
            $mahasiswa->agama->nama_agama ?? '-',
            $fullAddress,
            $mahasiswa->asal_sekolah ?? '-',
            $mahasiswa->jurusan->nama_jurusan ?? '-',
            $mahasiswa->periode->deskripsi ?? '-',
            $mahasiswa->gelombang->nama_gelombang ?? '-',
            PmbStatus::fromValue($mahasiswa->status_pemb)->label(),
            $statusVerifikasi,
            $statusPembayaran,
            $mahasiswa->created_at ? $mahasiswa->created_at->format('d-m-Y H:i') : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style table header row (row 8)
            8 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'] // premium indigo brand color
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Write Kop / Title
                $sheet->setCellValue('A1', 'LAPORAN DATA MAHASISWA PMB');
                $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));
                $sheet->mergeCells('A1:G1');
                
                // Filter Info
                $sheet->setCellValue('A3', 'Periode');
                $sheet->setCellValue('B3', ': ' . ($this->filters['periode_name'] ?? 'Semua'));
                
                $sheet->setCellValue('A4', 'Gelombang');
                $sheet->setCellValue('B4', ': ' . ($this->filters['gelombang_name'] ?? 'Semua'));
                
                $sheet->setCellValue('A5', 'Status PMB');
                $sheet->setCellValue('B5', ': ' . ($this->filters['status_label'] ?? 'Semua'));
                
                $sheet->setCellValue('E3', 'Tanggal Cetak');
                $sheet->setCellValue('F3', ': ' . ($this->filters['date'] ?? now()->format('d F Y H:i')));
                
                $sheet->setCellValue('E4', 'Dicetak Oleh');
                $sheet->setCellValue('F4', ': ' . ($this->filters['admin_name'] ?? 'Admin'));

                // Bold Filter Titles
                $sheet->getStyle('A3:A5')->getFont()->setBold(true);
                $sheet->getStyle('E3:E4')->getFont()->setBold(true);
                
                // Styling rows and columns bounds
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Table header starts at row 8, data starts at A9
                $sheet->setAutoFilter("A8:{$highestColumn}{$highestRow}");
                $sheet->freezePane('A9'); // Freeze table header (row 8)
                
                // Set borders for data range
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E2E8F0'], // Soft border gray
                        ],
                    ],
                ];
                $sheet->getStyle("A8:{$highestColumn}{$highestRow}")->applyFromArray($borderStyle);
                
                // Alignments
                $sheet->getStyle("A8:{$highestColumn}8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A9:A{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D9:F{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G9:I{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("N9:O{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("P9:R{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("S9:S{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // Total Count Summary at bottom
                $totalRow = $highestRow + 2;
                $sheet->setCellValue("A{$totalRow}", "TOTAL CALON MAHASISWA: " . ($highestRow - 8));
                $sheet->getStyle("A{$totalRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));
                $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
            }
        ];
    }
}
