# 實作開發

**變更名稱**: $ARGUMENTS

**任務**: 嚴格按照規格和任務清單實作程式碼

---

## 🔴 重要：使用專門的 Agent

### Laravel 後端開發

**所有 Laravel 相關開發必須使用 `laravel-specialist` agent**：

```
當處理 Laravel 後端任務時，必須使用 Task tool 啟動 laravel-specialist agent
```

**laravel-specialist 負責**：
- ✅ Controllers、Models、Migrations、Middleware、Policies
- ✅ Eloquent 關聯、Query 優化、Form Requests
- ✅ 確保遵循 Laravel 框架最佳實踐
- ✅ 實作完整的驗證和授權邏輯

**範例**：
```
Task tool:
- subagent_type: laravel-specialist
- prompt: 根據 openspec/changes/<feature-name>/specs/ 實作 Laravel 後端程式碼
```

### React/Next.js 前端開發

**所有 React/Next.js 相關開發必須使用 `react-specialist` agent**：

```
當處理 React/Next.js 前端任務時，必須使用 Task tool 啟動 react-specialist agent
```

**react-specialist 負責**：
- ✅ React/Next.js 組件和頁面實作
- ✅ TypeScript 型別安全（避免 any，使用高階類型）
- ✅ 效能優化（React.memo、useMemo、useCallback、虛擬化）
- ✅ React Internals 深度理解（Fiber、Reconciliation）
- ✅ Next.js App Router、Server Components、Server Actions
- ✅ React Query 整合、狀態管理優化

**範例**：
```
Task tool:
- subagent_type: react-specialist
- prompt: 根據 openspec/changes/<feature-name>/specs/ 實作 React/Next.js 前端程式碼
```

---

## 前置條件

- ✅ Proposal 已完成
- ✅ Specs 已完成並通過驗證
- ✅ Tasks.md 已拆解完成
- ✅ 變更目錄存在: `openspec/changes/<feature-name>/`

---

## 核心原則

### 規範驅動實作

**❌ 禁止**:
- 偏離規格定義的 API 格式
- 修改資料表結構（與規格不符）
- 跳過業務規則驗證
- 添加規格外的功能
- 猜測或假設未明確定義的行為

**✅ 必須**:
- 嚴格遵循 `specs/api.md` 的定義
- 嚴格遵循 `specs/data-model.md` 的結構
- 實現所有 `specs/business-rules.md` 的規則
- 按照 `tasks.md` 的順序執行

---

## 工作流程

### Step 1: 初始化任務追蹤

使用 `TodoWrite` 建立任務清單（對應 tasks.md）

```javascript
[
  {content: "Create migration for ratings", status: "pending", activeForm: "建立 ratings Migration"},
  {content: "Create RatingModel", status: "pending", activeForm: "建立 RatingModel"},
  {content: "Create RatingController", status: "pending", activeForm: "建立 RatingController"},
  // ... 其他任務
]
```

---

### Step 2: 逐步實作

對於每個任務：

```
1. 標記為 in_progress（僅一個任務）
2. 讀取相關規格（API/Data Model/Business Rules）
3. 實作程式碼（嚴格遵循規格）
4. 驗證功能正確
5. 標記為 completed
6. 繼續下一個任務
```

**重要**: 一次只能有一個 `in_progress` 任務

---

### Step 3: 實作細節

#### 3.1 建立 Migration

**讀取**: `specs/data-model.md` 的 Migration 程式碼

**執行**:
```bash
docker exec -it <container> php spark make:migration CreateTableName
```

**實作**:
- 複製規格中的完整 Migration 程式碼
- 不要修改欄位定義
- 確保所有索引和外鍵都已加入

**驗證**:
```bash
docker exec -it <container> php spark migrate
```

---

#### 3.2 建立 Model

**讀取**: `specs/data-model.md` 的 Model 程式碼

**執行**:
```bash
docker exec -it <container> php spark make:model TableNameModel
```

**實作**:
- 複製規格中的完整 Model 程式碼
- 包含所有 validation rules
- 包含所有 custom methods

**驗證**:
- 測試基本 CRUD 操作
- 測試驗證規則是否生效

---

#### 3.3 建立 Controller

**讀取**: `specs/api.md` 的所有端點定義

**執行**:
```bash
docker exec -it <container> php spark make:controller Api/FeatureController
```

**實作**:
- 繼承 `ResourceController`
- 實作每個端點方法
- **嚴格遵循 API 規格的 Request/Response 格式**

**範例**:
```php
public function create()
{
    // 1. 讀取規格: specs/api.md 的 POST /api/endpoint
    // 2. 取得 Request data
    $data = $this->request->getJSON(true);

    // 3. 驗證（遵循規格的 Validation 規則）
    if (!$this->validate($rules)) {
        return $this->failValidationErrors($this->validator->getErrors());
    }

    // 4. 業務規則檢查（遵循 specs/business-rules.md）
    // 實現 BR-001, BR-002 等

    // 5. 執行操作
    $result = $this->model->insert($data);

    // 6. 返回回應（遵循規格的 Response 格式）
    return $this->respondCreated([
        'status' => 'success',
        'data' => $result,
        'message' => '操作成功'
    ]);
}
```

---

#### 3.4 實現業務規則

**讀取**: `specs/business-rules.md` 的所有規則

對於每條規則（BR-001, BR-002...）:

