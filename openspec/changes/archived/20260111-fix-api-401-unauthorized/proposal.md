# Proposal: 修復業務員 Dashboard 頁面 API 401 Unauthorized 錯誤

**問題類型**: Bug Fix (Critical)
**影響範圍**: Frontend + Backend API 認證流程
**優先級**: High (P1 - 阻塞業務員核心功能)
**建立日期**: 2026-01-11

---

## 1. 背景與目標

### 業務背景

目前 YAMU 系統存在一個關鍵的認證問題：當業務員帳號登入後訪問 Dashboard 頁面（`http://localhost:3001/dashboard`），頁面會呼叫 API `GET /api/salesperson/profile` 以獲取業務員個人資料，但該 API 始終回傳 **401 Unauthorized** 錯誤，導致：

- 業務員無法查看和編輯個人資料
- Dashboard 頁面無法正常顯示
- 使用者體驗嚴重受損
- 影響業務員核心功能的可用性

### 問題嚴重性

- **阻塞性**: 業務員無法使用 Dashboard 核心功能
- **頻率**: 每次登入後訪問 Dashboard 必定發生
- **範圍**: 影響所有業務員使用者
- **可見度**: 使用者直接可見的錯誤

### 目標

1. **根本原因分析**: 找出為什麼已登入狀態仍會收到 401 錯誤
2. **修復認證問題**: 確保 Token 正確傳遞和驗證
3. **驗證修復**: 確認業務員可正常訪問 Dashboard 並獲取個人資料
4. **預防機制**: 建立更健全的錯誤處理和偵測機制

---

## 2. 問題根本原因分析

經過深入調查程式碼，我發現了問題的根本原因：

### 發現的問題

#### ✅ Frontend Token 傳遞機制（正常）

**檔案**: `frontend/lib/api/client.ts`

```typescript
// Request 攔截器 - 自動添加 Token (第 15-28 行)
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('access_token');
      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);
```

✅ **正常**: Frontend 正確從 `localStorage` 讀取 Token 並透過 `Authorization: Bearer {token}` 傳遞

#### ✅ Backend 路由定義（正常）

**檔案**: `my_profile_laravel/routes/api.php`

```php
// Protected Salesperson routes (第 98-124 行)
Route::prefix('salesperson')->group(function (): void {
    Route::middleware('jwt.auth')->group(function (): void {
        // ...
        // Profile alias (points to /profile/ endpoint)
        Route::get('/profile', [SalespersonProfileController::class, 'me']);
        // ...
    });
});
```

✅ **正常**: 路由正確定義，使用 `jwt.auth` 中間件保護

#### ✅ Backend JWT 中間件（正常）

**檔案**: `my_profile_laravel/app/Http/Middleware/JwtAuthMiddleware.php`

```php
public function handle(Request $request, Closure $next): Response
{
    try {
        $authenticatedUser = JWTAuth::parseToken()->authenticate();

        if (! $authenticatedUser instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check if user account is active
        if ($authenticatedUser->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active',
            ], 403);
        }

        return $next($request);
    } catch (TokenExpiredException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Token has expired',
        ], 401);
    } catch (TokenInvalidException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Token is invalid',
        ], 401);
    } catch (JWTException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Token not provided',
        ], 401);
    }
}
```

✅ **正常**: 中間件邏輯完整，能正確處理 Token 驗證

### 可能的原因

根據程式碼分析，401 錯誤可能來自以下幾種情況：

#### 1. Token 過期但未自動刷新（最可能）

**症狀**:
- 登入後可以正常使用（Token 有效）
- 一段時間後 Token 過期
- 刷新機制未正確觸發或失敗

**原因**:
```typescript
// frontend/lib/api/client.ts (第 30-72 行)
// Response 攔截器處理 Token 刷新
if (error.response?.status === 401 && !originalRequest._retry) {
    originalRequest._retry = true;

    try {
        const refreshToken = localStorage.getItem('refresh_token');
        if (refreshToken) {
            const response = await axios.post(`${API_BASE_URL}/auth/refresh`, {
                refresh_token: refreshToken,
            });

            const { access_token } = response.data.data;
            localStorage.setItem('access_token', access_token);

            return apiClient(originalRequest);
        }
    } catch (refreshError) {
        // Refresh 失敗，清除 Token 並重定向
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        window.location.href = '/login';
    }
}
```

