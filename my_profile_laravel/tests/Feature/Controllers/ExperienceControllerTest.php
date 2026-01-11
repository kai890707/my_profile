<?php

declare(strict_types=1);

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test 1.1: GET /api/salesperson/experiences - 業務員可成功取得經驗列表
 */
test('authenticated salesperson can retrieve their experiences list', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    // 建立 3 筆經驗（屬於該使用者）
    Experience::factory()->count(3)->create(['user_id' => $user->id]);

    // 建立 2 筆經驗（屬於其他使用者）
    Experience::factory()->count(2)->create();

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/salesperson/experiences');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'id',
                'user_id',
                'company',
                'position',
                'start_date',
                'end_date',
                'description',
                'approval_status',
                'rejected_reason',
                'approved_by',
                'approved_at',
                'sort_order',
                'created_at',
                'updated_at',
            ],
        ],
        'message',
    ]);
    $response->assertJsonPath('success', true);
    $response->assertJsonCount(3, 'data'); // 只回傳 3 筆（自己的）
});

/**
 * Test 1.2: GET /api/salesperson/experiences - 未登入使用者無法取得經驗列表
 */
test('unauthenticated user cannot retrieve experiences list', function () {
    $response = $this->getJson('/api/salesperson/experiences');

    $response->assertStatus(401);
});

/**
 * Test 1.3: GET /api/salesperson/experiences - 一般使用者無法取得經驗列表
 */
test('regular user cannot retrieve experiences list', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]); // 一般使用者
    $token = auth()->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/salesperson/experiences');

    $response->assertStatus(403);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
});

/**
 * Test 1.4: POST /api/salesperson/experiences - 業務員可成功新增經驗
 */
test('salesperson can create new experience successfully', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/salesperson/experiences', [
            'company' => 'ABC Company',
            'position' => 'Sales Manager',
            'start_date' => '2020-01-01',
            'end_date' => '2022-12-31',
            'description' => 'Managed sales team...',
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.company', 'ABC Company');
    $response->assertJsonPath('data.position', 'Sales Manager');
    $response->assertJsonPath('data.approval_status', 'approved'); // 自動審核
    $response->assertJsonPath('data.user_id', $user->id); // 自動設定

    // 驗證資料庫
    $this->assertDatabaseHas('experiences', [
        'user_id' => $user->id,
        'company' => 'ABC Company',
        'position' => 'Sales Manager',
        'approval_status' => 'approved',
    ]);
});

/**
 * Test 1.5: POST /api/salesperson/experiences - end_date 早於 start_date 時驗證失敗
 */
test('experience creation fails when end_date is before start_date', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/salesperson/experiences', [
            'company' => 'ABC Company',
            'position' => 'Sales Manager',
            'start_date' => '2022-01-01',
            'end_date' => '2020-12-31', // 早於 start_date
            'description' => 'Test',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['end_date']);
});

/**
 * Test 1.6: POST /api/salesperson/experiences - 必填欄位缺失時驗證失敗
 */
test('experience creation fails when required fields are missing', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/salesperson/experiences', [
            // 缺少 company, position, start_date
            'description' => 'Test',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['company', 'position', 'start_date']);
});

/**
 * Test 1.7: PUT /api/salesperson/experiences/{id} - 業務員可成功更新自己的經驗
 */
test('salesperson can update their own experience', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $experience = Experience::factory()->create([
        'user_id' => $user->id,
        'company' => 'Old Company',
    ]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->putJson("/api/salesperson/experiences/{$experience->id}", [
            'company' => 'New Company',
            'position' => 'Senior Sales Manager',
            'start_date' => '2020-01-01',
            'end_date' => '2023-12-31',
            'description' => 'Updated description',
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.company', 'New Company');
    $response->assertJsonPath('data.position', 'Senior Sales Manager');

    // 驗證資料庫
    $this->assertDatabaseHas('experiences', [
        'id' => $experience->id,
        'company' => 'New Company',
        'position' => 'Senior Sales Manager',
    ]);
});

/**
 * Test 1.8: PUT /api/salesperson/experiences/{id} - 業務員無法更新其他人的經驗
 */
test('salesperson cannot update other users experience', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $otherUser = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $experience = Experience::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->putJson("/api/salesperson/experiences/{$experience->id}", [
            'company' => 'Hacked Company',
            'position' => 'Hacker',
            'start_date' => '2020-01-01',
        ]);

    $response->assertStatus(403);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'FORBIDDEN');
    $response->assertJsonPath('error.message', 'You can only update your own experiences');

    // 驗證資料庫未被修改
    $this->assertDatabaseMissing('experiences', [
        'id' => $experience->id,
        'company' => 'Hacked Company',
    ]);
});

/**
 * Test 1.9: PUT /api/salesperson/experiences/{id} - 經驗不存在時回傳 404
 */
test('updating non-existent experience returns 404', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->putJson('/api/salesperson/experiences/99999', [
            'company' => 'Test',
            'position' => 'Test',
            'start_date' => '2020-01-01',
        ]);

    $response->assertStatus(404);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'NOT_FOUND');
});

/**
 * Test 1.10: DELETE /api/salesperson/experiences/{id} - 業務員可成功刪除自己的經驗
 */
test('salesperson can delete their own experience', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $experience = Experience::factory()->create(['user_id' => $user->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->deleteJson("/api/salesperson/experiences/{$experience->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('message', 'Experience deleted successfully');

    // 驗證資料庫已刪除
    $this->assertDatabaseMissing('experiences', [
        'id' => $experience->id,
    ]);
});

/**
 * Test 1.11: DELETE /api/salesperson/experiences/{id} - 業務員無法刪除其他人的經驗
 */
test('salesperson cannot delete other users experience', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $otherUser = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    $experience = Experience::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->deleteJson("/api/salesperson/experiences/{$experience->id}");

    $response->assertStatus(403);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('error.code', 'FORBIDDEN');

    // 驗證資料庫未被刪除
    $this->assertDatabaseHas('experiences', [
        'id' => $experience->id,
    ]);
});

/**
 * Test 1.12: GET /api/salesperson/experiences - 經驗列表按正確順序排序
 */
test('experiences list is sorted correctly by sort_order and start_date', function () {
    $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    $token = auth()->login($user);

    // 建立 3 筆經驗，不同 sort_order 和 start_date
    Experience::factory()->create([
        'user_id' => $user->id,
        'company' => 'Company A',
        'start_date' => '2020-01-01',
        'sort_order' => 1,
    ]);
    Experience::factory()->create([
        'user_id' => $user->id,
        'company' => 'Company B',
        'start_date' => '2022-01-01',
        'sort_order' => 0,
    ]);
    Experience::factory()->create([
        'user_id' => $user->id,
        'company' => 'Company C',
        'start_date' => '2021-01-01',
        'sort_order' => 0,
    ]);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/salesperson/experiences');

    $response->assertStatus(200);

    $data = $response->json('data');

    // 排序: sort_order=0 優先，然後按 start_date 降序
    expect($data[0]['company'])->toBe('Company B'); // sort_order=0, start_date=2022
    expect($data[1]['company'])->toBe('Company C'); // sort_order=0, start_date=2021
    expect($data[2]['company'])->toBe('Company A'); // sort_order=1
});