**範例 - BR-001: Self-Rating Prevention**:
```php
// 規則: 使用者不能評分自己
$userId = $this->getCurrentUserId();
$salesperson = $this->salespersonModel->find($salespersonId);

if ($salesperson['user_id'] === $userId) {
    return $this->failForbidden('無法評分自己');
}
```

**檢查清單**:
- [ ] 所有規則都已實現
- [ ] 錯誤處理符合規格定義的 HTTP 狀態碼
- [ ] 錯誤訊息符合規格定義

---

#### 3.5 更新 Routes

**讀取**: `specs/api.md` 的所有端點

**檔案**: `app/Config/Routes.php`

**實作**:
```php
$routes->group('api', ['filter' => 'auth'], function($routes) {
    $routes->post('endpoint', 'Api\FeatureController::create');
    $routes->get('endpoint/(:num)', 'Api\FeatureController::show/$1');
    // ... 其他路由
});
```

**驗證**:
```bash
docker exec -it <container> php spark routes | grep endpoint
```

---

### Step 4: 測試驗證

#### 4.1 基本功能測試

使用 curl 或測試腳本，測試規格中的範例：

```bash
# 測試成功案例（從 specs/api.md 複製）
curl -X POST http://localhost:8080/api/endpoint \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "field": "value"
  }'

# 預期結果: 200 OK, response 符合規格
```

#### 4.2 錯誤情況測試

測試所有錯誤情況：

```bash
# 測試 401 (未登入)
curl -X POST http://localhost:8080/api/endpoint \
  -H "Content-Type: application/json" \
  -d '{"field":"value"}'

# 預期結果: 401 Unauthorized

# 測試 422 (驗證錯誤)
curl -X POST http://localhost:8080/api/endpoint \
  -H "Authorization: Bearer <token>" \
  -d '{"field":""}'

# 預期結果: 422, errors 物件包含驗證錯誤
```

#### 4.3 業務規則測試

測試所有業務規則是否生效：

**範例 - 測試 BR-001**:
```bash
# 嘗試違反規則
curl -X POST http://localhost:8080/api/ratings \
  -H "Authorization: Bearer <self-user-token>" \
  -d '{
    "salesperson_id": <self-salesperson-id>,
    "rating": 5
  }'

# 預期結果: 403 Forbidden, message: "無法評分自己"
```

---

### Step 5: 錯誤處理

#### 遇到錯誤時

```
1. ❌ 不要標記任務為 completed
2. 分析錯誤原因:
   - 規格問題？ → 返回 /spec 修正規格
   - 實作問題？ → 修復後繼續
   - 環境問題？ → 解決環境問題
3. 創建修復子任務（如需要）
4. 修復後重新測試
5. 確認無誤後標記 completed
```

**禁止**:
- ❌ 忽略錯誤繼續執行
- ❌ 留待「稍後修復」
- ❌ 偏離規格「臨時解決」

---

## 品質檢查清單

每個任務完成時必須確認:

### 程式碼品質
- [ ] 無語法錯誤
- [ ] 無明顯安全漏洞（SQL Injection, XSS）
- [ ] 遵循專案程式碼規範
- [ ] 變數命名有意義

### 功能正確性
- [ ] API Request/Response 與規格完全一致
- [ ] 資料表結構與規格完全一致
- [ ] 所有業務規則都已實現
- [ ] 所有驗收標準都可測試

### 測試完成
- [ ] 基本功能已測試（成功案例）
- [ ] 錯誤情況已測試（失敗案例）
- [ ] 業務規則已驗證
- [ ] 無破壞現有功能

---

## 進度報告

### 簡潔報告

✅ **正確**:
```
已完成 Task 1.1: Migration 已建立並執行
進行中 Task 1.2: 建立 RatingModel
```

❌ **避免**:
```
我現在正在建立 RatingModel，這個 Model 會用於管理評分資料，
它繼承了 CodeIgniter 的 Model 類別，並且包含了驗證規則...
```

---

## 完成標準

開發完成時必須:
- ✅ 所有 Tasks 標記為 completed
- ✅ 所有驗收標準可測試
- ✅ 無已知 Bug 或錯誤
- ✅ 程式碼遵循專案規範
- ✅ 基本功能測試通過

---

##常見錯誤

### 錯誤 1: 偏離規格

**症狀**: 實作的 API 回應格式與規格不同

**範例**:
```php
// 規格定義:
{
  "status": "success",
  "data": {...}
}

// 錯誤實作:
{
  "success": true,  // ❌ 欄位名稱不同
  "result": {...}    // ❌ 欄位名稱不同
}
```

**解決**: 嚴格複製規格中的格式

---

### 錯誤 2: 跳過業務規則

**症狀**: 沒有實現所有業務規則

**解決**: 逐條檢查 `specs/business-rules.md`，確保每條規則都已實現

---

### 錯誤 3: 同時處理多個任務

**症狀**: TodoWrite 中有多個 `in_progress` 任務

**解決**: 只保留一個 `in_progress`，完成後再開始下一個

---

## 使用範例

```bash
/develop rating-feature
```

**執行流程**:
1. 讀取 `openspec/changes/rating-feature/tasks.md`
2. 使用 TodoWrite 建立任務清單
3. 標記 Task 1.1 為 in_progress
4. 讀取 `specs/data-model.md`
5. 建立 Migration（嚴格遵循規格）
6. 執行 Migration
7. 驗證資料表已建立
8. 標記 Task 1.1 為 completed
9. 繼續 Task 1.2...
10. 重複直到所有任務完成

---

**下一步**: 使用 `/archive <feature-name>` 歸檔到規範庫
