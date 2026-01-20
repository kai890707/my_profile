# Proposal: 修復待審核項目 API 缺少工作經驗與證照資料

## 1. 背景與目標

### 業務背景
管理員在使用「待審核項目」頁面 (`/admin/approvals`) 時，發現「工作經驗」(Experiences) 和「證照」(Certifications) 兩個頁籤是空白的。經過調查發現，這是因為 Backend API (`GET /api/admin/pending-approvals`) 未返回這兩類資料，導致 Frontend 無法顯示任何內容。

### 目標使用者
- 系統管理員 (Admin)
- 負責審核業務員提交的個人資料

### 成功指標
- API 正確返回所有 4 類待審核資料（companies, profiles, experiences, certifications）
- Frontend 能夠正常顯示工作經驗和證照的待審核項目
- 當沒有待審核項目時，顯示友善的空狀態提示
- 審核操作後，列表即時更新

---

## 2. 功能描述

### 核心功能
修復 Backend API `GET /api/admin/pending-approvals`，使其返回完整的待審核資料，包含：
1. 公司 (companies)
2. 業務員檔案 (profiles)
3. **工作經驗 (experiences)** ← 目前缺少
4. **證照 (certifications)** ← 目前缺少

### 使用流程
1. 管理員訪問 `/admin/approvals` 頁面
2. Frontend 呼叫 `GET /api/admin/pending-approvals` API
3. Backend 返回所有 `approval_status = 'pending'` 的項目
4. Frontend 顯示在對應的頁籤中
5. 如果某類別沒有待審核項目，顯示空狀態提示

### 使用情境範例

**情境 1: 有待審核項目**
- **使用者**: 管理員點擊「工作經驗」頁籤
- **系統**: 顯示所有 `approval_status = 'pending'` 的工作經驗列表
- **結果**: 管理員可以逐一審核

**情境 2: 無待審核項目**
- **使用者**: 管理員點擊「證照」頁籤
- **系統**: 顯示空狀態卡片："目前沒有待審核的證照"
- **結果**: 管理員了解目前沒有需要處理的項目

**情境 3: 審核後更新**
- **使用者**: 管理員審核通過一個工作經驗
- **系統**: 顯示成功訊息，並刷新待審核列表
- **結果**: 該項目從列表中消失

---

## 3. 功能範圍

### In Scope（本次實作）
- ✅ 修改 Backend API，新增 experiences 和 certifications 資料查詢
- ✅ 確保只返回 `approval_status = 'pending'` 的項目
- ✅ 建立測試資料（pending 狀態的 experiences 和 certifications）
- ✅ 撰寫 API 測試，驗證返回資料正確性
- ✅ 驗證 Frontend 能正確顯示資料
- ✅ 確認空狀態 UI 正常運作

### Out of Scope（不在範圍內）
- ❌ 修改 Frontend 程式碼：Frontend 已經準備好接收資料，無需修改
- ❌ 新增分頁功能：預期資料量 < 50 筆，不需要分頁
- ❌ 修改審核流程：僅修復資料顯示問題，不改變審核邏輯
- ❌ 新增篩選/排序功能：保持現有功能，僅修復資料缺失問題

---

## 4. 詳細需求

### 4.1 功能需求

#### FR-001: API 返回完整待審核資料
**描述**: `GET /api/admin/pending-approvals` 必須返回 4 類待審核資料
**優先級**: Must Have
**驗收標準**:
- [ ] API 返回包含 `experiences` 陣列
- [ ] API 返回包含 `certifications` 陣列
- [ ] 所有資料的 `approval_status` 必須為 'pending'
- [ ] API 回應時間 < 200ms（簡單查詢）
- [ ] 返回的資料結構符合 Frontend 預期格式

#### FR-002: 正確過濾審核狀態
**描述**: 只返回待審核（pending）狀態的項目
**優先級**: Must Have
**驗收標準**:
- [ ] Companies 過濾條件: `approval_status = 'pending'`
- [ ] Profiles 過濾條件: `approval_status = 'pending'`
- [ ] Experiences 過濾條件: `approval_status = 'pending'`
- [ ] Certifications 過濾條件: `approval_status = 'pending'`
- [ ] 已審核（approved/rejected）的項目不會出現在返回結果中

