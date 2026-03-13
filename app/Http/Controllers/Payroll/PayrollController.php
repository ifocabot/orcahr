<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Exports\PayrollExport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    /** Payroll calculate form + list of past runs */
    public function index(): Response
    {
        return Inertia::render('Payroll/Calculate/Index', [
            'runs' => PayrollRun::with(['calculatedBy', 'approvedBy'])
                ->orderByDesc('period_year')
                ->orderByDesc('period_month')
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'period_label' => $r->period_label,
                    'status' => $r->status,
                    'total_gross' => $r->total_gross,
                    'total_net' => $r->total_net,
                    'calculated_at' => $r->calculated_at?->toDateTimeString(),
                ]),
        ]);
    }

    /** Execute payroll calculation for a given month/year */
    public function calculate(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        // Create or reset payroll run for this period
        $run = PayrollRun::firstOrCreate(
            ['period_month' => $month, 'period_year' => $year],
            ['status' => 'draft']
        );

        if (!in_array($run->status, ['draft', 'calculated'])) {
            return back()->withErrors(['calculate' => 'Payroll periode ini sudah diapprove atau dibayar.']);
        }

        // Clear existing details for recalculation
        $run->details()->delete();

        $periodStart = Carbon::create($year, $month, 1);
        $periodEnd = $periodStart->copy()->endOfMonth();
        $workingDays = $this->countWorkingDays($periodStart, $periodEnd);

        $totalGross = 0;
        $totalDeductions = 0;

        $employees = Employee::active()
            ->with(['payrollConfigs' => fn($q) => $q->active()->with('component')])
            ->get();

        foreach ($employees as $employee) {
            $gapok = 0;
            $details = [];

            // --- Fixed earning/deduction components ---
            foreach ($employee->payrollConfigs as $config) {
                if (!$config->component->is_active)
                    continue;
                if (!$config->component->is_fixed)
                    continue;

                $details[] = [
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'component_id' => $config->component_id,
                    'type' => $config->component->type,
                    'amount' => $config->amount,
                    'notes' => null,
                ];

                if ($config->component->code === 'GAPOK') {
                    $gapok = (float) $config->amount;
                }

                if ($config->component->type === 'earning') {
                    $totalGross += (float) $config->amount;
                } elseif ($config->component->type === 'deduction') {
                    $totalDeductions += (float) $config->amount;
                }
            }

            // --- Attendance-based adjustments ---
            $summaries = AttendanceSummary::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->get();

            $overtimeMinutes = $summaries->sum('overtime_minutes');
            $absentDays = $summaries->where('status', 'absent')->count();

            // Overtime pay
            if ($overtimeMinutes > 0 && $gapok > 0) {
                $otRate = $gapok / 173 / 60 * 1.5;
                $otAmount = round($overtimeMinutes * $otRate, 2);

                $otComponent = \App\Models\PayrollComponent::where('code', 'OT')->first();
                if ($otComponent) {
                    $details[] = [
                        'payroll_run_id' => $run->id,
                        'employee_id' => $employee->id,
                        'component_id' => $otComponent->id,
                        'type' => 'earning',
                        'amount' => $otAmount,
                        'notes' => "{$overtimeMinutes} menit OT",
                    ];
                    $totalGross += $otAmount;
                }
            }

            // BPJS Kesehatan (1% gapok, capped 120.000)
            if ($gapok > 0) {
                $bpjsKes = min($gapok * 0.01, 120000);
                $bpjsComp = \App\Models\PayrollComponent::where('code', 'BPJS-KES')->first();
                if ($bpjsComp) {
                    $details[] = [
                        'payroll_run_id' => $run->id,
                        'employee_id' => $employee->id,
                        'component_id' => $bpjsComp->id,
                        'type' => 'deduction',
                        'amount' => $bpjsKes,
                        'notes' => null,
                    ];
                    $totalDeductions += $bpjsKes;
                }

                // BPJS Ketenagakerjaan (2% gapok)
                $bpjsTk = $gapok * 0.02;
                $bpjsTkComp = \App\Models\PayrollComponent::where('code', 'BPJS-TK')->first();
                if ($bpjsTkComp) {
                    $details[] = [
                        'payroll_run_id' => $run->id,
                        'employee_id' => $employee->id,
                        'component_id' => $bpjsTkComp->id,
                        'type' => 'deduction',
                        'amount' => $bpjsTk,
                        'notes' => null,
                    ];
                    $totalDeductions += $bpjsTk;
                }
            }

            // Absent deduction (uses GAPOK component with deduction type — clearly noted)
            if ($absentDays > 0 && $gapok > 0 && $workingDays > 0) {
                $absentDeduction = round($absentDays * ($gapok / $workingDays), 2);
                // Find or fallback to any deduction component for absent
                $gapokComponent = \App\Models\PayrollComponent::where('code', 'GAPOK')->first();
                if ($gapokComponent) {
                    $details[] = [
                        'payroll_run_id' => $run->id,
                        'employee_id' => $employee->id,
                        'component_id' => $gapokComponent->id,
                        'type' => 'deduction',
                        'amount' => $absentDeduction,
                        'notes' => "Potongan tidak hadir {$absentDays} hari",
                    ];
                    $totalDeductions += $absentDeduction;
                }
            }

            if (!empty($details)) {
                PayrollDetail::insert($details);
            }
        }

        $run->update([
            'status' => 'calculated',
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalGross - $totalDeductions,
            'calculated_by' => auth()->id(),
            'calculated_at' => now(),
        ]);

        return redirect()->route('payroll.index')
            ->with('success', "Payroll {$run->period_label} berhasil dihitung.");
    }

    /** Approve payroll run (calculated → approved) */
    public function approve(PayrollRun $payrollRun): RedirectResponse
    {
        if ($payrollRun->status !== 'calculated') {
            return back()->withErrors(['approve' => 'Payroll harus berstatus calculated untuk di-approve.']);
        }

        $payrollRun->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Payroll {$payrollRun->period_label} diapprove.");
    }

    /** Mark payroll as paid (approved → paid) */
    public function markPaid(PayrollRun $payrollRun): RedirectResponse
    {
        if ($payrollRun->status !== 'approved') {
            return back()->withErrors(['paid' => 'Payroll harus berstatus approved untuk ditandai lunas.']);
        }

        $payrollRun->update(['status' => 'paid']);

        return back()->with('success', "Payroll {$payrollRun->period_label} ditandai lunas.");
    }

    /** Payroll report: all employees in one run */
    public function report(PayrollRun $payrollRun): Response
    {
        $details = $payrollRun->details()
            ->with(['employee', 'component'])
            ->get()
            ->groupBy('employee_id');

        $employees = $details->map(function ($rows) {
            $employee = $rows->first()->employee;
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'gross' => $rows->where('type', 'earning')->sum('amount'),
                'deductions' => $rows->where('type', 'deduction')->sum('amount'),
                'net' => $rows->where('type', 'earning')->sum('amount') - $rows->where('type', 'deduction')->sum('amount'),
                'details' => $rows->map(fn($d) => [
                    'component_name' => $d->component->name,
                    'type' => $d->type,
                    'amount' => $d->amount,
                    'notes' => $d->notes,
                ])->values(),
            ];
        })->values();

        return Inertia::render('Payroll/Report/Index', [
            'run' => [
                'id' => $payrollRun->id,
                'period_label' => $payrollRun->period_label,
                'status' => $payrollRun->status,
                'total_gross' => $payrollRun->total_gross,
                'total_net' => $payrollRun->total_net,
            ],
            'employees' => $employees,
        ]);
    }

    /** Individual slip gaji */
    public function slip(PayrollRun $payrollRun, Employee $employee): Response
    {
        $details = $payrollRun->details()
            ->where('employee_id', $employee->id)
            ->with('component')
            ->orderBy('type')
            ->get();

        return Inertia::render('Payroll/Slip/Show', [
            'run' => [
                'id' => $payrollRun->id,
                'period_label' => $payrollRun->period_label,
                'status' => $payrollRun->status,
            ],
            'employee' => [
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
            ],
            'earnings' => $details->where('type', 'earning')->values(),
            'deductions' => $details->where('type', 'deduction')->values(),
            'gross' => $details->where('type', 'earning')->sum('amount'),
            'total_deductions' => $details->where('type', 'deduction')->sum('amount'),
            'net' => $details->where('type', 'earning')->sum('amount') - $details->where('type', 'deduction')->sum('amount'),
        ]);
    }

    /** Employee self-service: view own latest slip (most recent approved/paid run) */
    public function mySlip(): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            abort(404, 'Data karyawan tidak ditemukan untuk akun ini.');
        }

        // Find latest run that has been approved or paid and contains this employee
        $run = PayrollRun::whereIn('status', ['approved', 'paid'])
            ->whereHas('details', fn($q) => $q->where('employee_id', $employee->id))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->first();

        if (!$run) {
            return Inertia::render('Payroll/Slip/Show', [
                'run' => null,
                'employee' => ['full_name' => $employee->full_name, 'employee_code' => $employee->employee_code],
                'earnings' => [],
                'deductions' => [],
                'gross' => 0,
                'total_deductions' => 0,
                'net' => 0,
            ]);
        }

        $details = $run->details()
            ->where('employee_id', $employee->id)
            ->with('component')
            ->orderBy('type')
            ->get();

        return Inertia::render('Payroll/Slip/Show', [
            'run' => ['id' => $run->id, 'period_label' => $run->period_label, 'status' => $run->status],
            'employee' => ['full_name' => $employee->full_name, 'employee_code' => $employee->employee_code],
            'earnings' => $details->where('type', 'earning')->values(),
            'deductions' => $details->where('type', 'deduction')->values(),
            'gross' => $details->where('type', 'earning')->sum('amount'),
            'total_deductions' => $details->where('type', 'deduction')->sum('amount'),
            'net' => $details->where('type', 'earning')->sum('amount') - $details->where('type', 'deduction')->sum('amount'),
        ]);
    }

    /** Export payroll as Excel */
    public function export(PayrollRun $payrollRun)
    {
        $filename = 'Payroll_' . str_replace(' ', '_', $payrollRun->period_label) . '.xlsx';
        return Excel::download(new PayrollExport($payrollRun), $filename);
    }

    // --- Helpers ---

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }
        return $days;
    }
}
