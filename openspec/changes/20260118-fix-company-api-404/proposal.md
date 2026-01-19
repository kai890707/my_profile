# Proposal: 修復業務員公司資訊 API 404 錯誤

**日期**: 2026-01-18
**類型**: Bug Fix
**優先級**: Critical
**預估時間**: 30-60 分鐘

---

## 📋 問題描述

### 症狀
- **位置**: 業務員個人資料編輯頁面
- **操作**: 填入公司名稱後送出
- **錯誤**: `POST /api/salesperson/company` 返回 404
- **影響**: 業務員無法完成註冊流程

### 根本原因
Frontend 已實作公司資訊儲存功能（`lib/api/salesperson.ts:83-86`），但 Backend 缺少對應的 API 端點實作。

```typescript
// Frontend 已存在的 API 呼叫
export async function saveCompany(data: SaveCompanyRequest): Promise<ApiResponse<Company>> {
  const response = await apiClient.post<ApiResponse<Company>>('/salesperson/company', data);
  return response.data;
}
```

Backend `SalespersonController.php` 中沒有處理 `POST /api/salesperson/company` 的方法。

---

## 🎯 修復目標

### 主要目標
1. 實作 `POST /api/salesperson/company` API 端點
2. 允許業務員選擇既有公司或標記為自營業者
3. 確保業務員註冊流程完整可用

### Success Criteria
- ✅ API 端點返回 200 (不再 404)
- ✅ 業務員可以成功儲存公司資訊
- ✅ 可以選擇既有公司或自營業者
- ✅ 所有測試通過
- ✅ 不影響現有功能

---

## 🔍 技術分析

### 現有資料結構

**資料表**: `salesperson_profiles`
- `company_id` (nullable) - 外鍵關聯到 companies 表
- 如果 `company_id` 為 null，表示自營業者

**資料表**: `companies`
- `id` - 主鍵
- `name` - 公司名稱
- `tax_id` - 統一編號（可選）
- `type` - registered/personal

### Frontend Request 格式（推測）

```typescript
interface SaveCompanyRequest {
  company_id?: number;      // 選擇既有公司
  is_self_employed?: boolean; // 標記為自營業者
  // 或者
  company_name?: string;    // 新公司名稱
  company_tax_id?: string;  // 統一編號
}
```

### 所需實作

1. **Backend Controller 方法**
   - 新增 `SalespersonController::saveCompany()` 方法
   - 處理兩種情況：
     * 選擇既有公司 (`company_id`)
     * 標記為自營業者 (`company_id = null`)

2. **路由定義**
   - 新增 `POST /api/salesperson/company` 路由
   - 需要認證 (auth:api middleware)
   - 需要業務員角色

3. **Form Request 驗證**
   - 新增 `SaveCompanyRequest` 驗證類別
   - 驗證規則：
     * `company_id` - 可選，必須存在於 companies 表
     * `is_self_employed` - boolean，如果為 true 則 company_id 必須為 null

4. **測試**
   - Feature Test: 儲存公司資訊成功
   - Feature Test: 標記為自營業者成功
   - Feature Test: 驗證失敗處理
   - Feature Test: 未認證用戶無法存取

---

## 🚀 開發範圍判斷

```json
{
  "backend": true,       // ✅ 需要實作 API 端點
  "frontend": false,     // ❌ 前端已存在，不需修改
  "ui_design": false,    // ❌ UI 已存在
  "architecture": false, // ❌ 無架構變更
  "database": false,     // ❌ 資料表已存在，不需變更
  "priority": "critical" // ⚡ Critical 優先級
}
```

### Backend 工作項目

1. **實作 Controller 方法** (10 分鐘)
   - 檔案: `app/Http/Controllers/Api/SalespersonController.php`
   - 方法: `saveCompany(SaveCompanyRequest $request)`
   - 邏輯: 更新 salesperson_profile 的 company_id

2. **新增 Form Request** (5 分鐘)
   - 檔案: `app/Http/Requests/SaveCompanyRequest.php`
   - 驗證規則

3. **新增路由** (2 分鐘)
   - 檔案: `routes/api.php`
   - 路由: `POST /salesperson/company`

4. **撰寫測試** (15 分鐘)
   - 檔案: `tests/Feature/Controllers/SalespersonControllerTest.php`
   - 4 個測試案例

5. **執行測試與修復** (10 分鐘)

---

## 📝 實作規格

### API 端點規格

**端點**: `POST /api/salesperson/company`

**認證**: Required (auth:api middleware)

