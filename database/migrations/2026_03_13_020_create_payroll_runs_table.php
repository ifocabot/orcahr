<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('period_month')->comment('1–12');
            $table->unsignedSmallInteger('period_year');
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid'])->default('draft');
            $table->decimal('total_gross', 18, 2)->default(0);
            $table->decimal('total_deductions', 18, 2)->default(0);
            $table->decimal('total_net', 18, 2)->default(0);
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('calculated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['period_month', 'period_year'], 'uk_payroll_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
