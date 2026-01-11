# Fix Summary: API 401 Unauthorized Error

**日期**: 2026-01-11
**問題**: `GET /api/salesperson/profile` 返回 401 Unauthorized
**狀態**: ✅ 已修復並驗證

---

## 修復摘要

### 根本原因

Backend Middleware (`JwtAuthMiddleware`) 使用了不標準的方式將認證用戶注入到 Request 中：

```php
// ❌ 錯誤方式
$request->merge(['auth_user' => $authenticatedUser]);
```

而 Controller 嘗試使用此方式獲取用戶：

```php
// ❌ 不標準
$user = $request->get('auth_user');
```

在 Laravel 11 中，`$request->merge()` 不保證在後續的 middleware 和 controller 中能正確獲取自定義屬性，導致 `auth_user` 為 `null`，進而返回 401 錯誤。

---

## 修復方案

### 1. 更新 Middleware

**檔案**: `my_profile_laravel/app/Http/Middleware/JwtAuthMiddleware.php`

**修改內容**:
```php
// ✅ 使用 Laravel 標準方式
// Set authenticated user on the request (Laravel standard way)
$request->setUserResolver(function () use ($authenticatedUser) {
    return $authenticatedUser;
});

// Also set on auth guard for consistency
auth()->setUser($authenticatedUser);
```

**變更說明**:
- 使用 `$request->setUserResolver()` 設定用戶解析器
- 同時使用 `auth()->setUser()` 設定到認證 guard
- 這是 Laravel 框架的標準做法

### 2. 更新所有 Controllers

**修改的檔案**:
1. `app/Http/Controllers/Api/SalespersonProfileController.php`
   - `me()` 方法
   - `store()` 方法
   - `update()` 方法
   - `destroy()` 方法

2. `app/Http/Controllers/Api/CompanyController.php`
   - `myCompanies()` 方法
   - `update()` 方法
   - `destroy()` 方法

**修改內容**:
```php
// ❌ 舊方式
$user = $request->get('auth_user');

// ✅ 新方式 (Laravel 標準)
$user = $request->user();
```

### 3. 更新其他 Middleware

**檔案**: `app/Http/Middleware/RoleMiddleware.php`

**修改內容**:
```php
// ❌ 舊方式
$user = $request->get('auth_user');

// ✅ 新方式
$user = $request->user();
```

---

## 測試驗證

### Test 1: API 直接測試（無 Profile）

**測試命令**:
```bash
curl -X GET http://localhost:8080/api/salesperson/profile \
  -H "Authorization: Bearer {token}"
```

**預期結果**:
```json
{
  "success": false,
  "message": "Profile not found"
}
HTTP Status: 404
```

**測試結果**: ✅ 通過

**說明**:
- Middleware 正確認證用戶
- Controller 正確獲取認證用戶
- 返回 404（而非 401）因為用戶沒有 profile

### Test 2: API 直接測試（有 Profile）

**準備**:
```bash
# 創建 salesperson_profile
docker exec my_profile_laravel_app php artisan tinker --execute="
App\Models\SalespersonProfile::create([
    'user_id' => 4,
    'full_name' => 'Test User',
    'phone' => '0912345678',
    'bio' => 'Test profile',
]);
"
```

**測試命令**:
```bash
curl -X GET http://localhost:8080/api/salesperson/profile \
  -H "Authorization: Bearer {token}"
```

**預期結果**:
```json
{
  "success": true,
  "data": {
    "profile": {
      "id": 4,
      "user_id": 4,
      "full_name": "Test User",
      "phone": "0912345678",
      ...
    }
  }
}
HTTP Status: 200
```

**測試結果**: ✅ 通過

### Test 3: 完整登入流程測試（Playwright）

**測試腳本**: `/tmp/playwright-test-login-flow.js`

**測試步驟**:
1. 訪問登入頁面
2. 填寫測試帳號密碼
3. 提交登入表單
4. 檢查 localStorage tokens
5. 測試 API 端點

**測試結果**:
```
✅ Login successful
✅ Tokens saved to localStorage
✅ Access Token: valid and not expired
✅ Refresh Token: saved
✅ User ID: 4, Role: user
```

---

## 修復影響範圍

### 修改的檔案

1. **Middleware** (1 個檔案)
   - `app/Http/Middleware/JwtAuthMiddleware.php` - 核心修復

2. **Controllers** (2 個檔案)
   - `app/Http/Controllers/Api/SalespersonProfileController.php` - 4 個方法
   - `app/Http/Controllers/Api/CompanyController.php` - 3 個方法

3. **Other Middleware** (1 個檔案)
   - `app/Http/Middleware/RoleMiddleware.php` - 1 個方法

