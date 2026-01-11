# 測試規格

**專案**: 前後端 API 不一致修復
**版本**: 1.0
**最後更新**: 2026-01-11

---

## 概述

本文檔定義所有測試案例，確保 API 端點、資料模型和業務規則的正確性。

**測試策略**:
- **Feature Tests**: API 端點完整流程測試（主要）
- **Unit Tests**: Model 方法和業務邏輯測試（輔助）
- **Integration Tests**: 前後端整合測試（手動）

**測試工具**:
- **Pest 3.x**: PHP 測試框架
- **Laravel Testing**: Feature Tests 和 Unit Tests
- **Database**: 使用 SQLite in-memory 測試資料庫

**測試覆蓋目標**:
- Experiences API: 12 個測試案例
- Certifications API: 15 個測試案例
- Approval Status API: 5 個測試案例
- Salesperson Status API: 4 個測試案例
- **Total**: 36+ 個新增測試案例

---

## Feature Tests

### 1. Experience API Tests

**測試檔案**: `tests/Feature/Api/ExperienceControllerTest.php`

---

#### Test 1.1: GET /salesperson/experiences - 業務員可成功取得經驗列表

**測試目標**: 驗證業務員可以取得自己的經驗列表

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立 3 筆經驗（屬於該使用者）
Experience::factory()->count(3)->create(['user_id' => $user->id]);

// 建立 2 筆經驗（屬於其他使用者）
Experience::factory()->count(2)->create();
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/experiences');
```

**Assertions**:
```php
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
```

---

#### Test 1.2: GET /salesperson/experiences - 未登入使用者無法取得經驗列表

**測試目標**: 驗證認證失敗

**Setup**: 無

**Request**:
```php
$response = $this->getJson('/api/salesperson/experiences');
```

**Assertions**:
```php
$response->assertStatus(401);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'UNAUTHORIZED');
```

---

#### Test 1.3: GET /salesperson/experiences - 一般使用者無法取得經驗列表

**測試目標**: 驗證授權失敗（非業務員）

**Setup**:
```php
$user = User::factory()->create(['role' => 'user']); // 一般使用者
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/experiences');
```

**Assertions**:
```php
$response->assertStatus(403);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'FORBIDDEN');
```

---

#### Test 1.4: POST /salesperson/experiences - 業務員可成功新增經驗

**測試目標**: 驗證新增經驗功能

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/experiences', [
        'company' => 'ABC Company',
        'position' => 'Sales Manager',
        'start_date' => '2020-01-01',
        'end_date' => '2022-12-31',
        'description' => 'Managed sales team...',
    ]);
```

**Assertions**:
```php
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
```

---

#### Test 1.5: POST /salesperson/experiences - end_date 早於 start_date 時驗證失敗

**測試目標**: 驗證日期驗證規則

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/experiences', [
        'company' => 'ABC Company',
        'position' => 'Sales Manager',
        'start_date' => '2022-01-01',
        'end_date' => '2020-12-31', // 早於 start_date
        'description' => 'Test',
    ]);
```

**Assertions**:
```php
$response->assertStatus(422);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'VALIDATION_ERROR');
$response->assertJsonPath('error.details.end_date.0',
    'The end date must be after or equal to the start date.');
```

---

#### Test 1.6: POST /salesperson/experiences - 必填欄位缺失時驗證失敗

**測試目標**: 驗證必填欄位

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/experiences', [
        // 缺少 company, position, start_date
        'description' => 'Test',
    ]);
```

**Assertions**:
```php
$response->assertStatus(422);
$response->assertJsonPath('success', false);
$response->assertJsonValidationErrors(['company', 'position', 'start_date']);
```

---

#### Test 1.7: PUT /salesperson/experiences/{id} - 業務員可成功更新自己的經驗

