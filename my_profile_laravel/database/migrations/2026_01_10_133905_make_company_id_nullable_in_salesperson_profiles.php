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
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // 將 company_id 改為 nullable（支援獨立業務員）
            $table->unsignedBigInteger('company_id')
                ->nullable()
                ->change();

            // 移除舊的審核欄位（改用 Users table 的 salesperson_status）
            if (Schema::hasColumn('salesperson_profiles', 'approval_status')) {
                // Drop index first (SQLite requires this before dropping the column)
                $table->dropIndex(['approval_status']);

                // Drop foreign key for approved_by
                $table->dropForeign(['approved_by']);

                // Now drop the columns
                $table->dropColumn([
                    'approval_status',
                    'rejected_reason',
                    'approved_by',
                    'approved_at',
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // 恢復 company_id 為 not nullable
            $table->unsignedBigInteger('company_id')
                ->nullable(false)
                ->change();

            // 恢復審核欄位
            if (!Schema::hasColumn('salesperson_profiles', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending');
                $table->text('rejected_reason')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();

                // Restore index and foreign key
                $table->index('approval_status');
                $table->foreign('approved_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }
        });
    }
};
