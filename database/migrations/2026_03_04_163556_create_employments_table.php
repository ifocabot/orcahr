<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('employee_id');
            $table->ulid('department_id');
            $table->ulid('position_id');
            $table->ulid('job_level_id');
            $table->enum('employment_status', ['permanent', 'contract', 'probation'])->default('permanent');
            $table->date('join_date');
            $table->date('end_date')->nullable()->comment('null = masih aktif');
            $table->date('effective_from')->comment('Effective date untuk history');
            $table->date('effective_to')->nullable()->comment('null = current/active');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->restrictOnDelete();
            $table->foreign('job_level_id')->references('id')->on('job_levels')->restrictOnDelete();

            // Index untuk query current employment
            $table->index(['employee_id', 'effective_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employments');
    }
};