**潛在問題**:
- Refresh Token 可能也過期
- Refresh API 可能失敗但未正確處理
- 循環依賴導致刷新機制失效

#### 2. Token 格式錯誤

**可能原因**:
- `localStorage` 中的 Token 值包含多餘字元
- Token 未正確儲存（例如：儲存了整個 response 物件而非 token 字串）
- Token 在某些情況下被污染

#### 3. 用戶帳號狀態問題

**檢查點**:
```php
// 中間件檢查用戶狀態 (第 36-41 行)
if ($authenticatedUser->status !== 'active') {
    return response()->json([
        'success' => false,
        'message' => 'Account is not active',
    ], 403);
}
```

- 如果用戶 `status` 不是 `active`，會回傳 403（不是 401）
- 所以這不是主要原因

#### 4. CORS 或 Preflight 問題

**可能性較低**，因為：
- 如果是 CORS 問題，其他 API 也會受影響
- 問題描述表明只有特定 API 受影響

---

## 3. 功能範圍

### In Scope（本次修復）

#### ✅ 核心修復

1. **診斷工具**
   - 新增 Token 驗證端點 `POST /api/auth/validate-token`
   - 前端新增 Token 狀態檢查工具
   - 新增詳細的錯誤日誌記錄

2. **Token 刷新機制強化**
   - 修復 Axios 攔截器中的 Token 刷新邏輯
   - 新增 Token 過期前主動刷新機制（Proactive Refresh）
   - 處理 Refresh Token 過期的 Edge Case

3. **錯誤處理改善**
   - 統一 401 錯誤處理邏輯
   - 新增更明確的錯誤訊息
   - 前端新增 Token 狀態偵測

4. **Backend API 改善**
   - 確保 JWT 中間件正確處理所有 Token 異常
   - 新增 Token 驗證日誌
   - 改善錯誤回應格式

#### ✅ 測試與驗證

1. **手動測試**
   - 登入流程測試
   - Token 過期場景測試
   - Token 刷新測試
   - Dashboard 頁面功能測試

2. **自動化測試**
   - JWT 中間件單元測試
   - Token 刷新流程測試
   - API 端點整合測試

### Out of Scope（不在範圍內）

#### ❌ 不修復的項目

1. **認證機制重構**
   - 不更換認證方式（保持 JWT）
   - 不重構整個認證流程
   - 理由：這是 Bug 修復，非架構重構

2. **多裝置 Token 管理**
   - 不實作多裝置登出
   - 不追蹤 Token 使用裝置
   - 理由：超出 Bug 修復範圍

3. **Token 加密強化**
   - 不修改 JWT 簽名算法
   - 不增加 Token 加密層級
   - 理由：現有安全性已足夠

4. **Session Management**
   - 不新增 Session 管理機制
   - 不實作 Remember Me 功能
   - 理由：非緊急需求

---

## 4. 詳細需求

### 4.1 功能需求

#### FR-001: Token 驗證端點

**描述**: 新增 API 端點用於驗證 Token 有效性

**端點**: `POST /api/auth/validate-token`

**請求**:
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**回應**:
```json
{
  "success": true,
  "data": {
    "valid": true,
    "user_id": 123,
    "role": "salesperson",
    "expires_at": "2026-01-11T15:30:00Z",
    "expires_in_seconds": 3600
  }
}
```

**優先級**: Should Have

**驗收標準**:
- [ ] API 正確驗證 Token 有效性
- [ ] 回傳 Token 過期時間
- [ ] 回傳使用者基本資訊
- [ ] 無效 Token 回傳明確錯誤

#### FR-002: Token 刷新機制改善

**描述**: 強化 Axios 攔截器中的 Token 自動刷新機制

**優先級**: Must Have

**驗收標準**:
- [ ] 401 錯誤時自動嘗試刷新 Token
- [ ] 刷新成功後重試原始請求
- [ ] 刷新失敗後清除 Token 並重定向登入
- [ ] 避免多個請求同時觸發刷新（防止 Race Condition）
- [ ] 處理 Refresh Token 過期情況

