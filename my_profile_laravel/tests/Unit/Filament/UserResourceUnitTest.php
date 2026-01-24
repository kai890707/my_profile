<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;

test('user resource has correct model', function () {
    expect(UserResource::getModel())->toBe(User::class);
});

test('user resource has correct navigation properties', function () {
    expect(UserResource::getNavigationLabel())->toBe('使用者管理')
        ->and(UserResource::getNavigationGroup())->toBe('系統管理')
        ->and(UserResource::getNavigationIcon())->toBe('heroicon-o-users')
        ->and(UserResource::getNavigationSort())->toBe(10);
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
        ->and($searchableAttributes)->toContain('email')
        ->and($searchableAttributes)->toHaveCount(2);
});

test('user resource has form method', function () {
    expect(method_exists(UserResource::class, 'form'))->toBeTrue();
});

test('user resource has table method', function () {
    expect(method_exists(UserResource::class, 'table'))->toBeTrue();
});

test('user resource has infolist method', function () {
    expect(method_exists(UserResource::class, 'infolist'))->toBeTrue();
});

test('user resource slug is correct', function () {
    expect(UserResource::getSlug())->toBe('users');
});
