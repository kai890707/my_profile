# 提案：用戶註冊流程重構

## Why - 為什麼需要這個變更？

### 問題陳述

目前系統的註冊和身份管理存在以下問題：

1. **所有業務員都需要管理員審核** - 造成註冊流程繁瑣，使用者需要等待審核才能使用完整功能
2. **無一般使用者概念** - 系統只有業務員身份，缺少輕量級的一般使用者角色
3. **公司也需要審核** - 增加管理負擔，延遲業務員的正常使用
4. **缺少靈活的身份升級機制** - 使用者無法自主選擇何時成為業務員

### 業務影響

- ❌ **使用者體驗差** - 註冊後需要等待審核才能使用
- ❌ **管理成本高** - 管理員需要手動審核所有業務員和公司
- ❌ **轉換率低** - 繁瑣的審核流程可能導致潛在使用者流失

---

## What - 我們要做什麼？

### 解決方案概述

建立**雙層級使用者系統**，支援兩種註冊方式與靈活的升級機制：

1. **註冊方式（二選一）**
   - **一般使用者註冊**：Email + 密碼即可註冊，立即獲得瀏覽功能
   - **直接註冊為業務員**：註冊時同步提交業務員資料（姓名、電話、專長等）

2. **業務員升級機制（立即生效 + 審核制）**
   - 一般使用者可隨時升級為業務員
   - **升級後立即獲得業務員功能**（建立公司、評分、儀表板、數據分析）
   - **但需審核通過才能被搜尋到**（公開業務員列表）
   - 審核狀態：`pending`（未審核）→ `approved`（已審核）或 `rejected`（已拒絕）

3. **公司建立（取消審核）**
   - 業務員建立的公司立即生效
   - 移除 AdminController 中的公司審核功能

4. **資料遷移**
   - 現有已審核的業務員全部轉為「已升級使用者」
   - 保留其所有資料和權限

### 核心變更

#### 資料模型變更

**Users Table 新增欄位**:
- `role` (enum: 'user', 'salesperson', 'admin') - 使用者角色
- `salesperson_status` (enum: null, 'pending', 'approved', 'rejected') - 業務員審核狀態
  - `null`: 一般使用者
  - `pending`: 業務員（未審核）- 有儀表板/分析功能，但不能建立公司/評分，不可被搜尋
  - `approved`: 業務員（已審核）- 完整功能，可建立公司/評分，可被搜尋
  - `rejected`: 業務員申請被拒絕，降回一般使用者
- `salesperson_applied_at` (timestamp, nullable) - 業務員申請/升級時間
- `salesperson_approved_at` (timestamp, nullable) - 業務員審核通過時間
- `rejection_reason` (text, nullable) - 審核拒絕原因（供使用者了解並改進）
- `can_reapply_at` (timestamp, nullable) - 可重新申請的時間（拒絕後設定等待期）
- `is_paid_member` (boolean, default: false) - 付費會員標記（預留未來付費升級功能）

**SalespersonProfiles Table**:
- 保持現有欄位，但關聯邏輯改為只有 `role='salesperson'` 的使用者才能擁有
- 升級/註冊為業務員後立即建立 Profile（即使 status='pending'）
- `company_id` 改為 **nullable**（支援獨立業務員，未審核業務員不關聯公司）

**Companies Table（大幅簡化）**:
- **保留欄位**：
  - `id` - 主鍵
  - `name` - 公司名稱/標題（唯一必填欄位）
  - `tax_id` (nullable + unique) - 統一編號（防重複，個人工作室為 null）
  - `is_personal` (boolean, default: false) - 是否為個人工作室
  - `created_by` - 建立者 ID
  - `created_at`, `updated_at` - 時間戳
- **移除欄位**：
  - `industry_id` - 不需要產業分類
  - `address` - 不需要地址
  - `phone` - 不需要電話
  - `approval_status` - 取消公司審核
  - `approved_at`, `approved_by` - 取消公司審核
  - `rejected_reason` - 取消公司審核

#### API Endpoints 變更