**實作細節**:
```typescript
// 新增 Token 刷新鎖機制
let isRefreshing = false;
let refreshSubscribers: Array<(token: string) => void> = [];

// 當 Token 刷新中時，其他請求等待
const subscribeTokenRefresh = (cb: (token: string) => void) => {
  refreshSubscribers.push(cb);
};

const onTokenRefreshed = (token: string) => {
  refreshSubscribers.forEach((cb) => cb(token));
  refreshSubscribers = [];
};
```

#### FR-003: 主動 Token 刷新（Proactive Refresh）

**描述**: Token 過期前 5 分鐘自動刷新，避免使用者操作中斷

**優先級**: Should Have

**驗收標準**:
- [ ] 解析 Token 過期時間
- [ ] Token 過期前 5 分鐘觸發刷新
- [ ] 刷新成功後更新 `localStorage`
- [ ] 刷新失敗時不影響現有功能（因為 Token 仍有效）

**實作策略**:
```typescript
// 使用 jwt-decode 解析 Token
import { jwtDecode } from 'jwt-decode';

const checkTokenExpiry = () => {
  const token = getAccessToken();
  if (!token) return;

  const decoded = jwtDecode(token);
  const expiresAt = decoded.exp * 1000; // 轉換為毫秒
  const now = Date.now();
  const timeUntilExpiry = expiresAt - now;

  // 5 分鐘 = 300000 毫秒
  if (timeUntilExpiry < 300000 && timeUntilExpiry > 0) {
    refreshAccessToken();
  }
};

// 每分鐘檢查一次
setInterval(checkTokenExpiry, 60000);
```

#### FR-004: 改善錯誤處理和日誌

**描述**: 新增詳細的錯誤日誌和使用者友好的錯誤訊息

**優先級**: Should Have

**驗收標準**:
- [ ] Frontend 記錄所有 401 錯誤的詳細資訊
- [ ] Backend 記錄 JWT 驗證失敗的原因
- [ ] 提供明確的使用者錯誤提示
- [ ] 區分不同類型的認證錯誤

**錯誤類型**:
| 錯誤類型 | HTTP Status | 使用者訊息 | 系統日誌 |
|---------|-------------|-----------|---------|
| Token 未提供 | 401 | "請重新登入" | "No token provided" |
| Token 無效 | 401 | "登入已失效，請重新登入" | "Invalid token signature" |
| Token 過期 | 401 | "登入已過期，請重新登入" | "Token expired" |
| Refresh 失敗 | 401 | "無法更新登入狀態，請重新登入" | "Refresh token expired" |

### 4.2 技術需求

#### TN-001: Frontend 改善

**檔案**: `frontend/lib/api/client.ts`

**改善項目**:
1. **新增 Token 刷新鎖機制**
   - 防止多個請求同時觸發 Token 刷新
   - 使用 Promise 佇列管理等待的請求

2. **改善錯誤處理**
   - 區分不同類型的 401 錯誤
   - 記錄詳細的錯誤資訊
   - 提供明確的使用者提示

3. **新增 Token 狀態檢查**
   - 頁面載入時檢查 Token 有效性
   - Token 即將過期時主動刷新

#### TN-002: Backend 改善

**檔案**:
- `my_profile_laravel/app/Http/Middleware/JwtAuthMiddleware.php`
- `my_profile_laravel/app/Http/Controllers/Api/AuthController.php`

**改善項目**:
1. **新增 Token 驗證端點**
   - 實作 `validateToken()` 方法
   - 回傳 Token 詳細資訊

2. **改善中間件日誌**
   - 記錄 Token 驗證失敗原因
   - 記錄請求的 Authorization Header（脫敏處理）

3. **統一錯誤回應格式**
   - 確保所有認證錯誤使用一致格式
   - 新增錯誤代碼 (error_code)

### 4.3 測試需求

#### 測試案例 1: Token 自動刷新（正常情況）

**前置條件**:
- 使用者已登入
- Access Token 將在 1 分鐘內過期
- Refresh Token 有效

**操作步驟**:
1. 發送 API 請求到 `/api/salesperson/profile`
2. API 回傳 401 (Token 過期)
3. 攔截器自動使用 Refresh Token 刷新
4. 刷新成功，取得新 Access Token
5. 自動重試原始請求

