<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['ktp', 'npwp', 'kontrak', 'ijazah', 'sertifikasi', 'foto', 'other']);
            $table->string('original_name');          // nama file asli untuk tampilan
            $table->string('file_path');              // path di private disk
            $table->date('expires_at')->nullable();   // tanggal expired (kontrak, dll)
            $table->string('notes')->nullable();      // keterangan singkat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