**新增**:
- `POST /api/auth/register` - 一般使用者註冊（參數：email, password）
- `POST /api/auth/register-salesperson` - 直接註冊為業務員（參數：email, password + 業務員資料）
- `POST /api/salesperson/upgrade` - 一般使用者升級為業務員（立即建立 Profile，但 company_id=null）
- `GET /api/salesperson/status` - 查詢業務員審核狀態（pending/approved/rejected）
- `GET /api/admin/salesperson-applications` - 管理員查看待審核業務員列表
- `POST /api/admin/salesperson-applications/{id}/approve` - 審核通過（可被搜尋、可建立公司）
- `POST /api/admin/salesperson-applications/{id}/reject` - 審核拒絕（降回一般使用者 + 可重新申請）
- `GET /api/salespeople` - 搜尋業務員（僅返回 status='approved' 的業務員）
- `GET /api/companies/search` - 搜尋公司（by tax_id 或 name，用於防止重複）
- `POST /api/companies` - **僅審核通過的業務員可建立**（立即生效，無需審核）
- `PUT /api/salesperson/profile` - 更新業務員資料（包括 company_id）

**移除**:
- `POST /api/admin/companies/{id}/approve` - 公司審核（不再需要）
- `POST /api/admin/companies/{id}/reject` - 公司拒絕（不再需要）

**修改**:
- `POST /api/companies` - 加入統編重複檢查（unique constraint）
- `POST /api/companies` - 需要 `salesperson_status='approved'` 權限

#### 權限差異

| 功能 | 一般使用者 | 業務員（未審核） | 業務員（已審核） | 管理員 |
|------|-----------|----------------|----------------|--------|
| 瀏覽公司 | ✅ | ✅ | ✅ | ✅ |
| 瀏覽評分 | ✅ | ✅ | ✅ | ✅ |
| **建立公司** | ❌ | **❌** | **✅** | ✅ |
| **建立評分** | ❌ | **❌** | **✅** | ✅ |
| 搜尋業務員 | ✅ | ✅ | ✅ | ✅ |
| **被搜尋到** | ❌ | **❌** | **✅** | ❌ |
| 業務員儀表板 | ❌ | ✅ | ✅ | ✅ |
| 數據分析 | ❌ | ✅ | ✅ | ✅ |
| 編輯個人 Profile | ❌ | ✅ | ✅ | ✅ |
| 加入現有公司 | ❌ | ❌ | ✅ | ✅ |
| 審核業務員申請 | ❌ | ❌ | ❌ | ✅ |

**核心差異說明（方案 A: 嚴格分離）**：
- **一般使用者** (`role='user'`): 僅瀏覽功能
- **業務員（未審核）** (`role='salesperson'`, `status='pending'`):
  - ✅ 有業務員儀表板和數據分析
  - ✅ 可編輯個人 Profile
  - ❌ **不能建立公司、評分**（防止未審核資料污染）
  - ❌ **不出現在公開業務員搜尋列表**
- **業務員（已審核）** (`role='salesperson'`, `status='approved'`):
  - ✅ 完整的業務員功能（建立公司、評分）
  - ✅ **可被搜尋到**
- **管理員** (`role='admin'`): 完整權限 + 審核功能

---

## Scope - 範圍

### In Scope ✅

1. **Backend 變更**:
   - Database migration（Users table 新增 role 和 status 欄位）
   - Database migration（Companies table 簡化：移除 industry_id, address, phone, approval_status 等）
   - User model 和 SalespersonProfile model 更新
   - Company model 更新（移除 industry, approver 關聯）
   - 新增 SalespersonApplicationController
   - 更新 AuthController（一般使用者註冊、業務員註冊）
   - 更新 AdminController（業務員審核，移除公司審核）
   - 更新 CompanyController（簡化建立邏輯，僅需 name + tax_id）
   - Middleware 權限檢查更新
   - Policy 更新（IsSalesperson, IsAdmin, CanCreateCompany）