**預期結果**:
- 原始請求成功回傳資料
- 使用者無感知（無需重新登入）
- `localStorage` 中的 Access Token 已更新

#### 測試案例 2: Refresh Token 過期

**前置條件**:
- 使用者已登入
- Access Token 已過期
- Refresh Token 也已過期

**操作步驟**:
1. 發送 API 請求到 `/api/salesperson/profile`
2. API 回傳 401
3. 攔截器嘗試刷新 Token
4. Refresh API 回傳 401 (Refresh Token 過期)

**預期結果**:
- 清除 `localStorage` 中的 Tokens
- 顯示 Toast 訊息："登入已過期，請重新登入"
- 重定向到 `/login` 頁面

#### 測試案例 3: Token 格式錯誤

**前置條件**:
- `localStorage` 中的 Token 格式錯誤

**操作步驟**:
1. 發送 API 請求到 `/api/salesperson/profile`
2. API 回傳 401 (Token 無效)

**預期結果**:
- 顯示錯誤訊息："登入狀態異常，請重新登入"
- 清除 Tokens
- 重定向到 `/login`

#### 測試案例 4: 主動刷新機制

**前置條件**:
- 使用者已登入
- Token 將在 4 分鐘後過期

**操作步驟**:
1. 使用者停留在 Dashboard 頁面
2. 定時器檢測到 Token 即將過期
3. 自動觸發 Token 刷新

**預期結果**:
- Token 在背景自動刷新
- 使用者無感知
- `localStorage` 更新

#### 測試案例 5: 多個請求同時觸發刷新

**前置條件**:
- Access Token 已過期
- 同時發送 3 個 API 請求

**操作步驟**:
1. 3 個請求同時收到 401 回應
2. 第一個請求觸發刷新，設置 `isRefreshing = true`
3. 其他請求進入等待佇列
4. Token 刷新完成，通知所有等待的請求

**預期結果**:
- 只發送 1 次 Refresh 請求
- 所有等待的請求都使用新 Token 重試
- 3 個請求最終都成功

---

## 5. 邊界情境處理

### Edge Case 1: Token 正在刷新時使用者登出

**情境**: 使用者在 Token 刷新過程中點擊登出

**預期行為**:
- 中止 Token 刷新流程
- 清除所有 Tokens
- 立即重定向到登入頁面
- 取消所有待處理的 API 請求

### Edge Case 2: Token 在請求過程中過期

**情境**: 發送請求時 Token 有效，但在請求到達伺服器時已過期

**預期行為**:
- Backend 回傳 401
- Frontend 自動刷新 Token
- 重試原始請求

### Edge Case 3: Refresh API 回應延遲

**情境**: Refresh API 回應時間超過 10 秒

**預期行為**:
- 顯示 Loading 狀態
- 超時後顯示錯誤訊息
- 要求使用者重新登入

### Edge Case 4: 無網路連線

**情境**: 使用者網路斷線

**預期行為**:
- 顯示網路錯誤訊息
- 不觸發 Token 刷新
- 保留現有 Token（等待網路恢復）

### Edge Case 5: Backend API 維護中

**情境**: Backend API 回傳 503 Service Unavailable

**預期行為**:
- 顯示維護中訊息
- 不觸發 Token 刷新
- 不清除 Tokens

---

## 6. 技術考量

### 6.1 安全性考量

#### Token 儲存

**現狀**: Token 儲存在 `localStorage` 和 `cookies`

**考量**:
- ✅ 使用 HttpOnly Cookies 可防止 XSS 攻擊
- ⚠️ `localStorage` 容易受到 XSS 攻擊
- ⚠️ 但 Next.js SSR 需要在 Client 端訪問 Token

**決策**: 保持現狀，但新增以下安全措施：
1. 定期檢查 Token 完整性
2. 新增 Token 格式驗證
3. 記錄異常的 Token 使用

#### Token 傳輸

**考量**:
- ✅ 使用 HTTPS（Production）
- ✅ `Authorization: Bearer` 標準格式
- ✅ Token 不出現在 URL 中

### 6.2 效能考量

#### Token 刷新頻率

**問題**: 頻繁刷新可能增加伺服器負擔

