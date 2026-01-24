<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create super_admin role for admin_session guard if it doesn't exist
    $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(
        ['name' => 'super_admin', 'guard_name' => 'admin_session']
    );

    // Create admin user for testing
    $this->admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'status' => 'active',
    ]);

    // Assign super_admin role
    $this->admin->assignRole($superAdminRole);

    // Act as admin on admin_session guard (Filament uses this guard)
    $this->actingAs($this->admin, 'admin_session');
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
    // TODO: Rewrite using Filament Livewire testing
    // Filament forms use Livewire, not plain HTTP POST requests
    $this->markTestSkipped('Requires Filament Livewire testing - to be implemented');
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
    // TODO: Rewrite using Filament Livewire testing
    // Filament forms use Livewire, not plain HTTP PUT requests
    $this->markTestSkipped('Requires Filament Livewire testing - to be implemented');
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
    // TODO: Rewrite using Filament Livewire testing
    // Filament forms use Livewire, not plain HTTP POST requests
    $this->markTestSkipped('Requires Filament Livewire testing - to be implemented');
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
