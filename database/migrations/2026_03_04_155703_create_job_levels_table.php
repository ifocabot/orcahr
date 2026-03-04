<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_levels', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->unsignedTinyInteger('level')->unique()->comment('Urutan level: 1=tertinggi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_levels');
    }
};
