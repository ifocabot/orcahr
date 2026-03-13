<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('payroll_components')->restrictOnDelete();
            $table->enum('type', ['earning', 'deduction', 'benefit']);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['payroll_run_id', 'employee_id'], 'idx_payroll_detail');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
