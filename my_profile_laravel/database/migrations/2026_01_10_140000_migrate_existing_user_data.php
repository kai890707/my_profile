<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrate existing users with salesperson_profiles to role='salesperson', status='approved'
        DB::statement("
            UPDATE users
            SET role = 'salesperson',
                salesperson_status = 'approved',
                salesperson_applied_at = created_at,
                salesperson_approved_at = created_at
            WHERE id IN (
                SELECT DISTINCT user_id
                FROM salesperson_profiles
            )
            AND role IS NULL
        ");

        // 2. Set remaining users without salesperson_profiles to role='user'
        DB::statement("
            UPDATE users
            SET role = 'user'
            WHERE role IS NULL
        ");

        // 3. Set existing companies to is_personal=false
        DB::statement("
            UPDATE companies
            SET is_personal = false
            WHERE is_personal IS NULL
        ");

        // 4. Set admin role for specific users (if any admins exist)
        // This should be done manually or via seeder based on your business logic
        // Example:
        // DB::statement("UPDATE users SET role = 'admin' WHERE email = 'admin@example.com'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback changes
        DB::statement("
            UPDATE users
            SET role = NULL,
                salesperson_status = NULL,
                salesperson_applied_at = NULL,
                salesperson_approved_at = NULL
            WHERE role IN ('user', 'salesperson')
        ");

        DB::statement("
            UPDATE companies
            SET is_personal = NULL
        ");
    }
};