### 總計修改

- **檔案數**: 4 個 PHP 檔案
- **方法數**: 9 個方法
- **代碼行數**: ~20 行修改

---

## 技術細節

### Laravel Request User Resolution

Laravel 提供了標準的用戶解析機制：

```php
// 設定用戶解析器
$request->setUserResolver(function () use ($user) {
    return $user;
});

// 後續可通過以下方式獲取
$user = $request->user();      // 推薦
$user = auth()->user();         // 也可以
```

### 為什麼 `$request->merge()` 不可靠？

`$request->merge()` 主要用於合併請求參數（query params, form data），而非設定自定義屬性：

```php
// ❌ 不適合用於設定用戶
$request->merge(['auth_user' => $user]);

// ✅ 應該用於合併參數
$request->merge(['extra_param' => 'value']);
```

在 Laravel 11 中，Request 對象可能在 middleware 執行過程中被克隆或重建，導致通過 `merge()` 設定的自定義屬性丟失。

### Laravel 標準認證流程

```
1. Middleware 驗證 Token
   ↓
2. 使用 setUserResolver() 設定用戶
   ↓
3. Controller 使用 $request->user() 獲取
   ↓
4. 框架自動管理用戶狀態
```

---

## 預防措施

### 1. 使用 Laravel 標準 API

- ✅ 使用 `$request->user()` 而非自定義屬性
- ✅ 使用 `auth()->user()` 獲取認證用戶
- ✅ 使用 `$request->setUserResolver()` 設定用戶

### 2. Code Review 檢查點

在 Code Review 時，檢查：
- [ ] Middleware 是否使用標準方式設定用戶
- [ ] Controller 是否使用 `$request->user()`
- [ ] 是否避免使用 `$request->merge()` 設定用戶

### 3. 單元測試

為 Middleware 添加測試：

```php
test('middleware sets authenticated user correctly', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/protected-endpoint');

    expect(auth()->user())->toBeInstanceOf(User::class);
    expect(auth()->id())->toBe($user->id);
});
```

---

## 學習點

### 1. 遵循框架慣例

Laravel 提供了完善的認證機制，應該：
- 使用框架提供的標準 API
- 避免自創不標準的實現方式
- 閱讀官方文檔了解最佳實踐

### 2. Request 生命週期理解

- Request 對象在 middleware 鏈中傳遞
- 某些操作可能導致 Request 被克隆
- 使用標準 API 確保狀態正確傳遞

### 3. 診斷方法論

本次診斷使用的系統化方法：
1. ✅ 使用自動化工具（Playwright）測試完整流程
2. ✅ 使用 curl 測試 API 端點
3. ✅ 添加日誌確認執行流程
4. ✅ 檢查資料庫記錄驗證數據
5. ✅ 閱讀框架文檔找到標準做法

---

## 後續建議

### 1. 添加測試覆蓋

為受影響的 API 端點添加集成測試：

```php
test('authenticated user can access profile', function () {
    $user = User::factory()->create();
    $profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/salesperson/profile');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['profile' => ['id' => $profile->id]],
        ]);
});
```

### 2. 更新文檔

在 Backend CLAUDE.md 中添加認證最佳實踐：

```markdown
## 認證最佳實踐

### Middleware 中設定認證用戶

```php
// ✅ 正確
$request->setUserResolver(fn() => $user);
auth()->setUser($user);

// ❌ 錯誤
$request->merge(['auth_user' => $user]);
```

### Controller 中獲取認證用戶

```php
// ✅ 正確
$user = $request->user();
$user = auth()->user();

// ❌ 錯誤
$user = $request->get('auth_user');
```
```

### 3. Code Review Checklist

添加到 PR review checklist:
- [ ] Middleware 使用標準方式設定用戶
- [ ] Controller 使用 `$request->user()`
- [ ] 有對應的測試覆蓋

---

## 結論

### 修復成果

- ✅ **問題已解決**: API 不再返回 401 錯誤
- ✅ **測試通過**: 完整登入流程正常
- ✅ **代碼品質**: 符合 Laravel 標準實踐
- ✅ **可維護性**: 使用框架標準 API

### 技術收穫

1. **理解 Laravel Request 生命週期**
2. **掌握標準認證流程**
3. **學會系統化診斷方法**
4. **提升代碼品質意識**

### 影響評估

- **業務影響**: 業務員可正常使用 Dashboard
- **技術影響**: 代碼更標準、更可維護
- **測試影響**: 需要添加對應測試覆蓋

---

**修復執行者**: Claude Sonnet 4.5
**修復完成時間**: 2026-01-11
**總耗時**: ~2 小時（診斷 + 修復 + 驗證）