2. **Frontend 變更**:
   - 註冊頁面更新（支援兩種註冊方式：一般使用者 / 業務員）
   - 新增「升級為業務員」頁面（一般使用者升級入口）
   - **簡化「建立公司」頁面**（僅需 2 個欄位：統編檢查 + 公司名稱，或工作室名稱）
   - 業務員審核狀態顯示（pending/approved/rejected + 說明文字）
   - 管理員審核介面（業務員申請列表、批准/拒絕操作 + 拒絕原因）
   - 業務員搜尋頁面（僅顯示 status='approved' 的業務員）
   - 移除公司審核相關 UI（公司列表不再有審核狀態）
   - 移除公司詳細資料頁面（產業、地址、電話等欄位）
   - 權限控制更新（基於 role + salesperson_status）

3. **資料遷移**:
   - 現有業務員轉為 `role='salesperson'`, `salesperson_status='approved'`
   - 現有公司移除 `approval_status`, `industry_id`, `address`, `phone`, `approved_by`, `approved_at`, `rejected_reason` 欄位
   - Companies table 加入 `is_personal` 欄位（預設 false）

4. **測試**:
   - 單元測試（Models, Controllers, Policies）
   - 整合測試（API endpoints）
   - E2E 測試（註冊流程、升級流程、審核流程）

### Out of Scope ❌

1. **此次不包含（但預留擴展空間）**:
   - **付費升級機制**（已預留 `is_paid_member` 欄位，未來實作）
   - Email 驗證功能（未來可加）
   - 業務員認證系統（證照、證書等）
   - 公司認證系統
   - 業務員分級制度（初級、中級、高級等）
   - 社交登入（Google, Facebook 等）

2. **不修改**:
   - Rating system（評分系統保持不變）
   - 現有的 API rate limiting
   - 現有的密碼加密機制

---

## Success Criteria - 成功標準

### 功能性標準

1. ✅ 一般使用者可以註冊並立即登入（無需審核）
2. ✅ 可以直接註冊為業務員（填寫完整資料，status='pending'）
3. ✅ 一般使用者可以升級為業務員（立即建立 Profile，status='pending'）
4. ✅ 業務員（未審核）可以使用儀表板和數據分析，但**不能建立公司和評分**
5. ✅ 業務員（未審核）**不會出現在公開搜尋結果**
6. ✅ 管理員可以審核業務員申請（批准/拒絕 + 填寫原因）
7. ✅ 業務員（已審核）可以建立公司和評分
8. ✅ 業務員（已審核）可以被搜尋到
9. ✅ 業務員搜尋 API 僅返回 status='approved' 的業務員
10. ✅ **公司建立流程極簡化**（僅需統編 + 名稱，或工作室名稱）
11. ✅ 公司建立前檢查統編重複（unique constraint）
12. ✅ 公司建立後立即生效（無需審核，但需 salesperson_status='approved'）
13. ✅ 拒絕後降回一般使用者，可在等待期後重新申請
14. ✅ 個人工作室可建立（tax_id = null）
15. ✅ 公司不需要產業、地址、電話等詳細資料

### 技術標準

1. ✅ 所有測試通過（100%）
2. ✅ 測試覆蓋率 ≥80%
3. ✅ PHPStan Level 9 無錯誤
4. ✅ TypeScript 編譯無錯誤
5. ✅ ESLint 0 errors
6. ✅ 資料遷移成功（現有業務員和公司正確轉換）

### 使用者體驗標準

1. ✅ 一般使用者註冊流程簡單快速（< 1 分鐘）
2. ✅ 業務員註冊/升級流程清晰（< 3 分鐘）
3. ✅ 升級後立即可使用業務員功能（無需等待審核）
4. ✅ 審核狀態清楚顯示（pending = 審核中，approved = 可被搜尋）
5. ✅ 未審核業務員看到提示：「您的業務員資料正在審核中，審核通過後即可被客戶搜尋到」
6. ✅ 權限錯誤訊息清楚（告知使用者需要業務員身份或審核狀態）

---

## Dependencies - 相依性

### 技術相依性

1. **Database**:
   - Laravel Migration 系統
   - MySQL/PostgreSQL（需支援 ENUM 或等效類型）

