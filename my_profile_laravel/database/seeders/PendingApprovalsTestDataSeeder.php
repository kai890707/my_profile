<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Experience;
use App\Models\SalespersonProfile;
use Illuminate\Database\Seeder;

class PendingApprovalsTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates test data for pending approvals testing.
     * It creates pending, approved, and rejected records to verify
     * that only pending items appear in the admin approvals list.
     */
    public function run(): void
    {
        // Get or create salesperson profiles for testing
        $profiles = SalespersonProfile::with('user')->limit(3)->get();

        if ($profiles->count() < 3) {
            $this->command->error('Not enough salesperson profiles. Please run salesperson seeder first.');

            return;
        }

        // Create pending experiences
        foreach ($profiles as $index => $profile) {
            Experience::create([
                'user_id' => $profile->user_id,
                'company' => 'Test Company ' . ($index + 1),
                'position' => 'Test Position ' . ($index + 1),
                'start_date' => now()->subYears(2),
                'end_date' => now()->subYears(1),
                'description' => 'This is a pending test experience ' . ($index + 1),
                'approval_status' => 'pending',
            ]);
        }

        // Create approved experiences (should not appear in pending list)
        Experience::create([
            'user_id' => $profiles[0]->user_id,
            'company' => 'Approved Company',
            'position' => 'Approved Position',
            'start_date' => now()->subYears(3),
            'end_date' => now()->subYears(2),
            'description' => 'This is an approved test experience',
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);

        // Create rejected experiences (should not appear in pending list)
        Experience::create([
            'user_id' => $profiles[1]->user_id,
            'company' => 'Rejected Company',
            'position' => 'Rejected Position',
            'start_date' => now()->subYears(2),
            'end_date' => now()->subYear(),
            'description' => 'This is a rejected test experience',
            'approval_status' => 'rejected',
            'approved_by' => 1,
            'approved_at' => now(),
            'rejected_reason' => 'Test rejection reason',
        ]);

        // Create pending certifications
        foreach ($profiles as $index => $profile) {
            Certification::create([
                'user_id' => $profile->user_id,
                'name' => 'Test Certification ' . ($index + 1),
                'issuer' => 'Test Issuer ' . ($index + 1),
                'issue_date' => now()->subYears(2),
                'expiry_date' => now()->addYears(3),
                'description' => 'This is a pending test certification ' . ($index + 1),
                'approval_status' => 'pending',
            ]);
        }

        // Create approved certifications (should not appear in pending list)
        Certification::create([
            'user_id' => $profiles[0]->user_id,
            'name' => 'Approved Certification',
            'issuer' => 'Approved Issuer',
            'issue_date' => now()->subYears(3),
            'expiry_date' => now()->addYears(2),
            'description' => 'This is an approved test certification',
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);

        // Create rejected certifications (should not appear in pending list)
        Certification::create([
            'user_id' => $profiles[1]->user_id,
            'name' => 'Rejected Certification',
            'issuer' => 'Rejected Issuer',
            'issue_date' => now()->subYears(2),
            'expiry_date' => now()->addYear(),
            'description' => 'This is a rejected test certification',
            'approval_status' => 'rejected',
            'approved_by' => 1,
            'approved_at' => now(),
            'rejected_reason' => 'Certification expired',
        ]);

        $this->command->info('✅ Created test data for pending approvals:');
        $this->command->info('   - 3 pending experiences');
        $this->command->info('   - 1 approved experience');
        $this->command->info('   - 1 rejected experience');
        $this->command->info('   - 3 pending certifications');
        $this->command->info('   - 1 approved certification');
        $this->command->info('   - 1 rejected certification');
    }
}
