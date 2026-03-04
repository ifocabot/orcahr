<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_bpjs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('employee_id')->unique()->comment('One-to-one dengan employee');
            $table->text('bpjs_kes_encrypted')->nullable()->comment('BPJS Kesehatan number');
            $table->text('bpjs_tk_encrypted')->nullable()->comment('BPJS Ketenagakerjaan number');
            $table->enum('bpjs_class', ['1', '2', '3'])->nullable()->comment('Kelas BPJS Kesehatan');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_bpjs');
    }
};
