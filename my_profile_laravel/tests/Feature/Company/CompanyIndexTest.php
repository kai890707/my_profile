<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\User;
use function Pest\Laravel\getJson;

beforeEach(function (): void {
    // Create user first (needed for company created_by)
    $this->user = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Create industries
    $this->industry1 = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $this->industry2 = Industry::create([
        'name' => 'Finance',
        'slug' => 'finance',
    ]);
});

test('can get list of approved companies', function (): void {
    // Create approved companies
    Company::create([
        'name' => 'Tech Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry1->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'Finance Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry2->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $response = getJson('/api/companies');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'companies' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'tax_id',
                            'industry_id',
                            'address',
                            'phone',
                            'approval_status',
                            'created_by',
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

    expect($response->json('data.companies.data'))->toHaveCount(2);
});

test('only shows approved companies', function (): void {
    // Create companies with different statuses
    Company::create([
        'name' => 'Approved Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry1->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'Pending Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry1->id,
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    $response = getJson('/api/companies');

    $response->assertStatus(200);

    $companies = $response->json('data.companies.data');
    expect($companies)->toHaveCount(1);
    expect($companies[0]['name'])->toBe('Approved Company');
});

test('can filter companies by industry', function (): void {
    Company::create([
        'name' => 'Tech Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry1->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'Finance Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry2->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $response = getJson("/api/companies?industry_id={$this->industry1->id}");

    $response->assertStatus(200);

    $companies = $response->json('data.companies.data');
    expect($companies)->toHaveCount(1);
    expect($companies[0]['industry_id'])->toBe($this->industry1->id);
});

test('can search companies by name', function (): void {
    Company::create([
        'name' => 'ABC Technology Inc',
        'tax_id' => '12345678',
        'industry_id' => $this->industry1->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'XYZ Finance Corp',
        'tax_id' => '87654321',
        'industry_id' => $this->industry2->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $response = getJson('/api/companies?search=Technology');

    $response->assertStatus(200);

    $companies = $response->json('data.companies.data');
    expect($companies)->toHaveCount(1);
    expect($companies[0]['name'])->toBe('ABC Technology Inc');
});

test('pagination works correctly', function (): void {
    // Create 20 companies
    for ($i = 1; $i <= 20; $i++) {
        Company::create([
            'name' => "Company {$i}",
            'tax_id' => "TAX{$i}",
            'industry_id' => $this->industry1->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);
    }

    $response = getJson('/api/companies');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'companies' => [
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

    expect($response->json('data.companies.per_page'))->toBe(15);
    expect($response->json('data.companies.total'))->toBe(20);
});

test('can customize per page parameter', function (): void {
    // Create 10 companies
    for ($i = 1; $i <= 10; $i++) {
        Company::create([
            'name' => "Company {$i}",
            'tax_id' => "TAX{$i}",
            'industry_id' => $this->industry1->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);
    }

    $response = getJson('/api/companies?per_page=5');

    $response->assertStatus(200);

    expect($response->json('data.companies.per_page'))->toBe(5);
    expect($response->json('data.companies.data'))->toHaveCount(5);
});

test('returns empty array when no companies exist', function (): void {
    $response = getJson('/api/companies');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('data.companies.data'))->toBeArray();
    expect($response->json('data.companies.data'))->toHaveCount(0);
});
