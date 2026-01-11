<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Create user first (needed for company created_by)
    $this->user = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
        'salesperson_status' => 'approved',
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can create company', function (): void {
    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'company' => [
                'id',
                'name',
                'tax_id',
                'created_by',
            ],
        ])
        ->assertJson([
            'success' => true,
            'company' => [
                'name' => 'New Company',
                'tax_id' => '12345678',
                'created_by' => $this->user->id,
            ],
        ]);

    expect(Company::where('tax_id', '12345678')->exists())->toBeTrue();
});

test('company is created with pending status', function (): void {
    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201);

    $company = Company::where('tax_id', '12345678')->first();
    expect($company->approval_status)->toBe('pending');
});

test('fails with missing required fields', function (): void {
    $response = postJson('/api/companies', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ],
        ]);
});

test('can create personal studio without tax_id', function (): void {
    $companyData = [
        'name' => 'Personal Studio',
        'is_personal' => true,
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'company' => [
                'name' => 'Personal Studio',
                'tax_id' => null,
                'is_personal' => true,
            ],
        ]);
});

test('fails with duplicate tax_id', function (): void {
    // Create existing company
    Company::create([
        'name' => 'Existing Company',
        'tax_id' => '12345678',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'tax_id',
            ],
        ]);
});

test('name cannot exceed 200 characters', function (): void {
    $companyData = [
        'name' => str_repeat('a', 201),
        'tax_id' => '12345678',
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'name',
            ],
        ]);
});

test('tax_id cannot exceed 50 characters', function (): void {
    $companyData = [
        'name' => 'Test Company',
        'tax_id' => str_repeat('1', 51),
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'tax_id',
            ],
        ]);
});

test('unauthenticated user cannot create company', function (): void {
    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
    ];

    $response = postJson('/api/companies', $companyData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});