**測試目標**: 驗證更新經驗功能

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$experience = Experience::factory()->create([
    'user_id' => $user->id,
    'company' => 'Old Company',
]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->putJson("/api/salesperson/experiences/{$experience->id}", [
        'company' => 'New Company',
        'position' => 'Senior Sales Manager',
        'start_date' => '2020-01-01',
        'end_date' => '2023-12-31',
        'description' => 'Updated description',
    ]);
```

**Assertions**:
```php
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
```

---

#### Test 1.8: PUT /salesperson/experiences/{id} - 業務員無法更新其他人的經驗

**測試目標**: 驗證所有權檢查

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$otherUser = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$experience = Experience::factory()->create(['user_id' => $otherUser->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->putJson("/api/salesperson/experiences/{$experience->id}", [
        'company' => 'Hacked Company',
        'position' => 'Hacker',
        'start_date' => '2020-01-01',
    ]);
```

**Assertions**:
```php
$response->assertStatus(403);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'FORBIDDEN');
$response->assertJsonPath('error.message', 'You can only update your own experiences');

// 驗證資料庫未被修改
$this->assertDatabaseMissing('experiences', [
    'id' => $experience->id,
    'company' => 'Hacked Company',
]);
```

---

#### Test 1.9: PUT /salesperson/experiences/{id} - 經驗不存在時回傳 404

**測試目標**: 驗證 404 錯誤

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->putJson('/api/salesperson/experiences/99999', [
        'company' => 'Test',
        'position' => 'Test',
        'start_date' => '2020-01-01',
    ]);
```

**Assertions**:
```php
$response->assertStatus(404);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'NOT_FOUND');
```

---

#### Test 1.10: DELETE /salesperson/experiences/{id} - 業務員可成功刪除自己的經驗

**測試目標**: 驗證刪除經驗功能

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$experience = Experience::factory()->create(['user_id' => $user->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->deleteJson("/api/salesperson/experiences/{$experience->id}");
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('success', true);
$response->assertJsonPath('message', 'Experience deleted successfully');

// 驗證資料庫已刪除
$this->assertDatabaseMissing('experiences', [
    'id' => $experience->id,
]);
```

---

#### Test 1.11: DELETE /salesperson/experiences/{id} - 業務員無法刪除其他人的經驗

**測試目標**: 驗證所有權檢查

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$otherUser = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$experience = Experience::factory()->create(['user_id' => $otherUser->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->deleteJson("/api/salesperson/experiences/{$experience->id}");
```

**Assertions**:
```php
$response->assertStatus(403);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'FORBIDDEN');

// 驗證資料庫未被刪除
$this->assertDatabaseHas('experiences', [
    'id' => $experience->id,
]);
```

---

#### Test 1.12: GET /salesperson/experiences - 經驗列表按正確順序排序

**測試目標**: 驗證排序規則（sort_order ASC, start_date DESC）

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
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
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/experiences');
```

**Assertions**:
```php
$response->assertStatus(200);

$data = $response->json('data');

// 排序: sort_order=0 優先，然後按 start_date 降序
expect($data[0]['company'])->toBe('Company B'); // sort_order=0, start_date=2022
expect($data[1]['company'])->toBe('Company C'); // sort_order=0, start_date=2021
expect($data[2]['company'])->toBe('Company A'); // sort_order=1
```

---

### 2. Certification API Tests

**測試檔案**: `tests/Feature/Api/CertificationControllerTest.php`

---

#### Test 2.1: GET /salesperson/certifications - 業務員可成功取得證照列表

**測試目標**: 驗證業務員可以取得自己的證照列表

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立 2 筆證照（屬於該使用者）
Certification::factory()->count(2)->create(['user_id' => $user->id]);

// 建立 1 筆證照（屬於其他使用者）
Certification::factory()->create();
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/certifications');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonStructure([
    'success',
    'data' => [
        '*' => [
            'id',
            'user_id',
            'name',
            'issuer',
            'issue_date',
            'expiry_date',
            'description',
            'file_data',
            'file_mime',
            'file_size',
            'approval_status',
            'rejected_reason',
            'approved_by',
            'approved_at',
            'created_at',
            'updated_at',
        ],
    ],
    'message',
]);
$response->assertJsonPath('success', true);
$response->assertJsonCount(2, 'data'); // 只回傳 2 筆（自己的）
```

---

#### Test 2.2: GET /salesperson/certifications - file_data 永遠回傳 null

**測試目標**: 驗證 file_data 不回傳內容（效能考量）

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立有檔案的證照
Certification::factory()->withFile()->create(['user_id' => $user->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/certifications');
```

