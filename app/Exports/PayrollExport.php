<?php

namespace App\Exports;

use App\Models\PayrollRun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private readonly PayrollRun $run)
    {
    }

    public function title(): string
    {
        return 'Payroll ' . $this->run->period_label;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Karyawan',
            'Nama Karyawan',
            'Total Pendapatan',
            'Total Potongan',
            'Gaji Bersih',
        ];
    }

    public function collection()
    {
        $details = $this->run->details()
            ->with(['employee', 'component'])
            ->get()
            ->groupBy('employee_id');

        $rows = [];
        $i = 1;

        foreach ($details as $employeeId => $items) {
            $employee = $items->first()->employee;
            $gross = $items->where('type', 'earning')->sum('amount');
            $deductions = $items->where('type', 'deduction')->sum('amount');
            $net = $gross - $deductions;

            $rows[] = [
                $i++,
                $employee->employee_code,
                $employee->full_name,
                $gross,
                $deductions,
                $net,
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
