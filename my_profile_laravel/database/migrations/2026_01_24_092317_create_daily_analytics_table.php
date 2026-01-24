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
        Schema::create('daily_analytics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesperson_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('profile_views_count')->default(0);
            $table->unsignedInteger('contact_requests_count')->default(0);
            $table->unsignedInteger('unique_visitors_count')->default(0);
            $table->timestamps();

            // 唯一約束: 每個業務員每天只有一筆記錄
            $table->unique(['salesperson_id', 'date'], 'unique_salesperson_date');

            // 複合索引: 業務員時間範圍查詢
            $table->index(['salesperson_id', 'date'], 'idx_salesperson_date');

            // 單欄索引: 日期查詢
            $table->index('date', 'idx_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_analytics');
    }
};
