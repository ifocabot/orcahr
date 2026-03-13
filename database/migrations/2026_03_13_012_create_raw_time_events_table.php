<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('raw_time_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->dateTime('event_time');
            $table->enum('event_type', ['IN', 'OUT']);
            $table->string('source', 20)->default('web');
            $table->boolean('processed_flag')->default(false);
            $table->string('selfie_path', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'event_time']);
            $table->index('processed_flag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_time_events');
    }
};
