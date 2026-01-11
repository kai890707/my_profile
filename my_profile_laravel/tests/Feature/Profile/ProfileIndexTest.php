<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;

use function Pest\Laravel\getJson;

beforeEach(function (): void {
    // Create users first (needed for company created_by)
    $this->user1 = User::create([
        'username' => 'user1',
        'name' => 'User One',
        'email' => 'user1@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    $this->user2 = User::create([
        'username' => 'user2',
        'name' => 'User Two',
        'email' => 'user2@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Create industry for company
    $this->industry = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    // Create company
    $this->company = Company::create([
        'name' => 'Test Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user1->id,
    ]);
});

test('can get list of approved profiles', function (): void {
    // Create approved profiles
    SalespersonProfile::create([
        'user_id' => $this->user1->id,
        'company_id' => $this->company->id,
        'full_name' => 'User One',
        'phone' => '0912345678',
        'bio' => 'Test bio',
        'specialties' => 'Testing',
        'service_regions' => [1, 2],
        'approval_status' => 'approved',
    ]);

    SalespersonProfile::create([
        'user_id' => $this->user2->id,
        'company_id' => $this->company->id,
        'full_name' => 'User Two',
        'phone' => '0987654321',
        'approval_status' => 'approved',
    ]);

    $response = getJson('/api/profiles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'profiles' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'company_id',
                            'full_name',
                            'phone',
                            'bio',
                            'specialties',
                            'service_regions',
                            'approval_status',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('data.profiles.data'))->toHaveCount(2);
});

test('only shows approved profiles', function (): void {
    // Create profiles with different statuses
    SalespersonProfile::create([
        'user_id' => $this->user1->id,
        'company_id' => $this->company->id,
        'full_name' => 'Approved User',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);

    SalespersonProfile::create([
        'user_id' => $this->user2->id,
        'company_id' => $this->company->id,
        'full_name' => 'Pending User',
        'phone' => '0987654321',
        'approval_status' => 'pending',
    ]);

    $response = getJson('/api/profiles');

    $response->assertStatus(200);

    $profiles = $response->json('data.profiles.data');
    expect($profiles)->toHaveCount(1);
    expect($profiles[0]['full_name'])->toBe('Approved User');
});

test('can filter profiles by company', function (): void {
    $company2 = Company::create([
        'name' => 'Second Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user1->id,
    ]);

    SalespersonProfile::create([
        'user_id' => $this->user1->id,
        'company_id' => $this->company->id,
        'full_name' => 'User One',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);

    SalespersonProfile::create([
        'user_id' => $this->user2->id,
        'company_id' => $company2->id,
        'full_name' => 'User Two',
        'phone' => '0987654321',
        'approval_status' => 'approved',
    ]);

    $response = getJson("/api/profiles?company_id={$this->company->id}");

    $response->assertStatus(200);

    $profiles = $response->json('data.profiles.data');
    expect($profiles)->toHaveCount(1);
    expect($profiles[0]['company_id'])->toBe($this->company->id);
});

test('can search profiles by name and specialties', function (): void {
    SalespersonProfile::create([
        'user_id' => $this->user1->id,
        'company_id' => $this->company->id,
        'full_name' => 'John Developer',
        'phone' => '0912345678',
        'specialties' => 'Laravel, PHP',
        'approval_status' => 'approved',
    ]);

    SalespersonProfile::create([
        'user_id' => $this->user2->id,
        'company_id' => $this->company->id,
        'full_name' => 'Jane Designer',
        'phone' => '0987654321',
        'specialties' => 'UI/UX Design',
        'approval_status' => 'approved',
    ]);

    $response = getJson('/api/profiles?search=Laravel');

    $response->assertStatus(200);

    $profiles = $response->json('data.profiles.data');
    expect($profiles)->toHaveCount(1);
    expect($profiles[0]['full_name'])->toBe('John Developer');
});

test('pagination works correctly', function (): void {
    // Create 20 profiles
    for ($i = 3; $i <= 22; $i++) {
        $user = User::create([
            'username' => "user{$i}",
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $user->id,
            'company_id' => $this->company->id,
            'full_name' => "User {$i}",
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);
    }

    $response = getJson('/api/profiles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'profiles' => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ],
            ],
        ]);

    expect($response->json('data.profiles.per_page'))->toBe(15);
    expect($response->json('data.profiles.total'))->toBe(20);
});

test('can customize per page parameter', function (): void {
    // Create 10 profiles
    for ($i = 3; $i <= 12; $i++) {
        $user = User::create([
            'username' => "user{$i}",
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $user->id,
            'company_id' => $this->company->id,
            'full_name' => "User {$i}",
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);
    }

    $response = getJson('/api/profiles?per_page=5');

    $response->assertStatus(200);

    expect($response->json('data.profiles.per_page'))->toBe(5);
    expect($response->json('data.profiles.data'))->toHaveCount(5);
});

test('returns empty array when no profiles exist', function (): void {
    $response = getJson('/api/profiles');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('data.profiles.data'))->toBeArray();
    expect($response->json('data.profiles.data'))->toHaveCount(0);
});
