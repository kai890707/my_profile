<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
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
    ]);

    // Create industry
    $this->industry = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
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
        'industry_id' => $this->industry->id,
        'address' => '123 Test St',
        'phone' => '0912345678',
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'company' => [
                    'id',
                    'name',
                    'tax_id',
                    'industry_id',
                    'address',
                    'phone',
                    'approval_status',
                    'created_by',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => [
                'company' => [
                    'name' => 'New Company',
                    'tax_id' => '12345678',
                    'approval_status' => 'pending',
                    'created_by' => $this->user->id,
                ],
            ],
        ]);

    expect(Company::where('tax_id', '12345678')->exists())->toBeTrue();
});

test('company is created with pending status', function (): void {
    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
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
            'success',
            'message',
            'errors' => [
                'name',
                'tax_id',
                'industry_id',
            ],
        ]);
});

test('can create company without optional fields', function (): void {
    $companyData = [
        'name' => 'Minimal Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'company' => [
                    'address' => null,
                    'phone' => null,
                ],
            ],
        ]);
});

test('fails with duplicate tax_id', function (): void {
    // Create existing company
    Company::create([
        'name' => 'Existing Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
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

test('fails with invalid industry_id', function (): void {
    $companyData = [
        'name' => 'New Company',
        'tax_id' => '12345678',
        'industry_id' => 99999,
    ];

    $response = postJson('/api/companies', $companyData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'industry_id',
            ],
        ]);
});

test('name cannot exceed 200 characters', function (): void {
    $companyData = [
        'name' => str_repeat('a', 201),
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
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

test('tax_id cannot exceed 20 characters', function (): void {
    $companyData = [
        'name' => 'Test Company',
        'tax_id' => str_repeat('1', 21),
        'industry_id' => $this->industry->id,
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
        'industry_id' => $this->industry->id,
    ];

    $response = postJson('/api/companies', $companyData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});