**Assertions**:
```php
$response->assertStatus(200);
$data = $response->json('data');

// file_data 應該是 null
expect($data[0]['file_data'])->toBeNull();

// file_mime 和 file_size 應該有值
expect($data[0]['file_mime'])->not->toBeNull();
expect($data[0]['file_size'])->not->toBeNull();
```

---

#### Test 2.3: GET /salesperson/certifications - 可篩選審核狀態

**測試目標**: 驗證 approval_status 篩選

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立不同審核狀態的證照
Certification::factory()->pending()->create(['user_id' => $user->id]);
Certification::factory()->approved()->create(['user_id' => $user->id]);
Certification::factory()->rejected()->create(['user_id' => $user->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/certifications?approval_status=approved');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonCount(1, 'data'); // 只有 1 筆 approved
$response->assertJsonPath('data.0.approval_status', 'approved');
```

---

#### Test 2.4: POST /salesperson/certifications - 業務員可成功上傳證照（含檔案）

**測試目標**: 驗證上傳證照功能（Base64 檔案）

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立假的 Base64 PDF
$pdfContent = 'JVBERi0xLjQKJeLjz9MK'; // 假 PDF Base64
$base64File = "data:application/pdf;base64,{$pdfContent}";
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/certifications', [
        'name' => 'PMP Certificate',
        'issuer' => 'PMI',
        'issue_date' => '2021-06-15',
        'expiry_date' => '2024-06-15',
        'description' => 'Project Management Professional',
        'file_data' => $base64File,
    ]);
```

**Assertions**:
```php
$response->assertStatus(201);
$response->assertJsonPath('success', true);
$response->assertJsonPath('data.name', 'PMP Certificate');
$response->assertJsonPath('data.approval_status', 'pending'); // 預設 pending
$response->assertJsonPath('data.file_mime', 'application/pdf');

// 驗證資料庫
$this->assertDatabaseHas('certifications', [
    'user_id' => $user->id,
    'name' => 'PMP Certificate',
    'file_mime' => 'application/pdf',
    'approval_status' => 'pending',
]);

// 驗證 file_data 已儲存（BLOB）
$cert = Certification::where('name', 'PMP Certificate')->first();
expect($cert->file_data)->not->toBeNull();
expect($cert->file_size)->toBeGreaterThan(0);
```

---

#### Test 2.5: POST /salesperson/certifications - 業務員可上傳證照（不含檔案）

**測試目標**: 驗證 file_data 為選填

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/certifications', [
        'name' => 'Google Analytics',
        'issuer' => 'Google',
        'issue_date' => '2022-03-10',
        // 不提供 file_data
    ]);
```

**Assertions**:
```php
$response->assertStatus(201);
$response->assertJsonPath('data.file_data', null);
$response->assertJsonPath('data.file_mime', null);
$response->assertJsonPath('data.file_size', null);

// 驗證資料庫
$this->assertDatabaseHas('certifications', [
    'user_id' => $user->id,
    'name' => 'Google Analytics',
    'file_data' => null,
]);
```

---

#### Test 2.6: POST /salesperson/certifications - 檔案大小超過 16MB 時驗證失敗

**測試目標**: 驗證檔案大小限制

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立超過 16MB 的 Base64 檔案（模擬）
$largeContent = str_repeat('A', 17 * 1024 * 1024); // 17MB
$base64Large = 'data:application/pdf;base64,' . base64_encode($largeContent);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/certifications', [
        'name' => 'Large Certificate',
        'issuer' => 'Test',
        'file_data' => $base64Large,
    ]);
```

