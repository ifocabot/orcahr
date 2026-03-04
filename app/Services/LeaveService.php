<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Submit permohonan cuti.
     */
    public function submit(Employee $employee, array $data, ?UploadedFile $attachment = null): LeaveRequest
    {
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $totalDays = $this->countWorkingDays($start, $end);

        // Validasi: saldo cukup
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $start->year)
            ->first();

        if (!$balance || $balance->remaining() < $totalDays) {
            throw ValidationException::withMessages([
                'leave_type_id' => "Saldo cuti tidak cukup (tersisa {$balance?->remaining()} hari).",
            ]);
        }

        // Validasi: min advance notice
        if ($leaveType->min_days_advance > 0) {
            $advanceDays = now()->diffInDays($start, false);
            if ($advanceDays < $leaveType->min_days_advance) {
                throw ValidationException::withMessages([
                    'start_date' => "Pengajuan cuti harus minimal {$leaveType->min_days_advance} hari sebelumnya.",
                ]);
            }
        }

        // Simpan attachment jika ada
        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store("leave-attachments/{$employee->id}", 'private');
        }

        // Update saldo: naikkan pending
        $balance->increment('pending', $totalDays);

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);
    }

    /**
     * Approve permohonan cuti.
     */
    public function approve(LeaveRequest $leave, \App\Models\User $approver): void
    {
        if (!$leave->isPending()) {
            throw new \RuntimeException('Permohonan sudah diproses sebelumnya.');
        }

        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', Carbon::parse($leave->start_date)->year)
            ->firstOrFail();

        // Pindah dari pending ke used
        $balance->decrement('pending', $leave->total_days);
        $balance->increment('used', $leave->total_days);

        $leave->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject permohonan cuti.
     */
    public function reject(LeaveRequest $leave, \App\Models\User $approver, string $reason): void
    {
        if (!$leave->isPending()) {
            throw new \RuntimeException('Permohonan sudah diproses sebelumnya.');
        }

        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', Carbon::parse($leave->start_date)->year)
            ->first();

        // Kembalikan pending
        if ($balance) {
            $balance->decrement('pending', $leave->total_days);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Hitung hari kerja (exclude weekend + holiday).
     */
    public function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $count = 0;
        foreach (CarbonPeriod::create($start, $end) as $day) {
            if (!$day->isWeekend() && !in_array($day->toDateString(), $holidays)) {
                $count++;
            }
        }

        return $count;
    }
}
