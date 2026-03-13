<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_payroll_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('payroll_components')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->default(0)->comment('Nominal komponen untuk karyawan ini');
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'effective_date'], 'idx_payroll_config');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_configs');
    }
};