**Assertions**:
```php
$response->assertStatus(422);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'VALIDATION_ERROR');
$response->assertJsonPath('error.details.file_data.0',
    'The file size must not exceed 16MB');
```

---

#### Test 2.7: POST /salesperson/certifications - 不支援的檔案類型時驗證失敗

**測試目標**: 驗證檔案類型限制

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// GIF 檔案（不支援）
$gifFile = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/certifications', [
        'name' => 'Test Certificate',
        'issuer' => 'Test',
        'file_data' => $gifFile,
    ]);
```

**Assertions**:
```php
$response->assertStatus(422);
$response->assertJsonPath('success', false);
$response->assertJsonValidationErrors(['file_data']);
```

---

#### Test 2.8: POST /salesperson/certifications - expiry_date 早於 issue_date 時驗證失敗

**測試目標**: 驗證日期驗證規則

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/certifications', [
        'name' => 'Test Certificate',
        'issuer' => 'Test',
        'issue_date' => '2022-01-01',
        'expiry_date' => '2020-12-31', // 早於 issue_date
    ]);
```

**Assertions**:
```php
$response->assertStatus(422);
$response->assertJsonValidationErrors(['expiry_date']);
```

---

#### Test 2.9: POST /salesperson/certifications - 必填欄位缺失時驗證失敗

**測試目標**: 驗證必填欄位

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->postJson('/api/salesperson/certifications', [
        // 缺少 name, issuer
        'description' => 'Test',
    ]);
```

**Assertions**:
```php
$response->assertStatus(422);
$response->assertJsonValidationErrors(['name', 'issuer']);
```

---

#### Test 2.10: DELETE /salesperson/certifications/{id} - 業務員可成功刪除自己的證照

**測試目標**: 驗證刪除證照功能

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$certification = Certification::factory()->create(['user_id' => $user->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->deleteJson("/api/salesperson/certifications/{$certification->id}");
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('success', true);

// 驗證資料庫已刪除
$this->assertDatabaseMissing('certifications', [
    'id' => $certification->id,
]);
```

---

#### Test 2.11: DELETE /salesperson/certifications/{id} - 業務員無法刪除其他人的證照