**解決方案**:
- 主動刷新只在 Token 即將過期時觸發（5 分鐘前）
- 使用鎖機制避免重複刷新
- Refresh Token 有效期 14 天，減少刷新次數

#### 記憶體管理

**考量**:
- `refreshSubscribers` 陣列需要在使用後清空
- 避免記憶體洩漏

**實作**:
```typescript
const onTokenRefreshed = (token: string) => {
  refreshSubscribers.forEach((cb) => cb(token));
  refreshSubscribers = []; // 清空陣列
  isRefreshing = false; // 重置狀態
};
```

### 6.3 相容性考量

#### 瀏覽器相容性

**目標瀏覽器**:
- Chrome 90+
- Safari 14+
- Firefox 88+
- Edge 90+

**API 使用**:
- `localStorage`: 所有目標瀏覽器支援
- `Axios Interceptors`: 所有目標瀏覽器支援
- `jwt-decode`: 純 JavaScript，無相容性問題

#### 後端版本

**Laravel**: 11.x
**JWT Package**: tymon/jwt-auth 2.x

### 6.4 監控與偵測

#### Frontend 監控

**需要追蹤的指標**:
1. 401 錯誤發生次數
2. Token 刷新成功率
3. Token 刷新平均耗時
4. 強制登出次數（Refresh 失敗）

**實作方式**:
```typescript
// 使用 Console 記錄（開發環境）
console.log('[AUTH] Token refresh triggered', {
  timestamp: new Date().toISOString(),
  reason: 'Token expired',
});

// 未來可整合 Sentry 或 Google Analytics
```

#### Backend 監控

**需要追蹤的指標**:
1. JWT 驗證失敗率
2. 不同類型的認證錯誤比例
3. Refresh Token 使用頻率

**實作方式**:
```php
Log::info('JWT validation failed', [
    'reason' => $exception->getMessage(),
    'user_id' => $request->input('user_id'),
    'ip' => $request->ip(),
]);
```

---

## 7. 驗收標準

### 7.1 功能驗收

#### 核心功能

- [ ] **登入後訪問 Dashboard**: 業務員登入後可正常訪問 Dashboard 頁面
- [ ] **API 正常回應**: `GET /api/salesperson/profile` 正常回傳 200 和個人資料
- [ ] **資料正確顯示**: Dashboard 頁面正確顯示業務員資訊
- [ ] **編輯功能正常**: 可以編輯個人資料並儲存

#### Token 刷新機制

- [ ] **自動刷新**: Token 過期時自動刷新，使用者無感知
- [ ] **刷新成功**: 新 Token 正確儲存到 `localStorage`
- [ ] **請求重試**: 刷新後原始請求自動重試並成功
- [ ] **刷新失敗處理**: Refresh Token 過期時正確重定向到登入頁面

#### 錯誤處理

- [ ] **明確的錯誤訊息**: 所有認證錯誤都有清楚的使用者提示
- [ ] **正確的狀態碼**: 不同錯誤類型使用正確的 HTTP 狀態碼
- [ ] **不重複刷新**: 多個請求只觸發一次 Token 刷新

### 7.2 非功能驗收

#### 效能

- [ ] **Token 刷新耗時**: Refresh API 回應時間 < 500ms
- [ ] **頁面載入時間**: Dashboard 頁面首次載入 < 2 秒
- [ ] **API 回應時間**: `/api/salesperson/profile` 回應 < 300ms

#### 安全性

- [ ] **Token 格式驗證**: 拒絕格式錯誤的 Token
- [ ] **錯誤日誌記錄**: 所有認證失敗都有日誌記錄
- [ ] **敏感資訊保護**: 日誌中不包含完整 Token 內容

#### 可維護性

- [ ] **程式碼品質**: 通過 ESLint 和 PHPStan 檢查
- [ ] **單元測試**: JWT 中間件測試覆蓋率 > 90%
- [ ] **整合測試**: 認證流程端到端測試通過

### 7.3 使用者體驗驗收

#### 正常流程

- [ ] 登入後立即可訪問 Dashboard（無延遲）
- [ ] 頁面載入流暢，無閃爍
- [ ] 資料顯示完整且格式正確

#### 異常處理

- [ ] Token 過期時無需使用者操作（自動刷新）
- [ ] Refresh 失敗時有明確提示訊息
- [ ] 錯誤發生時不影響其他功能