2. **Backend**:
   - Laravel 11 Auth 系統
   - Laravel Policies
   - Laravel Middleware

3. **Frontend**:
   - Next.js App Router
   - React Context (AuthContext)
   - TypeScript

### 資料相依性

1. **Users Table**:
   - 需要新增 4 個欄位
   - 需要遷移現有資料

2. **SalespersonProfiles Table**:
   - 需要更新關聯邏輯
   - 不需要 schema 變更

3. **Companies Table**:
   - 需要移除多個欄位（industry_id, address, phone, approval_status, approved_at, approved_by, rejected_reason）
   - 需要新增 `is_personal` 欄位
   - 需要將 `tax_id` 改為 nullable + unique
   - **大幅簡化**：從 10+ 個欄位減少到 6 個核心欄位

---

## Risks - 風險與緩解措施

### 風險 1: 資料遷移失敗

**風險等級**: 🔴 高

**描述**: 現有 201 個測試中可能有部分依賴舊的審核邏輯

**緩解措施**:
1. 建立完整的資料備份
2. 在 staging 環境先執行遷移
3. 編寫 rollback migration
4. 更新所有相關測試

### 風險 2: 權限控制漏洞

**風險等級**: 🟠 中

**描述**: role-based 權限可能有疏漏，導致一般使用者能訪問業務員功能

**緩解措施**:
1. 使用 Laravel Policy 進行集中權限管理
2. Middleware 檢查所有業務員專屬路由
3. 編寫完整的權限測試
4. Code review 重點檢查權限邏輯

### 風險 3: 現有業務員功能受影響

**風險等級**: 🟡 低

**描述**: 現有業務員在資料遷移後可能無法正常使用功能

**緩解措施**:
1. 遷移腳本將所有現有業務員設為 `salesperson_status='approved'`
2. 保留所有 SalespersonProfile 資料
3. 執行完整的 E2E 測試確認現有功能正常

### 風險 4: 公司審核移除後的品質問題

**風險等級**: 🟡 低

**描述**: 移除公司審核後可能出現低品質或虛假公司

**緩解措施**:
1. 可在未來版本加入使用者檢舉機制
2. 管理員仍可手動刪除不當公司
3. 可考慮加入公司驗證（但不在此次範圍）

---

## 公司資料模型簡化

### 設計決策

**原則**：公司僅作為「標籤/標記」，不儲存詳細資料

**理由**：
1. **業務員才是核心**：使用者搜尋的是業務員，不是公司
2. **避免資料維護負擔**：公司詳細資料容易過時（地址、電話變更）
3. **簡化建立流程**：降低業務員建立公司的門檻
4. **統編已足夠防重複**：公司唯一性由統編保證

### 簡化前後對比

| 欄位 | 舊版 | 新版 | 說明 |
|------|------|------|------|
| `id` | ✅ | ✅ | 主鍵 |
| `name` | ✅ | ✅ | 公司名稱（唯一必填） |
| `tax_id` | ✅ unique | ✅ nullable + unique | 統一編號（防重複） |
| `is_personal` | ❌ | ✅ | 新增：個人工作室標記 |
| `created_by` | ✅ | ✅ | 建立者 |
| `industry_id` | ✅ | ❌ 移除 | 不需要產業分類 |
| `address` | ✅ | ❌ 移除 | 不需要地址 |
| `phone` | ✅ | ❌ 移除 | 不需要電話 |
| `approval_status` | ✅ | ❌ 移除 | 取消公司審核 |
| `approved_by` | ✅ | ❌ 移除 | 取消公司審核 |
| `approved_at` | ✅ | ❌ 移除 | 取消公司審核 |
| `rejected_reason` | ✅ | ❌ 移除 | 取消公司審核 |
| `created_at` | ✅ | ✅ | 建立時間 |
| `updated_at` | ✅ | ✅ | 更新時間 |

**結果**：從 13 個欄位簡化到 **6 個核心欄位**

### 資料呈現

**公司列表**：
```
🏢 三商美邦人壽股份有限公司（統編：12345678）
   - 3 位業務員
   - 建立時間：2025-12-01

🏢 王小明房仲工作室（個人工作室）
   - 1 位業務員
   - 建立時間：2026-01-05
```