**測試目標**: 驗證所有權檢查

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$otherUser = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$certification = Certification::factory()->create(['user_id' => $otherUser->id]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->deleteJson("/api/salesperson/certifications/{$certification->id}");
```

**Assertions**:
```php
$response->assertStatus(403);
$response->assertJsonPath('success', false);
$response->assertJsonPath('error.code', 'FORBIDDEN');

// 驗證資料庫未被刪除
$this->assertDatabaseHas('certifications', [
    'id' => $certification->id,
]);
```

---

#### Test 2.12: GET /salesperson/certifications - 未登入使用者無法取得證照列表

**測試目標**: 驗證認證失敗

**Request**:
```php
$response = $this->getJson('/api/salesperson/certifications');
```

**Assertions**:
```php
$response->assertStatus(401);
$response->assertJsonPath('success', false);
```

---

#### Test 2.13: GET /salesperson/certifications - 一般使用者無法取得證照列表

**測試目標**: 驗證授權失敗

**Setup**:
```php
$user = User::factory()->create(['role' => 'user']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/certifications');
```

**Assertions**:
```php
$response->assertStatus(403);
$response->assertJsonPath('success', false);
```

---

#### Test 2.14: POST /salesperson/certifications - 未登入使用者無法上傳證照

**測試目標**: 驗證認證失敗

**Request**:
```php
$response = $this->postJson('/api/salesperson/certifications', [
    'name' => 'Test',
    'issuer' => 'Test',
]);
```

**Assertions**:
```php
$response->assertStatus(401);
```

---

#### Test 2.15: GET /salesperson/certifications - 證照列表按 created_at 降序排序

**測試目標**: 驗證排序規則

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立 3 筆證照，不同建立時間
$cert1 = Certification::factory()->create([
    'user_id' => $user->id,
    'name' => 'Cert A',
    'created_at' => now()->subDays(3),
]);
$cert2 = Certification::factory()->create([
    'user_id' => $user->id,
    'name' => 'Cert B',
    'created_at' => now()->subDays(1),
]);
$cert3 = Certification::factory()->create([
    'user_id' => $user->id,
    'name' => 'Cert C',
    'created_at' => now()->subDays(2),
]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/certifications');
```

**Assertions**:
```php
$response->assertStatus(200);

$data = $response->json('data');

// 排序: created_at 降序
expect($data[0]['name'])->toBe('Cert B'); // 最新
expect($data[1]['name'])->toBe('Cert C');
expect($data[2]['name'])->toBe('Cert A'); // 最舊
```

---

### 3. Approval Status API Tests

**測試檔案**: `tests/Feature/Api/ApprovalStatusControllerTest.php`

---

#### Test 3.1: GET /salesperson/approval-status - 業務員可成功取得聚合審核狀態

**測試目標**: 驗證聚合審核狀態功能

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

// 建立 Profile
$profile = SalespersonProfile::factory()->create([
    'user_id' => $user->id,
    'approval_status' => 'approved',
]);

// 建立 Company
$company = Company::factory()->create(['approval_status' => 'approved']);
$profile->update(['company_id' => $company->id]);

// 建立 Certifications
Certification::factory()->approved()->create([
    'user_id' => $user->id,
    'name' => 'Cert 1',
]);
Certification::factory()->pending()->create([
    'user_id' => $user->id,
    'name' => 'Cert 2',
]);

// 建立 Experiences
Experience::factory()->create([
    'user_id' => $user->id,
    'company' => 'Company A',
    'approval_status' => 'approved',
]);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/approval-status');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('success', true);
$response->assertJsonPath('data.profile_status', 'approved');
$response->assertJsonPath('data.company_status', 'approved');
$response->assertJsonCount(2, 'data.certifications');
$response->assertJsonCount(1, 'data.experiences');

// 驗證 certifications 包含必要欄位
$response->assertJsonStructure([
    'data' => [
        'certifications' => [
            '*' => ['id', 'name', 'approval_status', 'rejected_reason'],
        ],
        'experiences' => [
            '*' => ['id', 'company', 'position', 'approval_status', 'rejected_reason'],
        ],
    ],
]);
```

---

#### Test 3.2: GET /salesperson/approval-status - 無 Profile 時 profile_status 為 pending

**測試目標**: 驗證預設值

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);
// 不建立 profile
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/approval-status');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('data.profile_status', 'pending');
$response->assertJsonPath('data.company_status', null);
$response->assertJsonCount(0, 'data.certifications');
$response->assertJsonCount(0, 'data.experiences');
```

---

#### Test 3.3: GET /salesperson/approval-status - 未登入使用者無法取得審核狀態

**測試目標**: 驗證認證失敗

**Request**:
```php
$response = $this->getJson('/api/salesperson/approval-status');
```

**Assertions**:
```php
$response->assertStatus(401);
```

---

#### Test 3.4: GET /salesperson/approval-status - 一般使用者無法取得審核狀態

**測試目標**: 驗證授權失敗

**Setup**:
```php
$user = User::factory()->create(['role' => 'user']);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/approval-status');
```

**Assertions**:
```php
$response->assertStatus(403);
```

---

#### Test 3.5: GET /salesperson/approval-status - 無 N+1 查詢問題

**測試目標**: 驗證效能（使用 Eager Loading）

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);
$token = auth()->login($user);

$profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);
$company = Company::factory()->create();
$profile->update(['company_id' => $company->id]);

Certification::factory()->count(5)->create(['user_id' => $user->id]);
Experience::factory()->count(5)->create(['user_id' => $user->id]);
```

**Request**:
```php
// 啟用查詢日誌
DB::enableQueryLog();

$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/approval-status');

