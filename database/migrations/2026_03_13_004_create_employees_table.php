<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique();
            $table->string('full_name', 100);
            $table->text('email')->comment('Encrypted AES-256');
            $table->text('nik')->nullable()->comment('Encrypted — PDP UU27/2022');
            $table->string('nik_hash', 64)->nullable()->unique()->comment('Blind index SHA-256');
            $table->text('npwp')->nullable()->comment('Encrypted — Data pajak');
            $table->text('phone')->nullable()->comment('Encrypted — Kontak pribadi');
            $table->string('bank_name', 50)->nullable();
            $table->text('bank_account_number')->nullable()->comment('Encrypted — Data finansial');
            $table->text('bank_account_name')->nullable()->comment('Encrypted — Data finansial');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->date('join_date');
            $table->date('resign_date')->nullable();
            $table->enum('employment_status', ['active', 'probation', 'resigned', 'terminated'])->default('active');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->timestamps();
        });

        // Deferred FK: departments.manager_id → employees.id
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::dropIfExists('employees');
    }
};
