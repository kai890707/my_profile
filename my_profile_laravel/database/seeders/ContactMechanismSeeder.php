<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactMechanismSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Update salesperson profiles with contact methods
        $this->seedSalespersonContactMethods();

        // 2. Create contact requests
        $this->seedContactRequests();

        // 3. Create contact events
        $this->seedContactEvents();
    }

    /**
     * Seed contact methods for existing salesperson profiles.
     */
    private function seedSalespersonContactMethods(): void
    {
        $salespersons = User::where('role', 'salesperson')
            ->with('salespersonProfile')
            ->limit(20)
            ->get();

        foreach ($salespersons as $salesperson) {
            if (! $salesperson->salespersonProfile) {
                continue;
            }

            $profile = $salesperson->salespersonProfile;

            // 80% chance to have all contact methods
            $hasAllMethods = rand(1, 100) <= 80;

            if ($hasAllMethods) {
                $profile->update([
                    'email_public' => "salesperson{$salesperson->id}@example.com",
                    'line_id' => 'line_'.strtolower($salesperson->name),
                    'wechat_id' => 'wechat_'.strtolower($salesperson->name),
                    'contact_preferences' => ['line', 'phone', 'email', 'wechat'],
                ]);
            } else {
                // 20% chance to have partial contact methods
                $profile->update([
                    'email_public' => rand(0, 1) ? "salesperson{$salesperson->id}@example.com" : null,
                    'line_id' => rand(0, 1) ? 'line_'.strtolower($salesperson->name) : null,
                    'wechat_id' => rand(0, 1) ? 'wechat_'.strtolower($salesperson->name) : null,
                    'contact_preferences' => ['phone', 'email'],
                ]);
            }
        }

        $this->command->info('✓ Updated contact methods for salesperson profiles');
    }

    /**
     * Seed contact requests.
     */
    private function seedContactRequests(): void
    {
        $users = User::where('role', 'user')->limit(10)->get();
        $salespersons = User::where('role', 'salesperson')
            ->whereHas('salespersonProfile', function ($query) {
                $query->where('approval_status', 'approved');
            })
            ->limit(10)
            ->get();

        if ($users->isEmpty() || $salespersons->isEmpty()) {
            $this->command->warn('⚠ No users or approved salespersons found. Skipping contact requests seeding.');

            return;
        }

        $statuses = ['pending', 'contacted', 'closed'];
        $messages = [
            '您好，我想了解保險相關服務，請問方便聯繫嗎？',
            '想諮詢貴公司的理財規劃服務。',
            '對您的專業服務很感興趣，希望能進一步了解。',
            '我需要協助規劃我的投資組合，希望能與您聯繫。',
            '想要了解更多關於退休規劃的資訊。',
        ];

        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $salesperson = $salespersons->random();

            ContactRequest::create([
                'user_id' => $user->id,
                'salesperson_id' => $salesperson->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => rand(0, 1) ? '0912-'.rand(100000, 999999) : null,
                'message' => $messages[array_rand($messages)],
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('✓ Created 50 contact requests');
    }

    /**
     * Seed contact events.
     */
    private function seedContactEvents(): void
    {
        $users = User::where('role', 'user')->limit(10)->get();
        $salespersons = User::where('role', 'salesperson')->limit(10)->get();

        if ($users->isEmpty() || $salespersons->isEmpty()) {
            $this->command->warn('⚠ No users or salespersons found. Skipping contact events seeding.');

            return;
        }

        $eventTypes = ['profile_view', 'contact_form_submission'];

        // Create 200 events
        for ($i = 0; $i < 200; $i++) {
            $salesperson = $salespersons->random();
            $hasUser = rand(1, 100) <= 70; // 70% logged in, 30% anonymous

            ContactEvent::create([
                'user_id' => $hasUser ? $users->random()->id : null,
                'salesperson_id' => $salesperson->id,
                'event_type' => $eventTypes[array_rand($eventTypes)],
                'ip_address_hash' => hash('sha256', '127.0.0.'.rand(1, 255)),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('✓ Created 200 contact events');
    }
}