**業務員資料頁**：
```
👤 張三
📍 所屬公司：三商美邦人壽股份有限公司
📞 聯絡電話：0912-345-678（來自 SalespersonProfile）
🏷️  專長領域：壽險規劃、投資型保單
```

**結論**：詳細聯絡資料都在 SalespersonProfile，公司僅作為「組織歸屬」標記

---

## 公司重複防止機制

### 問題背景

**風險**：多個業務員可能建立同一家公司的重複資料
```
A業務員建立：「三商美邦人壽」(tax_id: 12345678)
B業務員建立：「三商美邦公司」  (tax_id: 12345678)
C業務員建立：「三商美邦」      (tax_id: 12345678)

→ 結果：3筆公司資料，但其實是同一家公司！
```

**影響**：
- ❌ 客戶搜尋困惑（同一家公司出現多次）
- ❌ 評分資料分散（每筆公司各自有評分）
- ❌ 統計數據錯誤

### 解決方案：統編唯一性約束

#### 1. Database 層面防護

```php
// Migration
Schema::table('companies', function (Blueprint $table) {
    $table->string('tax_id')->nullable()->unique(); // ⭐ 唯一性約束
    // unique constraint 只對非 null 值生效，多個 null 不會衝突
});
```

**規則**：
- **註冊公司**（有統編）：`tax_id` 必填且唯一，防止重複
- **個人工作室**（無統編）：`tax_id = null`，允許多筆（支援獨立業務員）

#### 2. API 層面檢查

**新增搜尋 API**：
```php
// GET /api/companies/search?tax_id=12345678
CompanyController@search

// Response (公司已存在)
{
    "exists": true,
    "company": {
        "id": 123,
        "name": "三商美邦人壽股份有限公司",
        "tax_id": "12345678",
        "industry_id": 5,
        "created_by": 456
    }
}

// Response (公司不存在)
{
    "exists": false
}
```

**建立公司 API 加入驗證（簡化版）**：
```php
// POST /api/companies
public function store(Request $request)
{
    $rules = [
        'name' => 'required|string|max:200', // ⭐ 僅需公司名稱
        'tax_id' => 'nullable|string|max:50|unique:companies', // ⭐ 唯一性驗證
        'is_personal' => 'boolean',
    ];

    // 如果是註冊公司，tax_id 必填
    if (!$request->is_personal) {
        $rules['tax_id'] = 'required|string|max:50|unique:companies';
    }

    $validated = $request->validate($rules);

    $company = Company::create([
        'name' => $validated['name'],
        'tax_id' => $validated['tax_id'] ?? null,
        'is_personal' => $validated['is_personal'] ?? false,
        'created_by' => auth()->id(),
    ]);
}
```

#### 3. Frontend 建立流程

**Step 1: 選擇公司類型**
```
□ 註冊公司（有統一編號）
□ 個人工作室（無統一編號）
```

**Step 2A: 註冊公司 - 統編檢查 + 公司名稱**
```
統一編號：[________]  [檢查]

→ 情況 A: 公司不存在
  ✅ 顯示：「此統編尚未註冊，請輸入公司名稱」

  公司名稱：[________]
  [建立公司]

→ 情況 B: 公司已存在
  ⚠️  顯示：
  「三商美邦人壽股份有限公司（統編：12345678）已存在」

  [加入此公司] → 更新 SalespersonProfile.company_id
  [這不是我的公司，重新輸入]
```

**Step 2B: 個人工作室 - 僅需名稱**
```
工作室名稱：[________]
[建立工作室]

（無需統編，直接建立）
```

**完成！公司建立流程非常簡單，僅需 2 個欄位：**
- 註冊公司：統編 + 公司名稱
- 個人工作室：工作室名稱

#### 4. 模糊搜尋輔助（降低名稱錯誤）

