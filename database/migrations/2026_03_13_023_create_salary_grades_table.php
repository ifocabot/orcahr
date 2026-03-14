<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('component_id')->constrained('payroll_components')->cascadeOnDelete();
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->boolean('is_mandatory')->default(false);
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['position_id', 'job_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_grades');
    }
};
