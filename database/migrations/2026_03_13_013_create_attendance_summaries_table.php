<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->foreignId('shift_id')->nullable()->constrained('shift_masters')->nullOnDelete();
            $table->dateTime('actual_in')->nullable();
            $table->dateTime('actual_out')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            $table->unsignedSmallInteger('overtime_minutes')->default(0);
            $table->unsignedSmallInteger('work_duration_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'leave', 'holiday', 'half_permit'])->default('absent');
            $table->boolean('dirty_flag')->default(true);
            $table->dateTime('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
            $table->index('dirty_flag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};