$queries = DB::getQueryLog();
```

**Assertions**:
```php
$response->assertStatus(200);

// 查詢數應該 <= 5（避免 N+1）
// 1. User 查詢
// 2. Profile 查詢（with company）
// 3. Certifications 查詢
// 4. Experiences 查詢
expect(count($queries))->toBeLessThanOrEqual(5);
```

---

### 4. Salesperson Status API Tests

**測試檔案**: `tests/Feature/Api/SalespersonStatusControllerTest.php`

---

#### Test 4.1: GET /salesperson/status - 業務員可成功取得狀態（包含新欄位）

**測試目標**: 驗證修正後的回應格式

**Setup**:
```php
$user = User::factory()->create([
    'role' => 'salesperson',
    'salesperson_status' => 'approved',
    'salesperson_applied_at' => now()->subDays(30),
    'salesperson_approved_at' => now()->subDays(25),
    'rejection_reason' => null,
    'can_reapply_at' => null,
]);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/status');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonStructure([
    'success',
    'data' => [
        'role',
        'salesperson_status',
        'salesperson_applied_at',
        'salesperson_approved_at',
        'rejection_reason',
        'can_reapply',
        'can_reapply_at',
        'days_until_reapply',
    ],
    'message',
]);

$response->assertJsonPath('success', true);
$response->assertJsonPath('data.role', 'salesperson');
$response->assertJsonPath('data.salesperson_status', 'approved');
$response->assertJsonPath('data.can_reapply', false);
$response->assertJsonPath('data.days_until_reapply', null);
```

---

#### Test 4.2: GET /salesperson/status - 一般使用者可取得狀態（role = user）

**測試目標**: 驗證一般使用者也可調用

**Setup**:
```php
$user = User::factory()->create([
    'role' => 'user',
    'salesperson_status' => null,
]);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/status');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('data.role', 'user');
$response->assertJsonPath('data.salesperson_status', null);
$response->assertJsonPath('data.can_reapply', false);
```

---

#### Test 4.3: GET /salesperson/status - 被拒絕的使用者可取得重新申請資訊

**測試目標**: 驗證 days_until_reapply 計算

**Setup**:
```php
$user = User::factory()->create([
    'role' => 'user',
    'salesperson_status' => 'rejected',
    'rejection_reason' => '資料不完整',
    'can_reapply_at' => now()->addDays(10),
]);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/status');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('data.salesperson_status', 'rejected');
$response->assertJsonPath('data.rejection_reason', '資料不完整');
$response->assertJsonPath('data.can_reapply', false); // 未到日期
$response->assertJsonPath('data.days_until_reapply', 10);
```

---

#### Test 4.4: GET /salesperson/status - can_reapply_at 已過期時 days_until_reapply 為負數

**測試目標**: 驗證過期日期計算

**Setup**:
```php
$user = User::factory()->create([
    'role' => 'user',
    'salesperson_status' => 'rejected',
    'can_reapply_at' => now()->subDays(5), // 5 天前
]);
$token = auth()->login($user);
```

**Request**:
```php
$response = $this->withHeader('Authorization', "Bearer $token")
    ->getJson('/api/salesperson/status');
```

**Assertions**:
```php
$response->assertStatus(200);
$response->assertJsonPath('data.can_reapply', true); // 已過期，可重新申請
$response->assertJsonPath('data.days_until_reapply', -5); // 負數
```

---

### 5. Additional Integration Tests

#### Test 5.1: 刪除使用者時級聯刪除 Experiences 和 Certifications

**測試目標**: 驗證級聯刪除

**Setup**:
```php
$user = User::factory()->create(['role' => 'salesperson']);

Experience::factory()->count(3)->create(['user_id' => $user->id]);
Certification::factory()->count(2)->create(['user_id' => $user->id]);
```

**Action**:
```php
$user->delete();
```

**Assertions**:
```php
// 驗證使用者已刪除
$this->assertDatabaseMissing('users', ['id' => $user->id]);