**角色**: Salesperson

**Request Body**:
```json
{
  "company_id": 123,           // Optional: 選擇既有公司
  "is_self_employed": false    // Optional: 標記為自營業者
}
```

**驗證規則**:
- `company_id` - optional, integer, exists:companies,id
- `is_self_employed` - optional, boolean
- 兩者不可同時存在
- 至少提供一個

**Response (Success - 200)**:
```json
{
  "success": true,
  "data": {
    "profile": {
      "id": 1,
      "user_id": 5,
      "company_id": 123,
      "full_name": "王小明",
      "company": {
        "id": 123,
        "name": "ABC 保險公司",
        "tax_id": "12345678"
      }
    }
  },
  "message": "公司資訊已更新"
}
```

**Response (Self-employed - 200)**:
```json
{
  "success": true,
  "data": {
    "profile": {
      "id": 1,
      "user_id": 5,
      "company_id": null,
      "full_name": "王小明",
      "company": null
    }
  },
  "message": "已設定為自營業者"
}
```

**Response (Error - 422)**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "驗證失敗",
    "details": {
      "company_id": ["選擇的公司不存在"]
    }
  }
}
```

**Response (Error - 403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "僅業務員可更新公司資訊"
  }
}
```

### 測試案例

1. **測試：業務員可以選擇既有公司**
   - 前置條件：已登入業務員，公司已存在
   - 操作：POST /api/salesperson/company with company_id
   - 預期：200, profile.company_id 更新

2. **測試：業務員可以設定為自營業者**
   - 前置條件：已登入業務員
   - 操作：POST /api/salesperson/company with is_self_employed=true
   - 預期：200, profile.company_id = null

3. **測試：驗證失敗 - 公司不存在**
   - 前置條件：已登入業務員
   - 操作：POST with company_id=999999 (不存在)
   - 預期：422, 驗證錯誤

4. **測試：驗證失敗 - 同時提供 company_id 和 is_self_employed**
   - 前置條件：已登入業務員
   - 操作：POST with both company_id and is_self_employed
   - 預期：422, 驗證錯誤

5. **測試：未認證用戶無法存取**
   - 前置條件：未登入
   - 操作：POST /api/salesperson/company
   - 預期：401

6. **測試：非業務員無法存取**
   - 前置條件：已登入一般用戶
   - 操作：POST /api/salesperson/company
   - 預期：403

---

## 🎯 量化標準

### API 效能
- P95 響應時間 < 200ms
- 成功率 > 99.9%

### 測試覆蓋率
- Feature Tests: 6 個測試
- 覆蓋率目標: 100% (此功能)

### 程式碼品質
- PHPStan Level 9: 0 errors
- Code Complexity: <= 5

---

## ⚡ 風險評估

### 低風險
- ✅ 不涉及資料庫變更
- ✅ 不影響現有功能
- ✅ 純新增功能，無破壞性變更

### 注意事項
- ⚠️ 需確認 Frontend Request 格式與實際需求一致
- ⚠️ 需確認業務員是否有權限選擇任何公司，或僅限特定公司

---

## 📅 執行計畫

### 立即執行（自動化）

**Phase 1**: 需求分析 ✅ (已完成)

**Phase 2**: 實作 Backend API (預估 30 分鐘)
- Step 1: 新增 Form Request (5 分鐘)
- Step 2: 實作 Controller 方法 (10 分鐘)
- Step 3: 新增路由 (2 分鐘)
- Step 4: 撰寫測試 (10 分鐘)
- Step 5: 執行測試並修復 (5 分鐘)

**Phase 3**: Git 操作 (5 分鐘)
- 創建 commit
- 推送到遠端
- 無需創建 PR（緊急修復，直接合併）

**Phase 4**: 驗證 (5 分鐘)
- 在 localhost:8080 測試 API
- 在 localhost:3001 測試前端整合
- 確認功能正常

**總預估時間**: 40-45 分鐘

---

## ✅ 確認事項

- [x] 問題根本原因已確認：Backend 缺少 API 端點
- [x] 開發範圍已判斷：只需 Backend
- [x] 優先級：Critical（緊急修復）
- [x] 不需要 Frontend 變更
- [x] 不需要資料庫變更
- [x] 測試策略已定義
- [x] 量化標準已設定

---

**準備執行**: 自動化修復流程

**下一步**:
1. 建立 Git branch: `fix/20260118-company-api-404`
2. 實作 Backend API
3. 執行測試
4. Commit & Push
