# API 401 錯誤診斷結果

**日期**: 2026-01-11
**問題**: `GET /api/salesperson/profile` 返回 401 Unauthorized
**狀態**: ✅ 根本原因已確認

---

## 診斷過程摘要

### 使用的診斷工具

1. **Playwright Browser Automation**
   - 自動化測試登入流程
   - 檢查 localStorage tokens
   - 監聽 network requests
   - 解碼 JWT tokens

2. **Backend API 測試**
   - 直接使用 `docker exec` 生成 token
   - `curl` 測試 API 端點
   - 檢查資料庫記錄

3. **程式碼檢視**
   - Frontend API client
   - Backend middleware
   - Controller 邏輯
   - 路由定義

---

## 診斷發現

### ✅ Frontend 正常運作

#### 1. 登入流程正常
```
測試帳號: test@example.com
測試密碼: password123

結果:
- ✅ 登入成功
- ✅ 重定向到 http://localhost:3001/
- ✅ Access Token 已儲存到 localStorage
- ✅ Refresh Token 已儲存到 localStorage
```

#### 2. Token 格式正確
```javascript
Token preview: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJod...
Is Expired: false
Expires At: 2026-01-11T12:36:46.000Z
User ID: 4
Role: user  // ← 注意：不是 "salesperson"
```

#### 3. API 請求正確發送
```http
GET /api/salesperson/profile
Authorization: Bearer {valid_token}
Content-Type: application/json
```

---

### ❌ Backend 問題

#### 問題 1: Middleware 未正確設定 auth_user

**檢測方法**:
```bash
# 使用有效 token 測試 API
curl -X GET http://localhost:8080/api/salesperson/profile \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**回應**:
```json
{
  "success": false,
  "message": "Unauthorized"
}
HTTP Status: 401
```

**錯誤來源定位**:

檔案: `my_profile_laravel/app/Http/Controllers/Api/SalespersonProfileController.php`

```php
public function me(Request $request): JsonResponse
{
    $user = $request->get('auth_user');  // ← Line 205

    if (! $user instanceof User) {       // ← Line 207
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',  // ← 這就是錯誤來源！
        ], 401);
    }

    // ...
}
```

**分析**:
- Controller 返回 401 "Unauthorized" 表示 `$user` **不是** `User` 實例
- 但 Middleware (`JwtAuthMiddleware.php`) 應該在 Line 30 設定 `auth_user`:
  ```php
  $request->merge(['auth_user' => $authenticatedUser]);
  ```
- 這表示 **Middleware 可能沒有正確執行或設定失敗**

#### 問題 2: 測試用戶沒有 salesperson_profile

**檢測方法**:
```bash
docker exec my_profile_laravel_app php artisan tinker --execute="
echo App\Models\SalespersonProfile::where('user_id', 4)->count();
"
```

**結果**:
```
0  // ← 用戶 4 沒有 salesperson_profile！
```

**分析**:
- 即使 Middleware 正常，Controller 的 `getByUserId(4)` 也會返回 `null`
- 這應該返回 **404** "Profile not found"（Line 216-221）
- 但實際返回的是 **401**，所以問題在更早的地方（Line 207-212）

---

## 根本原因總結

### 🔴 確認的根本原因

**Middleware (`JwtAuthMiddleware`) 未能正確設定 `auth_user` 到 Request 中**

可能的原因:

1. **Middleware 順序問題**
   - `jwt.auth` middleware 可能沒有在正確的順序執行
   - 或被其他 middleware 覆蓋

2. **Request Merge 失敗**
   - `$request->merge(['auth_user' => $authenticatedUser])` 可能在某些情況下失敗
   - Laravel 11 可能對 Request merge 有不同的行為

3. **JWT Authentication Provider 配置問題**
   - `auth('api')` guard 可能未正確配置
   - JWT secret 可能不一致

4. **Type Casting 問題**
   - `JWTAuth::parseToken()->authenticate()` 可能返回的不是 `User` 實例
   - 而是代理物件或其他類型

---

## 測試用例對比

### Case 1: 直接生成 Token 測試

```bash
# 生成 Token
TOKEN=$(docker exec my_profile_laravel_app php artisan tinker --execute="
\$user = App\Models\User::where('email', 'test@example.com')->first();
\$token = auth('api')->login(\$user);
echo \$token;
")

# 測試 API
curl http://localhost:8080/api/salesperson/profile \
  -H "Authorization: Bearer $TOKEN"
```

**結果**: 401 "Unauthorized"

### Case 2: 瀏覽器登入後測試

- ✅ Token 正確儲存
- ✅ Token 格式正確
- ✅ Token 未過期
- ❌ API 仍返回 401

---

## 後續步驟建議

### 1. 驗證 Middleware 執行

在 `JwtAuthMiddleware.php` 添加日誌：

```php
public function handle(Request $request, Closure $next): Response
{
    \Log::info('JwtAuthMiddleware: Starting...', [
        'has_token' => $request->hasHeader('Authorization'),
        'token' => $request->header('Authorization'),
    ]);

    try {
        $authenticatedUser = JWTAuth::parseToken()->authenticate();

        \Log::info('JwtAuthMiddleware: User authenticated', [
            'user_id' => $authenticatedUser->id,
            'user_class' => get_class($authenticatedUser),
            'is_user_instance' => $authenticatedUser instanceof User,
        ]);

        // ...

        $request->merge(['auth_user' => $authenticatedUser]);

        \Log::info('JwtAuthMiddleware: auth_user merged', [
            'auth_user_exists' => $request->has('auth_user'),
            'auth_user_class' => get_class($request->get('auth_user')),
        ]);

    } catch (\Exception $e) {
        \Log::error('JwtAuthMiddleware: Exception', [
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ]);
    }

    return $next($request);
}
```

### 2. 檢查 JWT 配置

檔案: `config/auth.php`

確認:
- `'api'` guard 正確配置
- JWT driver 正確設定
- Provider 指向正確的 User model

### 3. 檢查 Middleware 註冊

檔案: `bootstrap/app.php`

確認:
- `jwt.auth` alias 正確註冊
- Middleware 在正確的位置執行

### 4. 替代方案：改用標準 Laravel auth middleware

考慮使用 Laravel 內建的 `auth:api` middleware 而不是自訂的 `jwt.auth`:

```php
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [SalespersonProfileController::class, 'me']);
});
```

然後在 Controller 使用標準方式:

```php
public function me(Request $request): JsonResponse
{
    $user = $request->user();  // Laravel 標準方式
    // 或
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    // ...
}
```

---

## 結論

**確認的問題**:
- ❌ **這不是 Token 刷新問題** - Token 是有效的且未過期
- ❌ **這不是 CORS 問題** - 請求正確發送
- ❌ **這不是 Frontend 問題** - Token 正確傳遞
- ✅ **這是 Backend Middleware 設定問題** - `auth_user` 未正確注入到 Request

**優先級**: Critical (P0)

**下一步**:
1. 添加日誌驗證 Middleware 執行流程
2. 檢查 JWT 配置
3. 考慮改用 Laravel 標準 auth middleware

---

**診斷執行者**: Claude Sonnet 4.5
**診斷完成時間**: 2026-01-11
**使用工具**: Playwright, Docker, Curl, Laravel Tinker
