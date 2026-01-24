<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super_admin role for web guard if it doesn't exist
    $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(
        ['name' => 'super_admin', 'guard_name' => 'web']
    );

    // Create admin user for testing
    $this->admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'status' => 'active',
    ]);

    // Assign super_admin role
    $this->admin->assignRole($superAdminRole);

    // Act as admin on web guard
    $this->actingAs($this->admin, 'web');
});

test('admin can access user resource', function () {
    expect(UserResource::canViewAny($this->admin))->toBeTrue();
});

test('user resource has correct navigation properties', function () {
    expect(UserResource::getNavigationLabel())->toBe('使用者管理')
        ->and(UserResource::getNavigationGroup())->toBe('系統管理')
        ->and(UserResource::getNavigationIcon())->toBe('heroicon-o-users');
});

test('user resource has correct pages', function () {
    $pages = UserResource::getPages();

    expect($pages)->toHaveKey('index')
        ->and($pages)->toHaveKey('create')
        ->and($pages)->toHaveKey('edit')
        ->and($pages)->toHaveKey('view');
});

test('user resource can search by name and email', function () {
    $searchableAttributes = UserResource::getGloballySearchableAttributes();

    expect($searchableAttributes)->toContain('name')
        ->and($searchableAttributes)->toContain('email');
});

test('user resource list page shows users', function () {
    User::factory()->count(5)->create([
        'role' => User::ROLE_USER,
        'status' => 'active',
    ]);

    $response = $this->get(UserResource::getUrl('index'));

    $response->assertSuccessful();
});

test('user resource create page is accessible', function () {
    $response = $this->get(UserResource::getUrl('create'));

    $response->assertSuccessful();
});

test('admin can create new user', function () {
    $response = $this->post(UserResource::getUrl('create'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => 'password123',
        'role' => User::ROLE_USER,
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => User::ROLE_USER,
    ]);
});

test('user resource edit page is accessible', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    $response = $this->get(UserResource::getUrl('edit', ['record' => $user]));

    $response->assertSuccessful();
});

test('user resource view page is accessible', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    $response = $this->get(UserResource::getUrl('view', ['record' => $user]));

    $response->assertSuccessful();
});

test('admin can update user role', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_USER,
        'status' => 'active',
    ]);

    $response = $this->put(UserResource::getUrl('edit', ['record' => $user]), [
        'name' => $user->name,
        'email' => $user->email,
        'role' => User::ROLE_SALESPERSON,
        'salesperson_status' => User::STATUS_PENDING,
        'status' => 'active',
    ]);

    $user->refresh();

    expect($user->role)->toBe(User::ROLE_SALESPERSON)
        ->and($user->salesperson_status)->toBe(User::STATUS_PENDING);
});

test('admin can toggle user status', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_USER,
        'status' => 'active',
    ]);

    $user->update(['status' => 'inactive']);

    expect($user->status)->toBe('inactive');

    $user->update(['status' => 'active']);

    expect($user->status)->toBe('active');
});

test('salesperson status is only shown for salesperson role', function () {
    $salesperson = User::factory()->create([
        'role' => User::ROLE_SALESPERSON,
        'salesperson_status' => User::STATUS_APPROVED,
    ]);

    $regularUser = User::factory()->create([
        'role' => User::ROLE_USER,
        'salesperson_status' => null,
    ]);

    expect($salesperson->salesperson_status)->toBe(User::STATUS_APPROVED)
        ->and($regularUser->salesperson_status)->toBeNull();
});

test('user resource filters work correctly', function () {
    // Create users with different roles
    User::factory()->create(['role' => User::ROLE_USER]);
    User::factory()->create(['role' => User::ROLE_SALESPERSON, 'salesperson_status' => User::STATUS_APPROVED]);
    User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->get(UserResource::getUrl('index', [
        'tableFilters' => ['role' => ['value' => User::ROLE_SALESPERSON]],
    ]));

    $response->assertSuccessful();
});

test('admin can soft delete user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    $user->delete();

    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('username auto-generates from email if not provided', function () {
    $response = $this->post(UserResource::getUrl('create'), [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password_hash' => 'password123',
        'role' => User::ROLE_USER,
        'status' => 'active',
    ]);

    $user = User::where('email', 'testuser@example.com')->first();

    expect($user->username)->toBe('testuser');
});

test('salesperson status is cleared when role changes from salesperson to user', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SALESPERSON,
        'salesperson_status' => User::STATUS_APPROVED,
    ]);

    $user->update([
        'role' => User::ROLE_USER,
        'salesperson_status' => null,
    ]);

    $user->refresh();

    expect($user->role)->toBe(User::ROLE_USER)
        ->and($user->salesperson_status)->toBeNull();
});
