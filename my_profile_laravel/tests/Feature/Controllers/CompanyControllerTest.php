<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function approved_salesperson_can_create_registered_company(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/companies', [
                'name' => 'Test Company Ltd.',
                'tax_id' => '12345678',
                'is_personal' => false,
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company Ltd.',
            'tax_id' => '12345678',
            'is_personal' => false,
            'created_by' => $user->id,
        ]);
    }

    /** @test */
    public function approved_salesperson_can_create_personal_studio(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/companies', [
                'name' => 'John Personal Studio',
                'is_personal' => true,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('companies', [
            'name' => 'John Personal Studio',
            'tax_id' => null,
            'is_personal' => true,
        ]);
    }

    /** @test */
    public function registered_company_requires_tax_id(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/companies', [
                'name' => 'Test Company',
                'is_personal' => false,
                // Missing tax_id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tax_id']);
    }

    /** @test */
    public function pending_salesperson_cannot_create_company(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/companies', [
                'name' => 'Test Company',
                'tax_id' => '12345678',
                'is_personal' => false,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_create_company(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/companies', [
                'name' => 'Test Company',
                'tax_id' => '12345678',
                'is_personal' => false,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_search_company_by_tax_id(): void
    {
        Company::factory()->create([
            'name' => 'Existing Company',
            'tax_id' => '12345678',
            'is_personal' => false,
        ]);

        $response = $this->getJson('/api/companies/search?tax_id=12345678');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'exists' => true,
            ]);

        $this->assertCount(1, $response->json('companies'));
    }

    /** @test */
    public function it_can_search_company_by_name(): void
    {
        Company::factory()->create([
            'name' => 'ABC Insurance Company',
            'tax_id' => '12345678',
            'is_personal' => false,
        ]);

        $response = $this->getJson('/api/companies/search?name=ABC');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'exists' => true,
            ]);
    }

    /** @test */
    public function search_returns_empty_when_no_match(): void
    {
        $response = $this->getJson('/api/companies/search?tax_id=99999999');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'exists' => false,
                'companies' => [],
            ]);
    }

    /** @test */
    public function it_prevents_duplicate_tax_id(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        $token = auth()->login($user);

        Company::factory()->create([
            'tax_id' => '12345678',
            'is_personal' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/companies', [
                'name' => 'Another Company',
                'tax_id' => '12345678',
                'is_personal' => false,
            ]);

        $response->assertStatus(422);
    }
}
