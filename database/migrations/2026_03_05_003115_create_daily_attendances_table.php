<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignUlid('schedule_id')->nullable()->constrained('schedule_assignments')->nullOnDelete();
            $table->datetime('clock_in')->nullable();
            $table->datetime('clock_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'early_leave', 'leave', 'holiday', 'weekend'])->default('absent');
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            $table->unsignedSmallInteger('overtime_minutes')->default(0);
            $table->decimal('work_hours', 8, 2)->default(0);
            $table->json('source_log_ids')->nullable();
            $table->datetime('calculated_at')->nullable();
            $table->string('calculation_trigger')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