```php
// GET /api/companies/search?name=三商美邦
// Response
[
    {
        "id": 123,
        "name": "三商美邦人壽股份有限公司",
        "tax_id": "12345678"
    },
    {
        "id": 124,
        "name": "三商美邦人壽保險代理人股份有限公司",
        "tax_id": "87654321"
    }
]
```

**Frontend 使用**：
```
使用者輸入公司名稱時，即時顯示類似公司：

您輸入：「三商美邦」

找到類似的公司：
  ☑️ 三商美邦人壽股份有限公司（統編：12345678）
  ☑️ 三商美邦人壽保險代理人股份有限公司（統編：87654321）

是否為以上其中一家？
  [是，加入此公司]  [否，建立新公司]
```

#### 5. 未來擴展：管理員合併功能

**適用情況**：已產生重複資料，需要事後合併

```php
// POST /api/admin/companies/merge
public function mergeCompanies(Request $request)
{
    $sourceId = $request->source_id;  // 要被合併的公司
    $targetId = $request->target_id;  // 保留的公司

    DB::transaction(function () use ($sourceId, $targetId) {
        // 1. 將所有業務員轉移
        SalespersonProfile::where('company_id', $sourceId)
            ->update(['company_id' => $targetId]);

        // 2. 將所有評分轉移（如果有關聯）
        // Rating::where('company_id', $sourceId)->update(...);

        // 3. 記錄合併日誌
        CompanyMergeLog::create([
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'merged_by' => auth()->id(),
        ]);

        // 4. 刪除來源公司
        Company::find($sourceId)->delete();
    });
}
```

### 成功標準

1. ✅ Database 層面：同一統編只能存在一筆公司資料
2. ✅ Frontend：建立前自動檢查統編，顯示既有公司
3. ✅ 使用者可加入既有公司（更新 SalespersonProfile.company_id）
4. ✅ 個人工作室可多筆存在（tax_id = null）
5. ✅ API 返回清楚的錯誤訊息（統編重複時）

---

## 審核拒絕處理機制（方案 A: 溫和拒絕 + 重新申請）

### 拒絕後的處理流程

#### 1. 資料變更

```php
// AdminController@reject
public function rejectSalespersonApplication(Request $request, int $userId)
{
    $validated = $request->validate([
        'rejection_reason' => 'required|string|max:500',
        'can_reapply_days' => 'integer|min:0|max:90', // 預設 7 天
    ]);

    $user = User::findOrFail($userId);
    $user->update([
        'role' => 'user', // ⭐ 降回一般使用者
        'salesperson_status' => 'rejected',
        'rejection_reason' => $validated['rejection_reason'],
        'can_reapply_at' => now()->addDays($validated['can_reapply_days'] ?? 7),
    ]);

    // SalespersonProfile 保留（不刪除），供重新申請時參考
}
```

#### 2. 權限變更

**拒絕後立即生效**：
- ❌ 失去業務員儀表板訪問權限
- ❌ 失去數據分析功能
- ✅ 保留瀏覽公司、評分權限（降回一般使用者）
- ⚠️ SalespersonProfile 資料保留但不公開

#### 3. 重新申請機制

```php
// SalespersonController@reapply
public function reapply(Request $request)
{
    $user = auth()->user();

    // 檢查是否可重新申請
    if ($user->salesperson_status === 'rejected') {
        if ($user->can_reapply_at && $user->can_reapply_at->isFuture()) {
            return response()->json([
                'error' => '請於 ' . $user->can_reapply_at->format('Y-m-d') . ' 後重新申請',
                'can_reapply_at' => $user->can_reapply_at,
            ], 429);
        }
    }

    // 允許重新申請
    $validated = $request->validate([
        'full_name' => 'required|string',
        'phone' => 'required|string',
        'bio' => 'nullable|string',
        'specialties' => 'nullable|string',
        // ...
    ]);

    // 更新/建立 SalespersonProfile
    $user->salespersonProfile()->updateOrCreate(
        ['user_id' => $user->id],
        $validated
    );

    // 重置為 pending
    $user->update([
        'role' => 'salesperson',
        'salesperson_status' => 'pending',
        'salesperson_applied_at' => now(),
        'rejection_reason' => null,
        'can_reapply_at' => null,
    ]);
}
```

