<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->enum('type', ['earning', 'deduction', 'benefit']);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_fixed')->default(true)->comment('Fixed amount vs formula-based');
            $table->text('formula')->nullable()->comment('Rumus kalkulasi jika is_fixed=false');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_components');
    }
};
