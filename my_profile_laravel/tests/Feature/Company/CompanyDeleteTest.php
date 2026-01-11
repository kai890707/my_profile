<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;

use function Pest\Laravel\deleteJson;
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

    // Create another user
    $this->otherUser = User::create([
        'username' => 'otheruser',
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Create company for user
    $this->company = Company::create([
        'name' => 'Test Company',
        'tax_id' => '12345678',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can delete their own company', function (): void {
    $response = deleteJson("/api/companies/{$this->company->id}", [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);

    expect(Company::find($this->company->id))->toBeNull();
});

test('cannot delete other users company', function (): void {
    $otherCompany = Company::create([
        'name' => 'Other Company',
        'tax_id' => '87654321',
        'approval_status' => 'approved',
        'created_by' => $this->otherUser->id,
    ]);

    $response = deleteJson("/api/companies/{$otherCompany->id}", [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden - You can only delete your own companies',
        ]);

    expect(Company::find($otherCompany->id))->not->toBeNull();
});

test('fails when company does not exist', function (): void {
    $response = deleteJson('/api/companies/99999', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Company not found',
        ]);
});

test('unauthenticated user cannot delete company', function (): void {
    $response = deleteJson("/api/companies/{$this->company->id}");

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);

    expect(Company::find($this->company->id))->not->toBeNull();
});

test('can delete pending company', function (): void {
    $pendingCompany = Company::create([
        'name' => 'Pending Company',
        'tax_id' => '87654321',
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    $response = deleteJson("/api/companies/{$pendingCompany->id}", [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);
    expect(Company::find($pendingCompany->id))->toBeNull();
});

test('can delete rejected company', function (): void {
    $rejectedCompany = Company::create([
        'name' => 'Rejected Company',
        'tax_id' => '87654321',
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid data',
        'created_by' => $this->user->id,
    ]);

    $response = deleteJson("/api/companies/{$rejectedCompany->id}", [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);
    expect(Company::find($rejectedCompany->id))->toBeNull();
});
