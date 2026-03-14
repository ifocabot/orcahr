<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->enum('manager_approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('status');
            $table->foreignId('manager_approved_by')
                ->nullable()
                ->after('manager_approval_status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['manager_approved_by']);
            $table->dropColumn(['manager_approval_status', 'manager_approved_by', 'manager_approved_at']);
        });
    }
};
