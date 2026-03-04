<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clock_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('employee_id')->constrained()->cascadeOnDelete();
            $table->datetime('timestamp');
            $table->enum('type', ['clock_in', 'clock_out']);
            $table->enum('source', ['web', 'mobile', 'manual'])->default('web');
            $table->string('ip_address', 45)->nullable();
            $table->json('location')->nullable();  // {lat, lng, accuracy}
            $table->string('photo')->nullable();   // path ke foto selfie (opsional)
            $table->boolean('is_manual')->default(false);
            $table->text('manual_reason')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clock_logs');
    }
};
