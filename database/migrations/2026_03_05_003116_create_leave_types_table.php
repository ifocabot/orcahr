<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->unsignedTinyInteger('default_quota')->default(12);
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_carry_forward')->default(false);
            $table->unsignedTinyInteger('max_carry_forward')->default(0);
            $table->boolean('requires_attachment')->default(false);
            $table->unsignedTinyInteger('min_days_advance')->default(1);
            $table->unsignedTinyInteger('max_consecutive_days')->default(0); // 0 = unlimited
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
