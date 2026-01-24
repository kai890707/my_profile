<?php

declare(strict_types=1);

use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\DailyAnalytics;
use App\Models\SalespersonProfile;
use App\Models\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    // Create test users
    $this->salesperson = User::factory()->create([
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    $this->admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    // Create salesperson profile
    $this->profile = SalespersonProfile::factory()->create([
        'user_id' => $this->salesperson->id,
    ]);

    // Generate tokens
    $this->salespersonToken = JWTAuth::fromUser($this->salesperson);
    $this->adminToken = JWTAuth::fromUser($this->admin);
});

describe('Salesperson Analytics', function () {
    test('GET /api/salesperson/analytics/stats returns stats', function () {
        $today = Carbon::today();

        // Create test data
        DailyAnalytics::factory()->create([
            'salesperson_id' => $this->salesperson->id,
            'date' => $today->copy()->subDays(3)->toDateString(),
            'profile_views_count' => 10,
            'contact_requests_count' => 2,
            'unique_visitors_count' => 8,
        ]);

        ContactEvent::factory()->count(5)->create([
            'salesperson_id' => $this->salesperson->id,
            'event_type' => 'profile_view',
            'created_at' => $today,
        ]);

        $response = $this->withToken($this->salespersonToken)
            ->getJson('/api/salesperson/analytics/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profile_views',
                    'contact_requests',
                    'unique_visitors',
                    'conversion_rate',
                    'previous_period',
                    'growth',
                    'range',
                ],
            ]);
    });

    test('GET /api/salesperson/analytics/trends returns daily trends', function () {
        for ($i = 0; $i < 7; $i++) {
            DailyAnalytics::factory()->create([
                'salesperson_id' => $this->salesperson->id,
                'date' => Carbon::today()->subDays($i)->toDateString(),
            ]);
        }

        $response = $this->withToken($this->salespersonToken)
            ->getJson('/api/salesperson/analytics/trends');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'trends',
                    'range',
                ],
            ]);

        expect(count($response->json('data.trends')))->toBe(7);
    });

    test('GET /api/salesperson/analytics/recent-contacts returns contacts', function () {
        ContactRequest::factory()->count(5)->create([
            'salesperson_id' => $this->salesperson->id,
        ]);

        $response = $this->withToken($this->salespersonToken)
            ->getJson('/api/salesperson/analytics/recent-contacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });
});

describe('Admin Analytics', function () {
    test('GET /api/admin/analytics/overview returns platform overview', function () {
        User::factory()->count(5)->create(['role' => 'salesperson']);

        $response = $this->withToken($this->adminToken)
            ->getJson('/api/admin/analytics/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_salespersons',
                    'total_profile_views',
                    'total_contact_requests',
                    'total_unique_visitors',
                    'platform_conversion_rate',
                    'active_salespersons',
                    'inactive_salespersons',
                    'activity_rate',
                    'previous_period',
                    'growth',
                    'range',
                ],
            ]);
    });

    test('GET /api/admin/analytics/top-salespersons returns top performers', function () {
        $response = $this->withToken($this->adminToken)
            ->getJson('/api/admin/analytics/top-salespersons');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    test('GET /api/admin/analytics/activity returns recent activity', function () {
        $response = $this->withToken($this->adminToken)
            ->getJson('/api/admin/analytics/activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    test('GET /api/admin/analytics/growth returns growth trends', function () {
        for ($i = 0; $i < 7; $i++) {
            DailyAnalytics::factory()->create([
                'date' => Carbon::today()->subDays($i)->toDateString(),
            ]);
        }

        $response = $this->withToken($this->adminToken)
            ->getJson('/api/admin/analytics/growth');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'trends',
                    'range',
                ],
            ]);
    });

    test('admin endpoints reject non-admin users', function () {
        $response = $this->withToken($this->salespersonToken)
            ->getJson('/api/admin/analytics/overview');

        $response->assertStatus(403);
    });
});

describe('Authentication', function () {
    test('endpoints require authentication', function () {
        $response = $this->getJson('/api/salesperson/analytics/stats');
        $response->assertStatus(401);

        $response = $this->getJson('/api/admin/analytics/overview');
        $response->assertStatus(401);
    });
});