#### 4. Frontend 顯示

**業務員申請被拒絕時**：
```
❌ 業務員申請未通過

拒絕原因：
個人資料不完整，請補充以下項目：
- 聯絡電話格式錯誤
- 專業證照未上傳
- 服務區域未填寫

您可以：
✅ 修改資料後重新申請（7 天後：2026-01-17）
✅ 查看申請記錄

[修改資料並重新申請]
```

**重新申請頁面**：
```
重新申請業務員資格

上次拒絕原因：
「個人資料不完整，請補充聯絡電話和專業證照」

請修改以下資料：
姓名：[張三]
電話：[__________] ⚠️ 上次未填寫
專業證照：[上傳檔案] ⚠️ 上次未上傳
專長領域：[________]
服務區域：[________] ⚠️ 上次未填寫

[提交重新審核]
```

### 拒絕類型與等待期設定

| 拒絕原因 | 等待期 | 說明 |
|---------|-------|------|
| 資料不完整 | 0-7 天 | 立即或短期內可重新申請 |
| 資料格式錯誤 | 0 天 | 立即修改後可重新申請 |
| 資料疑似造假 | 30 天 | 較長等待期 |
| 重複申請 | 7 天 | 防止短時間內重複提交 |

**管理員可自訂等待期**（彈性處理）

### 成功標準

1. ✅ 拒絕後使用者降回一般使用者（失去業務員功能）
2. ✅ SalespersonProfile 資料保留（供重新申請參考）
3. ✅ 顯示清楚的拒絕原因
4. ✅ 等待期後可重新申請
5. ✅ 重新申請前檢查等待期（API 層面阻擋）
6. ✅ 前端清楚顯示下次可申請時間

---

## 業務員可見性控制機制

### 核心邏輯

**業務員搜尋 API (`GET /api/salespeople`)**:
```php
// 僅返回已審核通過的業務員
User::where('role', 'salesperson')
    ->where('salesperson_status', 'approved')
    ->with('salespersonProfile')
    ->get();
```

### 審核狀態說明

| 狀態 | 英文 | 有業務員功能 | 可被搜尋 | 可建立公司/評分 | 說明 |
|------|------|------------|---------|---------------|------|
| 未審核 | `pending` | 部分 | ❌ | ❌ | 有儀表板和分析功能，但**不能建立公司和評分** |
| 已審核 | `approved` | ✅ | ✅ | ✅ | 完整業務員功能，可建立公司、評分，且可被客戶搜尋到 |
| 已拒絕 | `rejected` | ❌ | ❌ | ❌ | 降回一般使用者，7天後可重新申請 |

### 未審核業務員的使用者提示

在前端顯示狀態 badge 和說明文字：

```
🟡 審核中（預計 1-3 個工作天）

您的業務員資料正在審核中。

✅ 目前您可以：
  - 瀏覽所有公司和評分
  - 使用業務員儀表板
  - 使用數據分析工具
  - 編輯個人資料

⏳ 審核通過後即可：
  - 建立和管理公司
  - 發布評分和評論
  - 出現在業務員搜尋列表（被客戶找到）

📋 加速審核提示：
  - 確保個人資料完整（姓名、電話、專長）
  - 上傳專業證照（如適用）
  - 填寫服務區域
```

---

## Implementation Plan - 實作計畫

### Phase 1: Backend 基礎架構（預估 6-8 tasks）

1. Database migration（role, salesperson_status 欄位）
2. Model 更新（User, SalespersonProfile）
3. Policy 更新（IsSalesperson, IsAdmin）
4. Middleware 更新（auth, salesperson）

### Phase 2: Backend API（預估 8-10 tasks）

1. AuthController 更新（一般使用者註冊）
2. SalespersonApplicationController（申請、查詢狀態）
3. AdminController 更新（業務員審核，移除公司審核）
4. CompanyController 更新（移除審核邏輯）

### Phase 3: Frontend（預估 10-12 tasks）

1. 註冊頁面更新
2. 業務員申請頁面
3. 申請狀態顯示元件
4. 管理員審核介面
5. 權限控制更新（路由、元件）