// 驗證 Experiences 已級聯刪除
$this->assertDatabaseMissing('experiences', ['user_id' => $user->id]);

// 驗證 Certifications 已級聯刪除
$this->assertDatabaseMissing('certifications', ['user_id' => $user->id]);
```

---

## Unit Tests

### 1. Experience Model Tests

**測試檔案**: `tests/Unit/Models/ExperienceTest.php`

---

#### Test U1.1: Experience Model - scopes 正常運作

**測試目標**: 驗證 Model Scopes

**Setup**:
```php
$user = User::factory()->create();

Experience::factory()->approved()->create(['user_id' => $user->id]);
Experience::factory()->pending()->create(['user_id' => $user->id]);
Experience::factory()->rejected()->create(['user_id' => $user->id]);
```

**Tests**:
```php
// approved scope
$approved = Experience::approved()->get();
expect($approved)->toHaveCount(1);
expect($approved[0]->approval_status)->toBe('approved');

// pending scope
$pending = Experience::pending()->get();
expect($pending)->toHaveCount(1);

// rejected scope
$rejected = Experience::rejected()->get();
expect($rejected)->toHaveCount(1);
```

---

### 2. Certification Model Tests

**測試檔案**: `tests/Unit/Models/CertificationTest.php`

---

#### Test U2.1: Certification Model - hasFile() 方法正常運作

**測試目標**: 驗證 hasFile() helper

**Setup**:
```php
$certWithFile = Certification::factory()->withFile()->create();
$certWithoutFile = Certification::factory()->create(['file_data' => null]);
```

**Tests**:
```php
expect($certWithFile->hasFile())->toBeTrue();
expect($certWithoutFile->hasFile())->toBeFalse();
```

---

#### Test U2.2: Certification Model - getFileExtension() 正常運作

**測試目標**: 驗證 getFileExtension() helper

**Setup**:
```php
$pdfCert = Certification::factory()->create(['file_mime' => 'application/pdf']);
$jpgCert = Certification::factory()->create(['file_mime' => 'image/jpeg']);
$noCert = Certification::factory()->create(['file_mime' => null]);
```

**Tests**:
```php
expect($pdfCert->getFileExtension())->toBe('pdf');
expect($jpgCert->getFileExtension())->toBe('jpg');
expect($noCert->getFileExtension())->toBeNull();
```

---

## 測試覆蓋統計

| 類型 | 數量 | 說明 |
|------|------|------|
| **Experience API Tests** | 12 | GET, POST, PUT, DELETE 完整測試 |
| **Certification API Tests** | 15 | GET, POST, DELETE 完整測試 + 檔案上傳測試 |
| **Approval Status API Tests** | 5 | 聚合狀態查詢測試 |
| **Salesperson Status API Tests** | 4 | 狀態查詢測試（含新欄位） |
| **Integration Tests** | 1 | 級聯刪除測試 |
| **Unit Tests** | 4 | Model Scopes 和 Helper 測試 |
| **總計** | **41** | 完整覆蓋所有功能 |

---

## 測試執行

### 執行所有測試

```bash
cd my_profile_laravel
docker exec -it my_profile_laravel_app composer test
```

### 執行特定測試檔案

```bash
# Experience API Tests
docker exec -it my_profile_laravel_app php artisan test --filter=ExperienceControllerTest

# Certification API Tests
docker exec -it my_profile_laravel_app php artisan test --filter=CertificationControllerTest
```

### 測試覆蓋率

```bash
docker exec -it my_profile_laravel_app composer test:coverage
```

**覆蓋率目標**:
- Experience API: 95%+
- Certification API: 95%+
- Approval Status API: 95%+
- Overall: 90%+

---

## 變更記錄

| 日期 | 版本 | 變更內容 |
|------|------|---------|
| 2026-01-11 | 1.0 | 初始版本，定義所有測試案例 |

---

**文檔狀態**: ✅ Complete
**審核狀態**: Pending Review
**總測試案例數**: 41
