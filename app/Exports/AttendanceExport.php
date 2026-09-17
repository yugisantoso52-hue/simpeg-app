<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Attendance::with(['user.pegawai.unitKerja', 'user.pegawai.jabatan'])
            ->latest('attendance_date')
            ->latest('check_in_time');

        if (!empty($this->filters['date'])) {
            $query->whereDate('attendance_date', $this->filters['date']);
        }

        if (!empty($this->filters['date_start']) && !empty($this->filters['date_end'])) {
            $query->whereBetween('attendance_date', [$this->filters['date_start'], $this->filters['date_end']]);
        }

        if (!empty($this->filters['attendance_type'])) {
            $query->where('attendance_type', $this->filters['attendance_type']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhereHas('pegawai', function ($qp) use ($search) {
                       $qp->where('nama', 'like', "%{$search}%")
                           ->orWhere('nip', 'like', "%{$search}%");
                   });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEGAWAI',
            'NIP / EMAIL',
            'JABATAN',
            'UNIT KERJA',
            'TANGGAL',
            'TIPE PRESENSI',
            'JAM MASUK',
            'JAM PULANG',
            'TOTAL JAM KERJA (DURASI)',
            'JARAK GPS',
            'STATUS KEHADIRAN',
            'CATATAN',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item->user->name ?? '-',
            $item->user->pegawai?->nip ?? $item->user->email,
            $item->user->pegawai?->jabatan?->nama_jabatan ?? '-',
            $item->user->pegawai?->unitKerja?->nama_unit ?? '-',
            $item->attendance_date ? $item->attendance_date->format('d/m/Y') : '-',
            strtoupper($item->attendance_type),
            $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i:s') : '-',
            $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i:s') : 'Belum Check-Out',
            $item->work_duration,
            number_format($item->check_in_distance_meters, 1) . ' m',
            $item->status_badge['label'] ?? ucfirst($item->status),
            $item->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style Header baris 1
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'], // UNRI Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Center alignments untuk kolom tertentu
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:L{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("A1:M{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        return [];
    }
}