### Phase 4: 資料遷移與測試（預估 6-8 tasks）

1. 資料遷移腳本
2. 單元測試更新
3. 整合測試更新
4. E2E 測試

---

## Alternatives Considered - 其他方案

### 方案 A: 完全取消審核（未採用）

**描述**: 所有使用者註冊後即為業務員，無需任何審核

**優點**:
- 最簡單的實作
- 無需審核流程

**缺點**:
- ❌ 無法控制業務員品質
- ❌ 可能出現大量虛假業務員
- ❌ 無法區分一般使用者和業務員

### 方案 B: 保持現有審核制度（未採用）

**描述**: 維持現有的業務員和公司都需要審核的機制

**優點**:
- 無需任何程式碼變更
- 業務員品質可控

**缺點**:
- ❌ 使用者體驗差
- ❌ 管理成本高
- ❌ 不符合業務需求

### 方案 C: 自動審核（未採用）

**描述**: 業務員申請後自動通過，但保留審核機制以便未來使用

**優點**:
- 使用者體驗好
- 保留審核彈性

**缺點**:
- ❌ 與方案 A 差異不大
- ❌ 增加不必要的複雜度

---

## Open Questions - 待確認問題

1. ✅ **註冊方式** - 支援兩種：一般使用者註冊 OR 直接註冊為業務員
2. ✅ **業務員升級機制（方案 A: 嚴格分離）** - 升級後有儀表板/分析功能，但需審核通過才能建立公司/評分和被搜尋
3. ✅ **權限差異** - 業務員（未審核）有部分功能但不能建立公開內容，業務員（已審核）有完整功能
4. ✅ **公司審核** - 取消公司審核，但僅審核通過的業務員可建立公司（立即生效）
5. ✅ **公司重複防止** - 統編唯一性約束 + 前端建立前檢查，支援個人工作室（tax_id = null）
6. ✅ **審核拒絕處理** - 降回一般使用者 + 保留資料 + 7天後可重新申請
7. ✅ **資料遷移** - 現有業務員全部轉為 `role='salesperson'`, `status='approved'`
8. ✅ **未來擴展** - 預留 `is_paid_member` 欄位供未來付費升級功能使用

**無待確認問題** - 所有需求已明確

---

## Timeline - 時程估算

**總計**: 預估 30-38 個 tasks

- Phase 1: Backend 基礎架構（6-8 tasks）
- Phase 2: Backend API（8-10 tasks）
- Phase 3: Frontend（10-12 tasks）
- Phase 4: 資料遷移與測試（6-8 tasks）

**AUTO-RUN 模式**: 預估 25-30 分鐘完成所有自動實作

---

## References - 參考資料

### 相關文檔

- `.claude/workflows/GIT_FLOW.md` - Git Flow 工作流程
- `.claude/commands/feature-finish.md` - 品質門檻要求
- `.claude/skills/php-pro/SKILL.md` - Laravel 最佳實踐

### 現有程式碼

- `my_profile_laravel/app/Models/User.php` - User model
- `my_profile_laravel/app/Models/SalespersonProfile.php` - SalespersonProfile model
- `my_profile_laravel/app/Http/Controllers/Api/AdminController.php` - Admin controller
- `my_profile_laravel/app/Http/Controllers/Api/AuthController.php` - Auth controller
- `my_profile_laravel/app/Policies/` - 現有 Policies

---

**建立時間**: 2026-01-10
**最後更新**: 2026-01-10
**狀態**: ✅ 已確認，準備進入 Step 2-3（規格撰寫和任務規劃）

**核心決策**：
- ✅ 採用方案 A（嚴格分離）：未審核業務員不能建立公開內容
- ✅ **公司資料極簡化**：僅需名稱 + 統編（或工作室名稱），移除產業/地址/電話等欄位
- ✅ 公司重複防止：統編唯一性約束 + 前端檢查
- ✅ 審核拒絕：降回一般使用者 + 溫和重新申請機制
- ✅ 支援個人工作室（tax_id = null）