---

## 8. 風險與依賴

### 8.1 潛在風險

#### 風險 1: Token 刷新機制引入新 Bug

**描述**: 修改 Axios 攔截器可能影響其他 API 請求

**機率**: 中
**影響**: 高

**緩解措施**:
1. 完整的單元測試覆蓋
2. 在 Staging 環境充分測試
3. 保留原有邏輯的備份
4. 使用 Feature Flag 控制新邏輯啟用

#### 風險 2: Race Condition 導致多次刷新

**描述**: 多個請求同時觸發 Token 刷新

**機率**: 中
**影響**: 中

**緩解措施**:
1. 實作 Token 刷新鎖機制
2. 使用 Promise 佇列管理等待請求
3. 測試併發場景

#### 風險 3: 主動刷新消耗過多資源

**描述**: 定時檢查和刷新可能增加不必要的 API 請求

**機率**: 低
**影響**: 低

**緩解措施**:
1. 只在 Token 即將過期時才刷新（5 分鐘前）
2. 刷新失敗不影響現有功能
3. 可透過 Feature Flag 關閉主動刷新

### 8.2 依賴項目

#### Frontend 依賴

| 依賴項目 | 版本 | 用途 | 風險評估 |
|---------|------|------|---------|
| axios | 1.7.9 | HTTP 客戶端 | 低（穩定版本） |
| jwt-decode | Latest | 解析 JWT | 低（純函式） |
| js-cookie | Latest | Cookie 管理 | 低（穩定） |

#### Backend 依賴

| 依賴項目 | 版本 | 用途 | 風險評估 |
|---------|------|------|---------|
| tymon/jwt-auth | 2.x | JWT 認證 | 低（廣泛使用） |
| Laravel | 11.x | Framework | 低（LTS） |

#### 系統依賴

- **無外部服務依賴**: 認證完全在本地處理
- **無資料庫 Schema 變更**: 不需要 Migration
- **無配置文件變更**: 使用現有配置

---

## 9. 實作計畫

### Phase 1: 診斷與分析（1 天）

**目標**: 確認問題根本原因

**任務**:
1. [ ] 在 Frontend 新增詳細的 Token 狀態日誌
2. [ ] 在 Backend 新增 JWT 驗證日誌
3. [ ] 使用 Postman 測試 API 端點
4. [ ] 檢查 Token 格式和內容
5. [ ] 驗證 Token 過期時間設置

**產出**:
- 問題診斷報告
- 確認的根本原因
- 修復策略文件

### Phase 2: Backend 修復（1 天）

**目標**: 改善 Backend API 和中間件

**任務**:
1. [ ] 實作 Token 驗證端點 `POST /api/auth/validate-token`
2. [ ] 改善 JWT 中間件錯誤處理
3. [ ] 新增詳細的錯誤日誌
4. [ ] 統一錯誤回應格式
5. [ ] 撰寫單元測試

**產出**:
- 新增 API 端點
- 改善的中間件程式碼
- 單元測試（覆蓋率 > 90%）

### Phase 3: Frontend 修復（2 天）

**目標**: 改善 Token 管理和錯誤處理

**任務**:
1. [ ] 實作 Token 刷新鎖機制
2. [ ] 改善 Axios 攔截器錯誤處理
3. [ ] 新增主動 Token 刷新機制
4. [ ] 實作 Token 狀態檢查工具
5. [ ] 改善使用者錯誤提示

**產出**:
- 改善的 API 客戶端
- Token 管理工具函式
- 錯誤處理邏輯

### Phase 4: 測試與驗證（1 天）

**目標**: 全面測試修復效果

**任務**:
1. [ ] 執行所有測試案例
2. [ ] 測試邊界情境
3. [ ] 測試不同瀏覽器
4. [ ] 效能測試
5. [ ] 安全性測試

**產出**:
- 測試報告
- Bug 清單（如有）
- 效能基準數據

### Phase 5: 上線與監控（1 天）

**目標**: 部署到生產環境並監控

**任務**:
1. [ ] 部署到 Staging 環境
2. [ ] Staging 環境完整測試
3. [ ] 部署到 Production
4. [ ] 監控 401 錯誤數量
5. [ ] 收集使用者反饋

