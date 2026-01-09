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
            // Add username field
            $table->string('username', 100)->unique()->after('id');

            // Rename password to password_hash for CI4 compatibility
            $table->renameColumn('password', 'password_hash');

            // Add role field with enum values
            $table->enum('role', ['salesperson', 'user', 'admin'])
                ->default('user')
                ->after('password_hash');

            // Add status field with enum values
            $table->enum('status', ['pending', 'active', 'inactive'])
                ->default('pending')
                ->after('role');

            // Add soft deletes
            $table->softDeletes()->after('updated_at');

            // Add index for role
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Remove indexes
            $table->dropIndex(['role']);

            // Drop soft deletes
            $table->dropSoftDeletes();

            // Drop status field
            $table->dropColumn('status');

            // Drop role field
            $table->dropColumn('role');

            // Rename password_hash back to password
            $table->renameColumn('password_hash', 'password');

            // Drop username field
            $table->dropColumn('username');
        });
    }
};
