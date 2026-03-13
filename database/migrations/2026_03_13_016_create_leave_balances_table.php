<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->date('balance_date');
            $table->decimal('opening_balance', 5, 2)->default(0);
            $table->decimal('accrued', 5, 2)->default(0);
            $table->decimal('used', 5, 2)->default(0);
            $table->decimal('adjustment', 5, 2)->default(0);
            $table->decimal('closing_balance', 5, 2)->default(0);
            $table->integer('entitlement_year');
            $table->date('expiry_date');
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'balance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
