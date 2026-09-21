<?php

namespace App\Exports;

use App\Models\Logbook;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LogbookExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ?int $pegawaiId;
    protected ?int $month;
    protected ?int $year;
    protected ?int $unitKerjaId;
    protected ?string $status;
    protected ?array $bawahanIds;
    protected int $rowNumber = 0;

    public function __construct(?int $pegawaiId = null, ?int $month = null, ?int $year = null, ?int $unitKerjaId = null, ?string $status = null, ?array $bawahanIds = null)
    {
        $this->pegawaiId = $pegawaiId;
        $this->month = $month ?: Carbon::now()->month;
        $this->year = $year ?: Carbon::now()->year;
        $this->unitKerjaId = $unitKerjaId;
        $this->status = $status;
        $this->bawahanIds = $bawahanIds;
    }

    public function collection()
    {
        $query = Logbook::with(['pegawai.unitKerja', 'pegawai.jabatan', 'verifikator'])
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month);

        if ($this->pegawaiId) {
            $query->where('pegawai_id', $this->pegawaiId);
        }

        if ($this->bawahanIds !== null) {
            $query->whereIn('pegawai_id', $this->bawahanIds);
        }

        if ($this->unitKerjaId) {
            $query->whereHas('pegawai', function ($q) {
                $q->where('unit_kerja_id', $this->unitKerjaId);
            });
        }

        if ($this->status && $this->status !== 'semua') {
            $query->where('status', $this->status);
        }

        return $query->orderBy('tanggal', 'asc')->orderBy('jam_mulai', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NIP',
            'NAMA PEGAWAI',
            'UNIT KERJA',
            'TANGGAL',
            'JAM MULAI',
            'JAM SELESAI',
            'DURASI (MENIT)',
            'KATEGORI KEGIATAN',
            'RINGKASAN AKTIVITAS',
            'RINCIAN / DESKRIPSI',
            'OUTPUT',
            'STATUS',
            'CATATAN ATASAN',
            'VERIFIKATOR',
        ];
    }

    public function map($logbook): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $logbook->pegawai?->nip ?? '-',
            $logbook->pegawai?->nama ?? '-',
            $logbook->pegawai?->unitKerja?->nama_unit ?? '-',
            Carbon::parse($logbook->tanggal)->format('d/m/Y'),
            substr($logbook->jam_mulai, 0, 5),
            substr($logbook->jam_selesai, 0, 5),
            $logbook->durasi_menit,
            $logbook->kategori_kegiatan,
            $logbook->aktivitas,
            $logbook->deskripsi_kegiatan,
            $logbook->jumlah_output . ' ' . $logbook->satuan_output . ($logbook->output_kegiatan ? ' (' . $logbook->output_kegiatan . ')' : ''),
            $logbook->status_label,
            $logbook->catatan_atasan ?? '-',
            $logbook->verifikator?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rowNumber + 1;

        // Header style
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E40AF'], // Tailwind blue-800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Borders
        $sheet->getStyle('A1:O' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);

        // Alignment for numbers & dates
        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M2:M' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
