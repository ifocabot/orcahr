<?php

use App\Jobs\MonthlyLeaveAccrual;
use App\Jobs\ProcessAttendanceBatch;
use App\Jobs\RecalculateDirtySummaries;
use App\Jobs\YearlyLeaveCarryover;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process unprocessed raw time events every 5 minutes
Schedule::job(new ProcessAttendanceBatch())->everyFiveMinutes();

// Recalculate dirty attendance summaries every 10 minutes
Schedule::job(new RecalculateDirtySummaries())->everyTenMinutes();

// Leave: monthly accrual on 1st of each month at 00:05
Schedule::job(new MonthlyLeaveAccrual())->monthlyOn(1, '00:05');

// Leave: yearly carryover on 1 January at 00:10
Schedule::job(new YearlyLeaveCarryover())->yearlyOn(1, 1, '00:10');
