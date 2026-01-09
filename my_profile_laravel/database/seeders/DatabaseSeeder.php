<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed reference data first
        $this->call([
            IndustrySeeder::class,
            RegionSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ 已建立測試 Admin 帳號 (帳號: admin, 密碼: admin123)');

        // Create salesperson user
        $salesperson = User::create([
            'username' => 'salesperson_test',
            'name' => 'Test Salesperson',
            'email' => 'salesperson@example.com',
            'password_hash' => Hash::make('test123'),
            'role' => 'salesperson',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ 已建立測試業務員帳號 (帳號: salesperson_test, 密碼: test123)');

        // Create salesperson profile
        SalespersonProfile::create([
            'user_id' => $salesperson->id,
            'company_id' => null,
            'full_name' => '測試業務員',
            'phone' => '0912345678',
            'bio' => '這是一個測試業務員帳號，用於開發與測試。',
            'specialties' => '軟體銷售, 系統整合',
            'service_regions' => ['台北市', '新北市'],
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->command->info('✓ 已建立測試業務員資料');
        $this->command->info('');
        $this->command->info('=== DatabaseSeeder 執行完成 ===');
    }
}