**產出**:
- 部署報告
- 監控儀表板
- 上線總結文件

**預計總工時**: 6 天

---

## 10. 成功指標

### 量化指標

1. **錯誤率降低**
   - 目標: 401 錯誤率降低 > 95%
   - 測量: 監控 API 錯誤日誌

2. **Token 刷新成功率**
   - 目標: > 99%
   - 測量: 記錄刷新成功/失敗次數

3. **使用者投訴**
   - 目標: 0 筆關於登入問題的投訴
   - 測量: 客服回報

4. **頁面載入成功率**
   - 目標: Dashboard 頁面載入成功率 > 99.5%
   - 測量: Frontend 監控

### 質化指標

1. **使用者體驗**
   - 業務員可以順暢使用 Dashboard
   - 無需頻繁重新登入
   - 錯誤訊息清楚易懂

2. **開發體驗**
   - 認證邏輯清晰易懂
   - 錯誤容易除錯
   - 有完整的測試覆蓋

3. **系統穩定性**
   - 無新增的 Bug
   - 無效能退化
   - 無安全性風險

---

## 11. 附錄

### 11.1 參考資料

1. **技術文檔**
   - [JWT 官方規範](https://jwt.io/introduction)
   - [tymon/jwt-auth 文檔](https://jwt-auth.readthedocs.io/)
   - [Axios 攔截器文檔](https://axios-http.com/docs/interceptors)

2. **專案文檔**
   - `frontend/CLAUDE.md` - Frontend 開發規範
   - `my_profile_laravel/CLAUDE.md` - Backend 開發規範
   - `openspec/specs/backend/api.md` - API 規格

3. **相關 Issue**
   - 無（新問題）

### 11.2 相關檔案

**Frontend**:
- `frontend/lib/api/client.ts` - Axios 配置和攔截器
- `frontend/lib/auth/token.ts` - Token 管理工具
- `frontend/middleware.ts` - Next.js 路由中間件
- `frontend/app/(dashboard)/dashboard/page.tsx` - Dashboard 頁面

**Backend**:
- `my_profile_laravel/routes/api.php` - API 路由定義
- `my_profile_laravel/app/Http/Middleware/JwtAuthMiddleware.php` - JWT 中間件
- `my_profile_laravel/app/Http/Controllers/Api/AuthController.php` - 認證控制器
- `my_profile_laravel/app/Http/Controllers/Api/SalespersonProfileController.php` - 業務員資料控制器

### 11.3 測試環境

**開發環境**:
- Frontend: http://localhost:3001
- Backend: http://localhost:8080
- MySQL: localhost:3307

**測試帳號**:
- 業務員: 需在測試環境建立
- 一般使用者: 需在測試環境建立
- 管理員: 需在測試環境建立

### 11.4 後續規劃

**短期（本次修復後）**:
1. 監控 Token 刷新成功率
2. 收集使用者反饋
3. 微調錯誤訊息

**中期（1-2 個月）**:
1. 考慮實作 Token 白名單機制
2. 新增多裝置登入管理
3. 改善 Token 過期提醒

**長期（3-6 個月）**:
1. 評估是否需要更換認證方案（例如 OAuth2）
2. 實作 SSO（Single Sign-On）
3. 新增生物識別登入

---

## 總結

這是一個關鍵的 Bug 修復提案，旨在解決業務員 Dashboard 頁面的 401 認證錯誤問題。經過深入的程式碼分析，我們識別出潛在的根本原因（Token 刷新機制問題），並提出了完整的修復方案，包括：

1. **診斷工具**: 新增 Token 驗證端點和詳細日誌
2. **核心修復**: 改善 Token 刷新機制，新增鎖機制防止 Race Condition
3. **主動刷新**: Token 過期前自動刷新，提升使用者體驗
4. **錯誤處理**: 統一且清晰的錯誤處理和使用者提示
5. **全面測試**: 涵蓋正常流程和所有邊界情境

修復完成後，業務員將能夠順暢地使用 Dashboard 功能，無需頻繁重新登入，系統穩定性和使用者體驗都將得到顯著提升。

---

**建立者**: Claude (Product Manager)
**審核者**: 待定
**最後更新**: 2026-01-11
**版本**: 1.0
