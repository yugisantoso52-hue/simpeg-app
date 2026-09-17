<?php

namespace App\Exports;

use App\Models\Pegawai;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceMonthlyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected int $month;
    protected int $year;
    protected ?string $search;
    protected int $rowNumber = 0;
    protected int $daysInMonth;
    protected array $days = [];

    public function __construct(int $month, int $year, ?string $search = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->search = $search;

        $startOfMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth();
        $this->daysInMonth = $startOfMonth->daysInMonth;
        $now = Carbon::now('Asia/Jakarta');

        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $date = Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta');
            $this->days[$d] = [
                'is_weekend' => $date->isWeekend(),
                'is_future' => $date->isAfter($now->endOfDay()),
            ];
        }
    }

    public function collection()
    {
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1, 'Asia/Jakarta')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $query = Pegawai::with([
            'unitKerja',
            'jabatan',
            'user.attendances' => function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('attendance_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            }
        ])->where('status_pegawai', 'Aktif');

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama', 'asc')->get();
    }

    public function headings(): array
    {
        $headings = [
            'NO',
            'NAMA PEGAWAI',
            'NIP',
            'JABATAN',
            'UNIT KERJA',
        ];

        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $headings[] = (string) $d;
        }

        $headings[] = 'TOTAL HADIR';
        $headings[] = 'TERLAMBAT';
        $headings[] = 'TOTAL JAM KERJA';

        return $headings;
    }

    public function map($pegawai): array
    {
        $this->rowNumber++;

        $attendancesByDay = [];
        if ($pegawai->user && $pegawai->user->attendances) {
            foreach ($pegawai->user->attendances as $att) {
                $dayNum = (int) Carbon::parse($att->attendance_date)->format('j');
                $attendancesByDay[$dayNum] = $att;
            }
        }

        $totalHadir = 0;
        $totalLate = 0;
        $totalSeconds = 0;

        $row = [
            $this->rowNumber,
            $pegawai->nama,
            $pegawai->nip ?? '-',
            $pegawai->jabatan?->nama_jabatan ?? '-',
            $pegawai->unitKerja?->nama_unit ?? '-',
        ];

        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $dayInfo = $this->days[$d];
            $att = $attendancesByDay[$d] ?? null;

            if ($att) {
                $totalHadir++;
                if ($att->status === 'late') {
                    $totalLate++;
                    $row[] = 'T';
                } elseif ($att->status === 'leave') {
                    $row[] = 'I';
                } else {
                    $row[] = 'H';
                }

                if ($att->check_in_time && $att->check_out_time) {
                    $totalSeconds += $att->check_in_time->diffInSeconds($att->check_out_time);
                }
            } else {
                if ($dayInfo['is_weekend']) {
                    $row[] = '—';
                } elseif ($dayInfo['is_future']) {
                    $row[] = '·';
                } else {
                    $row[] = 'A';
                }
            }
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $formattedDuration = $hours > 0 ? "{$hours} Jam {$minutes} Menit" : ($minutes > 0 ? "{$minutes} Menit" : "-");

        $row[] = $totalHadir . ' Hari';
        $row[] = $totalLate . 'x';
        $row[] = $formattedDuration;

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 10,
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

        $sheet->getRowDimension(1)->setRowHeight(26);

        if ($highestRow > 1) {
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F2:{$highestColumn}{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        return [];
    }
}
