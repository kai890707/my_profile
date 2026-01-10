<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\SalespersonProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalespersonControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_user_can_upgrade_to_salesperson(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/salesperson/upgrade', [
                'full_name' => 'John Doe',
                'phone' => '0912345678',
                'bio' => 'Experienced salesperson',
                'specialties' => 'Insurance',
                'service_regions' => ['台北市'],
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals(User::ROLE_SALESPERSON, $user->role);
        $this->assertEquals(User::STATUS_PENDING, $user->salesperson_status);
        $this->assertDatabaseHas('salesperson_profiles', [
            'user_id' => $user->id,
            'full_name' => 'John Doe',
        ]);
    }

    /** @test */
    public function salesperson_cannot_upgrade_again(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/salesperson/upgrade', [
                'full_name' => 'John Doe',
                'phone' => '0912345678',
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_upgrade_request(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/salesperson/upgrade', [
                'full_name' => '',
                'phone' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'phone']);
    }

    /** @test */
    public function it_can_get_salesperson_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/salesperson/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_salesperson' => true,
                'status' => User::STATUS_PENDING,
            ]);
    }

    /** @test */
    public function approved_salesperson_can_update_profile(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        $profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/salesperson/profile', [
                'full_name' => 'Updated Name',
                'phone' => '0987654321',
                'bio' => 'Updated bio',
            ]);

        $response->assertStatus(200);

        $profile->refresh();
        $this->assertEquals('Updated Name', $profile->full_name);
        $this->assertEquals('0987654321', $profile->phone);
    }

    /** @test */
    public function it_can_list_approved_salespeople_publicly(): void
    {
        // Create approved salespeople
        $approved1 = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $approved1->id]);

        $approved2 = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $approved2->id]);

        // Create pending salesperson (should not appear)
        $pending = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $pending->id]);

        $response = $this->getJson('/api/salespeople');

        $response->assertStatus(200);

        // Paginated response has data nested in data.data
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }
}