#### FR-003: 空狀態處理
**描述**: 當某類別沒有待審核項目時，返回空陣列
**優先級**: Must Have
**驗收標準**:
- [ ] 如果沒有待審核 experiences，返回 `experiences: []`
- [ ] 如果沒有待審核 certifications，返回 `certifications: []`
- [ ] Frontend 能正確顯示空狀態 UI
- [ ] 空狀態顯示友善提示文字（例如："目前沒有待審核的工作經驗"）

---

### 4.2 資料需求

#### API 回應格式

**Endpoint**: `GET /api/admin/pending-approvals`

**Response Schema**:
```json
{
  "companies": [
    {
      "id": "integer",
      "name": "string",
      "approval_status": "pending",
      // ... 其他公司欄位
    }
  ],
  "profiles": [
    {
      "id": "integer",
      "user_id": "integer",
      "approval_status": "pending",
      // ... 其他檔案欄位
    }
  ],
  "experiences": [
    {
      "id": "integer",
      "salesperson_profile_id": "integer",
      "company_name": "string",
      "position": "string",
      "start_date": "date",
      "end_date": "date|null",
      "description": "string|null",
      "approval_status": "pending",
      "created_at": "datetime",
      "updated_at": "datetime"
    }
  ],
  "certifications": [
    {
      "id": "integer",
      "salesperson_profile_id": "integer",
      "name": "string",
      "issuer": "string",
      "issue_date": "date",
      "expiry_date": "date|null",
      "credential_id": "string|null",
      "approval_status": "pending",
      "created_at": "datetime",
      "updated_at": "datetime"
    }
  ]
}
```

#### 資料庫查詢

**Experiences Table**:
- 篩選條件: `WHERE approval_status = 'pending'`
- 排序: `ORDER BY created_at DESC`（最新的在前）
- 關聯查詢: 可能需要關聯 salesperson_profiles 以顯示業務員資訊

**Certifications Table**:
- 篩選條件: `WHERE approval_status = 'pending'`
- 排序: `ORDER BY created_at DESC`（最新的在前）
- 關聯查詢: 可能需要關聯 salesperson_profiles 以顯示業務員資訊

---

### 4.3 權限需求

| 角色 | 可執行操作 | 限制條件 |
|-----|-----------|---------|
| Admin | 查看所有待審核項目 | 需要登入且角色為 admin |
| User | 無權限 | 一般業務員無法訪問此 API |

**權限檢查**:
- API 必須使用 `auth:sanctum` middleware
- 必須使用 `role:admin` middleware（假設有角色檢查）
- 返回 403 如果使用者不是管理員

---

### 4.4 UI/UX 需求

#### 頁面結構
Frontend 已經存在以下結構，無需修改：
- 4 個頁籤: Users, Companies, Certifications, Experiences
- 每個頁籤顯示對應的待審核項目列表
- 空狀態 UI 組件

#### 空狀態提示文字
- **工作經驗**: "目前沒有待審核的工作經驗"
- **證照**: "目前沒有待審核的證照"
- **公司**: "目前沒有待審核的公司"
- **業務員**: "目前沒有待審核的業務員"

#### 操作回饋
- **審核成功**: 顯示 Toast 訊息 "審核成功"，並刷新列表
- **審核失敗**: 顯示 Toast 訊息 "審核失敗：{錯誤原因}"
- **API 錯誤**: 顯示 Toast 訊息 "載入失敗，請稍後再試"

---

## 5. 邊界情境處理

### 異常情況處理

| 情境 | 系統行為 | 錯誤訊息 |
|-----|---------|---------|
| 所有類別都沒有待審核項目 | 返回所有空陣列 `[]` | Frontend 顯示空狀態 |
| 資料庫連線失敗 | 返回 500 錯誤 | "伺服器錯誤，請稍後再試" |
| 使用者無權限 | 返回 403 錯誤 | "您沒有權限執行此操作" |
| Token 過期 | 返回 401 錯誤 | "登入已過期，請重新登入" |

### Edge Cases

