<?php

namespace App\Exports;

use App\Models\AttendanceSummary;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimesheetExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $month,
        private readonly ?string $departmentId = null,
    ) {
    }

    public function query()
    {
        [$y, $m] = explode('-', $this->month);

        return AttendanceSummary::with(['employee.department', 'shift'])
            ->whereYear('work_date', $y)
            ->whereMonth('work_date', $m)
            ->when(
                $this->departmentId,
                fn($q) =>
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $this->departmentId))
            )
            ->orderBy('work_date')
            ->orderBy('employee_id');
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Karyawan',
            'Departemen',
            'Shift',
            'Tanggal',
            'Clock In',
            'Clock Out',
            'Durasi (mnt)',
            'Terlambat (mnt)',
            'Lembur (mnt)',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee->employee_code,
            $row->employee->full_name,
            $row->employee->department?->name ?? '-',
            $row->shift?->name ?? '-',
            $row->work_date->format('Y-m-d'),
            $row->actual_in?->format('H:i') ?? '-',
            $row->actual_out?->format('H:i') ?? '-',
            $row->work_duration_minutes,
            $row->late_minutes,
            $row->overtime_minutes,
            $row->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
