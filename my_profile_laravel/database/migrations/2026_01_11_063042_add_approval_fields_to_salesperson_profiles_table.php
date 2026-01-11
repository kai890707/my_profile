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
            // Add back approval fields for salesperson profile approval workflow
            if (! Schema::hasColumn('salesperson_profiles', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('avatar_size');

                $table->text('rejected_reason')
                    ->nullable()
                    ->after('approval_status');

                $table->unsignedBigInteger('approved_by')
                    ->nullable()
                    ->after('rejected_reason');

                $table->timestamp('approved_at')
                    ->nullable()
                    ->after('approved_by');

                // Add index for approval_status
                $table->index('approval_status');

                // Add foreign key for approved_by
                $table->foreign('approved_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('salesperson_profiles', 'approval_status')) {
                // Drop index and foreign key first
                $table->dropIndex(['approval_status']);
                $table->dropForeign(['approved_by']);

                // Drop columns
                $table->dropColumn([
                    'approval_status',
                    'rejected_reason',
                    'approved_by',
                    'approved_at',
                ]);
            }
        });
    }
};
