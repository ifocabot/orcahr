<?php

namespace App\Exports;

use App\Models\AttendanceSummary;
use App\Models\Employee;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecapExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private int $month,
        private int $year,
        private ?int $departmentId = null
    ) {
    }

    public function title(): string
    {
        return "Rekap {$this->year}-{$this->month}";
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Departemen', 'Hadir', 'Terlambat', 'Absent', 'Cuti', 'Libur', 'Mnt Telat', 'Mnt OT', '% Hadir'];
    }

    public function collection()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->toDateString();

        $employees = Employee::active()
            ->with('department')
            ->when($this->departmentId, fn($q, $v) => $q->where('department_id', $v))
            ->orderBy('full_name')
            ->get();

        return $employees->map(function (Employee $emp) use ($startDate, $endDate) {
            $s = AttendanceSummary::where('employee_id', $emp->id)
                ->whereBetween('work_date', [$startDate, $endDate])
                ->get();
            $total = $s->count();
            $pct = $total > 0 ? round((($s->where('status', 'present')->count() + $s->where('status', 'late')->count()) / $total) * 100, 1) : 0;

            return [
                $emp->employee_code,
                $emp->full_name,
                $emp->department?->name ?? '—',
                $s->where('status', 'present')->count(),
                $s->where('status', 'late')->count(),
                $s->where('status', 'absent')->count(),
                $s->where('status', 'leave')->count(),
                $s->where('status', 'holiday')->count(),
                $s->sum('late_minutes'),
                $s->sum('overtime_minutes'),
                "{$pct}%",
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]],
        ];
    }
}
