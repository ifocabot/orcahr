<?php

namespace App\Jobs;

use App\Models\AttendanceSummary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateDirtySummaries implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        AttendanceSummary::dirty()->chunk(100, function ($summaries) {
            foreach ($summaries as $summary) {
                ProcessAttendanceBatch::dispatch($summary->employee_id, $summary->work_date->toDateString());
            }
        });
    }
}
