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
        Schema::table('companies', function (Blueprint $table): void {
            // 1. 新增 is_personal 欄位
            $table->boolean('is_personal')
                ->default(false)
                ->after('tax_id')
                ->comment('是否為個人工作室');

            // 2. 移除 foreign keys（先移除外鍵，才能移除欄位）
            $table->dropForeign(['industry_id']);
            $table->dropForeign(['approved_by']);

            // 3. 移除 indexes
            $table->dropIndex(['industry_id']);
            $table->dropIndex(['approval_status']);

            // 4. 移除不需要的欄位
            $table->dropColumn([
                'industry_id',
                'address',
                'phone',
                'approval_status',
                'rejected_reason',
                'approved_by',
                'approved_at',
            ]);
        });

        // 5. 將 tax_id 改為 nullable（需要在另一個 closure 中執行）
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('tax_id', 50)
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 恢復 tax_id 為 not nullable
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('tax_id', 50)
                ->nullable(false)
                ->change();
        });

        Schema::table('companies', function (Blueprint $table): void {
            // 恢復欄位
            $table->unsignedBigInteger('industry_id')->nullable()->after('tax_id');
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // 移除新增的欄位
            $table->dropColumn('is_personal');

            // 恢復 foreign keys 和 indexes
            $table->foreign('industry_id')
                ->references('id')
                ->on('industries')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('industry_id');
            $table->index('approval_status');
        });
    }
};
