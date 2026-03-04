<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('employee_id');
            $table->text('bank_name_encrypted');
            $table->text('branch_encrypted')->nullable();
            $table->text('account_number_encrypted');
            $table->text('account_holder_encrypted');
            $table->boolean('is_primary')->default(true)->comment('Rekening utama untuk payroll');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
