<?php

declare(strict_types=1);

use App\Models\Company;
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

    // Create approved company
    $this->company = Company::create([
        'name' => 'Test Company',
        'tax_id' => '12345678',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);
});

test('can get single company by id', function (): void {
    $response = getJson("/api/companies/{$this->company->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'company' => [
                    'id',
                    'name',
                    'tax_id',
                    'is_personal',
                    'approval_status',
                    'created_by',
                    'created_at',
                    'updated_at',
                    'creator',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'company' => [
                    'id' => $this->company->id,
                    'name' => 'Test Company',
                    'tax_id' => '12345678',
                ],
            ],
        ]);
});

test('company includes creator relationship', function (): void {
    $response = getJson("/api/companies/{$this->company->id}");

    $response->assertStatus(200);

    $company = $response->json('data.company');
    expect($company['creator'])->not->toBeNull();
    expect($company['creator']['email'])->toBe('test@example.com');
});

test('returns 404 for non-existent company', function (): void {
    $response = getJson('/api/companies/99999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Company not found',
        ]);
});

test('can access pending company by id', function (): void {
    $pendingCompany = Company::create([
        'name' => 'Pending Company',
        'tax_id' => '87654321',
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    $response = getJson("/api/companies/{$pendingCompany->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'company' => [
                    'id' => $pendingCompany->id,
                    'approval_status' => 'pending',
                ],
            ],
        ]);
});

test('can access rejected company by id', function (): void {
    $rejectedCompany = Company::create([
        'name' => 'Rejected Company',
        'tax_id' => '87654321',
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid tax ID',
        'created_by' => $this->user->id,
    ]);

    $response = getJson("/api/companies/{$rejectedCompany->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'company' => [
                    'id' => $rejectedCompany->id,
                    'approval_status' => 'rejected',
                ],
            ],
        ]);
});