**情境 1: 大量待審核項目（> 50 筆）**
- **預期行為**: 目前不實作分頁，直接返回所有項目
- **未來優化**: 如果資料量持續增長，考慮新增分頁功能
- **注意**: 目前預期 < 50 筆，效能影響可接受

**情境 2: 審核狀態同步問題**
- **預期行為**: 審核後立即刷新列表
- **錯誤處理**: 如果刷新失敗，顯示錯誤訊息並提供 Retry 按鈕

**情境 3: 並發審核**
- **預期行為**: 使用樂觀鎖定或版本控制（如果有的話）
- **錯誤處理**: 如果項目已被其他管理員審核，返回 409 錯誤

**情境 4: 資料關聯缺失**
- **預期行為**: 如果 experience 關聯的 salesperson_profile 已被刪除，仍然返回該 experience
- **顯示處理**: Frontend 顯示 "業務員資料已刪除" 或類似提示

---

## 6. 技術考量

### 技術限制
- Laravel 11 + MySQL
- 必須使用 Eloquent ORM
- 遵循現有的程式碼風格和架構

### 效能考量
- **預期資料量**: < 50 筆待審核項目
- **API 回應時間**: < 200ms（簡單查詢標準）
- **資料庫查詢**: 避免 N+1 問題，使用 Eager Loading 如果需要關聯資料
- **快取策略**: 目前不需要快取（資料即時性要求高）

### 安全性考量
- **認證**: 使用 `auth:sanctum` middleware
- **授權**: 確保只有 Admin 可以訪問
- **輸入驗證**: API 不接受任何輸入參數，無需驗證
- **SQL Injection**: 使用 Eloquent ORM，自動防護

### 資料庫索引
確保以下欄位有索引以提升查詢效能：
- `experiences.approval_status`
- `certifications.approval_status`
- `experiences.created_at`
- `certifications.created_at`

---

## 7. 驗收標準

### 功能驗收
- [ ] **API 測試**: 呼叫 `GET /api/admin/pending-approvals`，返回包含 4 類資料
- [ ] **狀態過濾**: 所有返回的項目 `approval_status` 必須為 'pending'
- [ ] **空狀態**: 當沒有待審核項目時，返回空陣列 `[]`
- [ ] **Frontend 整合**: Frontend 能正確顯示工作經驗和證照頁籤
- [ ] **空狀態 UI**: Frontend 正確顯示空狀態提示文字
- [ ] **審核流程**: 審核後列表正確更新

### 非功能驗收
- [ ] **效能**: API 回應時間 < 200ms（在 < 50 筆資料的情況下）
- [ ] **安全性**: 通過權限檢查測試（非 Admin 返回 403）
- [ ] **程式碼品質**: PHPStan Level 9 無錯誤
- [ ] **測試覆蓋率**: 新增程式碼測試覆蓋率 >= 95%
- [ ] **相容性**: 不影響現有的 companies 和 profiles 查詢

### 測試資料驗證
- [ ] 建立至少 3 筆 pending 狀態的 experiences 測試資料
- [ ] 建立至少 3 筆 pending 狀態的 certifications 測試資料
- [ ] 建立至少 2 筆 approved 狀態的 experiences（確保不會被返回）
- [ ] 建立至少 2 筆 rejected 狀態的 certifications（確保不會被返回）

---

## 8. 風險與依賴

### 潛在風險

**風險 1: 資料庫效能問題**
- **描述**: 如果待審核項目數量超過預期（> 100 筆），可能影響查詢效能
- **機率**: 低
- **影響**: 中
- **緩解措施**:
  - 確保資料庫索引正確建立
  - 監控 API 回應時間
  - 如果發生效能問題，後續新增分頁功能

**風險 2: Frontend 資料格式不符**
- **描述**: Backend 返回的資料格式可能與 Frontend 預期不完全一致
- **機率**: 低
- **影響**: 高
- **緩解措施**:
  - 仔細檢查 Frontend 程式碼，確認預期的資料結構
  - 使用 TypeScript 型別定義確保一致性
  - 先在開發環境測試整合

**風險 3: 測試資料不足**
- **描述**: 目前所有 experiences 都是 approved 狀態，缺少 pending 測試資料
- **機率**: 高
- **影響**: 中
- **緩解措施**:
  - 建立專門的測試資料 Seeder
  - 在開發環境建立充足的測試資料

