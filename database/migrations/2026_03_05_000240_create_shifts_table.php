<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->time('clock_in');
            $table->time('clock_out');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_flexible')->default(false);
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
            $table->unsignedSmallInteger('early_leave_tolerance_minutes')->default(15);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
