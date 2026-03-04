<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id')->nullable()->unique()->comment('Link ke users table, nullable');
            $table->string('employee_number', 20)->unique()->comment('Auto-generated: RKS-YYYY-NNNN');
            $table->string('full_name');
            $table->string('email')->unique()->comment('Email kantor');

            // Encrypted fields — suffix _encrypted
            $table->text('personal_email_encrypted')->nullable();
            $table->text('phone_encrypted')->nullable();
            $table->text('nik_encrypted')->nullable()->comment('AES-256 encrypted');
            $table->string('nik_hash', 64)->nullable()->unique()->comment('HMAC-SHA256 untuk uniqueness check');
            $table->text('npwp_encrypted')->nullable();
            $table->string('npwp_hash', 64)->nullable()->unique();
            $table->text('birth_place_encrypted')->nullable();
            $table->text('address_encrypted')->nullable();

            // Unencrypted — masih RBAC-protected di Blade
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->enum('religion', ['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu', 'other'])->nullable();
            $table->string('photo')->nullable()->comment('Path ke storage');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