### 依賴項目
- 依賴 Laravel Eloquent ORM
- 依賴 MySQL 資料庫
- 依賴 Frontend React Query（用於 API 呼叫和快取）
- 依賴現有的認證和授權系統

---

## 9. 實作計劃

### Phase 1: Backend API 修改（1 小時）
- [ ] 修改 `AdminController::pendingApprovals()` 方法
- [ ] 新增 experiences 查詢
- [ ] 新增 certifications 查詢
- [ ] 確保過濾條件正確（`approval_status = 'pending'`）

### Phase 2: 測試資料建立（30 分鐘）
- [ ] 建立 `PendingApprovalsTestDataSeeder`
- [ ] 建立 pending 狀態的 experiences 測試資料
- [ ] 建立 pending 狀態的 certifications 測試資料
- [ ] 建立 approved/rejected 狀態的測試資料（用於驗證過濾）

### Phase 3: 測試撰寫與驗證（1 小時）
- [ ] 撰寫 Feature Test: `AdminPendingApprovalsTest`
- [ ] 測試 API 返回正確的資料結構
- [ ] 測試過濾邏輯正確
- [ ] 測試權限檢查
- [ ] 測試空狀態處理

### Phase 4: Frontend 整合測試（30 分鐘）
- [ ] 在開發環境啟動 Frontend
- [ ] 訪問 `/admin/approvals` 頁面
- [ ] 驗證 4 個頁籤都能正常顯示資料
- [ ] 驗證空狀態 UI 正常運作
- [ ] 測試審核流程

### Phase 5: 文檔更新與歸檔（15 分鐘）
- [ ] 更新 API 文檔（OpenAPI 規格）
- [ ] 歸檔規格到 `openspec/specs/backend/`
- [ ] 更新 CHANGELOG

**預估總時間**: 3-3.5 小時

---

## 10. 附錄

### 參考資料
- Backend 程式碼: `my_profile_laravel/app/Http/Controllers/Api/AdminController.php`
- Frontend 程式碼: `frontend/app/admin/approvals/page.tsx`
- API Client: `frontend/lib/api.ts`
- 資料庫 Schema: `my_profile_laravel/database/migrations/`

### 相關問題
- Issue #XXX: "待審核項目頁面工作經驗和證照頁籤為空"（如果有的話）

### 未來規劃
- **分頁功能**: 如果待審核項目數量持續增長（> 100 筆），新增分頁支援
- **即時通知**: 當有新的待審核項目時，推送通知給管理員
- **批次審核**: 允許管理員一次審核多個項目
- **審核歷史**: 記錄審核操作的歷史紀錄（誰在什麼時候審核的）
- **篩選與排序**: 新增按日期、類型、業務員篩選的功能

---

## 11. 開發範圍判斷

### 結論: **純 Backend 開發**

**理由**:
1. ✅ **Frontend 已準備好**: Frontend 程式碼已經預期接收 `experiences` 和 `certifications` 資料
2. ✅ **Frontend 無需修改**: 所有 UI 組件（列表、空狀態、審核按鈕）都已存在
3. ✅ **問題根源在 Backend**: API 未返回必要資料，導致 Frontend 無法顯示

### 修改範圍
- **Backend**: ✅ 需要修改
  - 修改 `AdminController::pendingApprovals()` 方法
  - 新增測試資料 Seeder
  - 撰寫 Feature Tests

- **Frontend**: ❌ 不需要修改
  - 現有程式碼已支援
  - 僅需驗證整合是否正常

### 開發建議
使用 `/implement` 命令進行 Backend 開發：
```bash
/implement 修復待審核項目 API 缺少工作經驗與證照資料
```

這將自動執行完整的 Backend SDD 流程：
1. 建立詳細規格（API、Tests）
2. 實作 Backend 程式碼
3. 執行測試驗證
4. 歸檔規格

---

**文件版本**: 1.0
**建立日期**: 2026-01-20
**最後更新**: 2026-01-20
**作者**: Product Manager (Claude)
**審核者**: 待確認
