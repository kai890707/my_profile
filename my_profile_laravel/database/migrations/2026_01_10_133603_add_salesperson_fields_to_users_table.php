<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Note: 'role' column already exists from earlier migration, no need to add it again

            // 業務員審核狀態
            $table->enum('salesperson_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->after('role')
                ->comment('null=一般使用者, pending=未審核, approved=已審核, rejected=已拒絕');

            // 業務員申請/升級時間
            $table->timestamp('salesperson_applied_at')
                ->nullable()
                ->after('salesperson_status');

            // 業務員審核通過時間
            $table->timestamp('salesperson_approved_at')
                ->nullable()
                ->after('salesperson_applied_at');

            // 審核拒絕原因
            $table->text('rejection_reason')
                ->nullable()
                ->after('salesperson_approved_at');

            // 可重新申請的時間
            $table->timestamp('can_reapply_at')
                ->nullable()
                ->after('rejection_reason');

            // 付費會員標記（預留）
            $table->boolean('is_paid_member')
                ->default(false)
                ->after('can_reapply_at');

            // Indexes (role index already exists from earlier migration)
            $table->index('salesperson_status');
            $table->index(['role', 'salesperson_status'], 'idx_role_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('idx_role_status');
            $table->dropIndex(['salesperson_status']);
            // Note: Don't drop 'role' index as it was created by earlier migration

            $table->dropColumn([
                'salesperson_status',
                'salesperson_applied_at',
                'salesperson_approved_at',
                'rejection_reason',
                'can_reapply_at',
                'is_paid_member',
            ]);
            // Note: Don't drop 'role' column as it was created by earlier migration
        });
    }
};
