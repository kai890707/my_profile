---
category: workflow
tags: [specification, validation, quality, sdd]
priority: high
last_updated: 2026-01-13
applies_to: All specification documents
related_docs: [sdd-process.md, requirements-checklist.md, metrics-standards.md]
---

# 規格驗證檢查清單

## Quick Reference

- 使用時機: 規格撰寫完成後、實作開始前
- 驗證對象: API 規格、DB Schema、UI/UX 規格、測試規格
- 目標: 確保規格完整、具體、可測試、無歧義
- 輸出: 驗證報告 `specs/validation-report.md`
- 通過標準: 所有檢查項目 ✅

## 使用場景

**適用於**:
- 所有 Backend 規格驗證
- 所有 Frontend 規格驗證
- 重大架構變更的規格驗證

**不適用於**:
- 簡單 Bug 修復（沒有規格文件）
- 文案修改（不需要規格）

## 核心概念

規格驗證是 SDD 流程中的關鍵檢查點，確保在投入實作資源前，規格已經達到「可直接實作」的品質。

**驗證三原則**:
1. **完整性** - 所有必要資訊都有
2. **具體性** - 沒有模糊或歧義
3. **可測試性** - 可以直接轉換為測試用例

## API 規格驗證

### 完整性檢查

#### 端點定義
- [ ] URL 路徑明確（包含版本號）
- [ ] HTTP 方法明確（GET, POST, PUT, PATCH, DELETE）
- [ ] 認證要求明確（JWT, API Key, etc.）
- [ ] 授權要求明確（角色、權限）

**範例**:
```yaml
✅ 好的端點定義
POST /api/v1/ratings
Authentication: Required (JWT)
Authorization: salesperson role
Rate Limit: 100 requests/min

❌ 不完整的定義
POST /ratings
需要登入
```

#### Request 規格
- [ ] 所有 Request 參數都有定義
- [ ] 每個參數都有資料類型
- [ ] 每個參數都標註必填或可選
- [ ] 驗證規則明確且具體
- [ ] 有完整的 Request Body 範例

**驗證標準**:
```json
✅ 完整的 Request 規格
{
  "rating": {
    "type": "integer",
    "required": true,
    "min": 1,
    "max": 5,
    "description": "評分，1-5 星"
  },
  "comment": {
    "type": "string",
    "required": false,
    "max_length": 500,
    "description": "評論內容，最多 500 字"
  }
}

範例 Request:
{
  "rating": 5,
  "comment": "服務態度很好"
}

❌ 不完整的規格
- rating: 必填
- comment: 可選
```

#### Response 規格
- [ ] 成功回應的格式明確
- [ ] 成功回應有完整範例
- [ ] 所有錯誤情況都有定義
- [ ] 錯誤回應格式一致（RFC 7807）
- [ ] 狀態碼正確使用

**檢查項目**:
```yaml
✅ 完整的 Response 規格

成功回應 (201 Created):
{
  "data": {
    "id": 123,
    "rating": 5,
    "comment": "服務態度很好",
    "created_at": "2026-01-13T10:00:00Z"
  }
}

錯誤回應:
- 400 Bad Request: 參數格式錯誤
- 401 Unauthorized: 未登入
- 403 Forbidden: 已評分過（每人限評分一次）
- 404 Not Found: 業務員不存在
- 422 Unprocessable Entity: 驗證失敗
- 429 Too Many Requests: 超過頻率限制

每個錯誤都有範例:
{
  "type": "https://yamu.com/errors/duplicate-rating",
  "title": "Duplicate Rating",
  "status": 403,
  "detail": "You have already rated this salesperson."
}
```

### 具體性檢查

#### 驗證規則具體化
- [ ] 不只寫 "required"，要寫具體的驗證規則
- [ ] 數字範圍明確（min, max）
- [ ] 字串長度明確（min_length, max_length）
- [ ] 格式要求明確（email, url, phone, date）
- [ ] 自訂規則有清楚說明

**對比範例**:
```markdown
❌ 模糊的驗證規則
- email: 必填，email 格式
- phone: 必填，手機格式

✅ 具體的驗證規則
- email: 必填，RFC 5322 格式，最大長度 255
- phone: 必填，台灣手機格式 09XX-XXX-XXX 或 09XXXXXXXX，10 碼數字
- password: 必填，最小長度 8，必須包含大小寫字母和數字
```

#### 回應格式一致性
- [ ] 所有端點使用相同的 data wrapper
- [ ] 分頁格式統一（meta, links）
- [ ] 錯誤格式統一（RFC 7807）
- [ ] 日期時間格式統一（ISO 8601）

**範例**:
```json
✅ 統一的回應格式

單一資源:
{ "data": { ... } }

集合資源:
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  },
  "links": {
    "first": "...",
    "last": "...",
    "next": "...",
    "prev": null
  }
}

錯誤回應（RFC 7807）:
{
  "type": "...",
  "title": "...",
  "status": 422,
  "detail": "...",
  "errors": { ... }
}
```

#### 分頁參數明確
- [ ] 分頁方式明確（page-based, cursor-based）
- [ ] 預設值明確（預設第 1 頁，每頁 15 筆）
- [ ] 最大值明確（最多 100 筆/頁）
- [ ] 排序參數明確

### 可測試性檢查

#### 測試用例覆蓋
- [ ] 每個端點都有測試用例
- [ ] 包含正常情況測試
- [ ] 包含驗證失敗測試
- [ ] 包含授權失敗測試
- [ ] 包含邊界條件測試

**測試用例範例**:
```markdown
POST /api/v1/ratings 測試用例:

正常情況:
- [ ] 可以成功建立評分（valid rating + comment）
- [ ] 可以建立評分不含評論（valid rating, no comment）

驗證測試:
- [ ] rating 必填
- [ ] rating 必須是 1-5
- [ ] rating 為 0 時拒絕
- [ ] rating 為 6 時拒絕
- [ ] comment 超過 500 字時拒絕

授權測試:
- [ ] 未登入用戶無法評分（401）
- [ ] 用戶重複評分被拒絕（403）

邊界測試:
- [ ] comment 剛好 500 字可以通過
- [ ] 評分對象不存在（404）
- [ ] rating 為 1（最小值）
- [ ] rating 為 5（最大值）
```

#### 範例可直接使用
- [ ] Request 範例可以直接用於 API 測試
- [ ] Response 範例是真實可能的回應
- [ ] 範例涵蓋所有必填欄位
- [ ] 範例符合驗證規則

## DB Schema 驗證

### 完整性檢查

#### 資料表定義
- [ ] 表名使用複數形式
- [ ] 所有欄位都有資料類型
- [ ] 所有欄位都有長度/精度定義
- [ ] 所有欄位都標註 NULL/NOT NULL
- [ ] 有主鍵定義
- [ ] 有時間戳（created_at, updated_at）

**範例**:
```sql
✅ 完整的 Table 定義
CREATE TABLE ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    salesperson_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salesperson_id) REFERENCES salespersons(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_salesperson (user_id, salesperson_id),
    INDEX idx_salesperson_id (salesperson_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

❌ 不完整的定義
ratings table:
- id
- user_id
- rating
- comment
```

#### 關係定義
- [ ] 所有外鍵關係明確定義
- [ ] 外鍵的級聯行為明確（CASCADE, SET NULL, RESTRICT）
- [ ] 多對多關係有中間表
- [ ] 關係方向明確（belongsTo, hasMany, etc.）

**Laravel Eloquent 範例**:
```php
✅ 明確的關係定義

class Rating extends Model
{
    // 屬於哪個用戶
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 屬於哪個業務員
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }
}

class Salesperson extends Model
{
    // 擁有多個評分
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    // 計算平均評分（Accessor）
    public function getAverageRatingAttribute(): float
    {
        return $this->ratings()->avg('rating') ?? 0;
    }
}
```

#### 索引策略
- [ ] 查詢欄位都有索引
- [ ] 外鍵都有索引
- [ ] 唯一約束使用 UNIQUE INDEX
- [ ] 複合索引順序正確（最左前綴原則）

**索引檢查**:
```sql
✅ 完整的索引策略

PRIMARY KEY: id
FOREIGN KEY + INDEX: user_id, salesperson_id
UNIQUE INDEX: (user_id, salesperson_id) -- 複合唯一約束
INDEX: created_at -- 排序查詢
INDEX: salesperson_id -- WHERE 查詢

❌ 缺少索引
只有 PRIMARY KEY，沒有其他索引
```

### 效能檢查

#### 查詢效能
- [ ] 查詢欄位都有索引
- [ ] 避免 SELECT * （只查詢需要的欄位）
- [ ] Eager Loading 避免 N+1 問題
- [ ] 大表有分頁策略

**N+1 問題預防**:
```php
❌ N+1 問題
$salespersons = Salesperson::all(); // 1 query
foreach ($salespersons as $sp) {
    echo $sp->user->name; // N queries
}

✅ 使用 Eager Loading
$salespersons = Salesperson::with('user')->get(); // 2 queries total
foreach ($salespersons as $sp) {
    echo $sp->user->name; // No additional queries
}
```

#### 資料類型優化
- [ ] 使用適當的整數類型（TINYINT, SMALLINT, INT, BIGINT）
- [ ] 字串長度合理（VARCHAR 避免過大）
- [ ] ENUM 用於固定選項
- [ ] 布林值使用 BOOLEAN/TINYINT

**範例**:
```sql
✅ 優化的資料類型
rating TINYINT UNSIGNED        -- 0-255，夠用且省空間
status ENUM('active', 'inactive')  -- 固定選項
is_featured BOOLEAN            -- 布林值

❌ 未優化的類型
rating INT                     -- 過大，浪費空間
status VARCHAR(255)            -- 固定選項不需要這麼大
is_featured VARCHAR(10)        -- 布林值不應該用字串
```

### 資料完整性檢查

#### 約束定義
- [ ] 唯一約束明確定義
- [ ] CHECK 約束（如適用）
- [ ] 外鍵約束和級聯行為
- [ ] DEFAULT 值合理設定

**範例**:
```sql
✅ 完整的約束
rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
email VARCHAR(255) NOT NULL UNIQUE,
status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',

❌ 缺少約束
rating INT,
email VARCHAR(255),
status VARCHAR(20),
```

#### 軟刪除策略
- [ ] 是否使用軟刪除已決定
- [ ] deleted_at 欄位已定義（如使用軟刪除）
- [ ] 查詢時正確處理軟刪除資料

## UI/UX 規格驗證

### 完整性檢查

#### 狀態覆蓋
- [ ] Loading 狀態有視覺設計
- [ ] Empty 狀態有視覺設計
- [ ] Error 狀態有視覺設計
- [ ] Success 狀態有視覺設計

**狀態檢查清單**:
```markdown
評分組件狀態：

Loading:
- [ ] 顯示 Skeleton 或 Spinner
- [ ] 禁用提交按鈕

Empty（無評分）:
- [ ] 顯示 "尚無評分" 訊息
- [ ] 引導用戶進行第一次評分

Error（API 失敗）:
- [ ] 顯示錯誤訊息
- [ ] 提供重試按鈕

Success（提交成功）:
- [ ] 顯示 Toast 通知
- [ ] 更新評分顯示
```

#### 互動元素
- [ ] 所有按鈕都有 Disabled 狀態
- [ ] 所有表單都有驗證回饋
- [ ] 破壞性操作有確認機制
- [ ] Hover/Focus 狀態已定義

**互動檢查**:
```typescript
✅ 完整的互動定義

<Button
  onClick={handleSubmit}
  disabled={!rating || isSubmitting}  // Disabled 狀態
  loading={isSubmitting}              // Loading 狀態
>
  提交評分
</Button>

// 表單驗證
{errors.rating && (
  <ErrorMessage>{errors.rating}</ErrorMessage>
)}

// 破壞性操作確認
const handleDelete = () => {
  if (confirm('確定要刪除評分嗎？此操作無法復原。')) {
    deleteRating();
  }
};
```

### 響應式檢查

#### 斷點覆蓋
- [ ] Mobile 佈局 (< 768px) 已定義
- [ ] Tablet 佈局 (768px - 1024px) 已定義
- [ ] Desktop 佈局 (> 1024px) 已定義
- [ ] 特殊情況（超寬螢幕）已考慮

**響應式檢查**:
```css
✅ 完整的響應式設計

/* Mobile */
@media (max-width: 767px) {
  .rating-card {
    grid-template-columns: 1fr;
    padding: 12px;
  }
}

/* Tablet */
@media (min-width: 768px) and (max-width: 1023px) {
  .rating-card {
    grid-template-columns: repeat(2, 1fr);
    padding: 16px;
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .rating-card {
    grid-template-columns: repeat(3, 1fr);
    padding: 24px;
  }
}
```

### 可訪問性檢查

#### WCAG 標準
- [ ] 顏色對比 >= 4.5:1 (AA 標準)
- [ ] 所有互動元素可鍵盤操作
- [ ] 適當的 ARIA 標籤
- [ ] 圖片有 alt 文字

**可訪問性檢查清單**:
```tsx
✅ 可訪問的組件

<button
  onClick={handleSubmit}
  aria-label="提交評分"           // ARIA 標籤
  tabIndex={0}                     // 鍵盤可達
  onKeyPress={handleKeyPress}      // 鍵盤事件
>
  提交
</button>

<img
  src={photo}
  alt="業務員照片"                 // Alt 文字
/>

// 顏色對比檢查
color: #000000;  // 黑色
background: #FFFFFF;  // 白色
// 對比度 21:1 ✅ (超過 4.5:1)
```

## 驗證報告模板

### 驗證報告結構

```markdown
# 規格驗證報告

**功能名稱**: [功能名稱]
**規格位置**: `openspec/changes/[feature-name]/specs/`
**驗證日期**: 2026-01-13
**驗證者**: Claude / [人類審查者]

## 驗證結果總覽

- 總檢查項目: X 項
- 通過項目: Y 項
- 未通過項目: Z 項
- 通過率: Y/X %
- **最終判定**: ✅ 通過 / ❌ 需修正

## API 規格驗證

### 完整性檢查 (X/Y)
- ✅ 端點定義完整
- ✅ Request 規格完整
- ❌ Response 錯誤情況不完整（缺少 429 Too Many Requests）

### 具體性檢查 (X/Y)
- ✅ 驗證規則具體
- ✅ 回應格式一致
- ✅ 分頁參數明確

### 可測試性檢查 (X/Y)
- ✅ 測試用例覆蓋完整
- ✅ 範例可直接使用

## DB Schema 驗證

### 完整性檢查 (X/Y)
- ✅ 資料表定義完整
- ✅ 關係定義明確
- ❌ 缺少 created_at 索引

### 效能檢查 (X/Y)
- ✅ 查詢欄位有索引
- ✅ 使用 Eager Loading

### 資料完整性 (X/Y)
- ✅ 約束定義完整
- ✅ 軟刪除策略明確

## UI/UX 規格驗證

### 完整性檢查 (X/Y)
- ✅ 所有狀態有視覺設計
- ✅ 互動元素完整

### 響應式檢查 (X/Y)
- ✅ 所有斷點已定義

### 可訪問性檢查 (X/Y)
- ✅ WCAG AA 標準符合

## 需要修正的項目

### 高優先級
1. **Response 錯誤情況不完整**
   - 問題: 缺少 429 Too Many Requests 定義
   - 建議: 補充 Rate Limiting 錯誤回應

2. **缺少 created_at 索引**
   - 問題: 按時間排序查詢會很慢
   - 建議: 新增 INDEX idx_created_at (created_at)

### 中優先級
（無）

### 低優先級
（無）

## 總結

規格整體品質良好，有 2 項需要修正。修正後即可進入實作階段。

預估修正時間: 10 分鐘
```

## 最佳實踐

### 驗證時機
- [ ] 規格撰寫完成後立即驗證
- [ ] 不要等到實作階段才發現問題
- [ ] 驗證未通過不進入實作

### 驗證原則
- [ ] 用「能否直接實作」作為標準
- [ ] 用「能否直接寫測試」作為標準
- [ ] 所有模糊的地方都要澄清

### 常見錯誤

**錯誤 1: 規格過於抽象**
```markdown
❌ 抽象的規格
"實作評分 API"

✅ 具體的規格
完整的 Request/Response 定義
具體的驗證規則
完整的錯誤情況
```

**錯誤 2: 缺少邊界情況**
```markdown
❌ 只有 Happy Path
測試: 可以成功建立評分

✅ 包含邊界情況
測試:
- 成功建立評分
- rating 為 0 時拒絕
- rating 為 6 時拒絕
- 重複評分被拒絕
- 未登入被拒絕
```

**錯誤 3: 範例不可用**
```markdown
❌ 不可用的範例
Request: { ... }  // 省略了欄位

✅ 可用的範例
Request:
{
  "rating": 5,
  "comment": "很好"
}
// 這個範例可以直接複製到 Postman 測試
```

## 相關知識

### 前置知識
- [SDD 流程](./sdd-process.md) - 規格驗證在流程中的位置
- [需求檢查清單](./requirements-checklist.md) - 需求階段的檢查

### 延伸閱讀
- [量化標準](./metrics-standards.md) - 具體的量化指標
- [Backend 架構](../backend/architecture.md) - 實作標準
- [Frontend 架構](../frontend/architecture.md) - UI 實作標準

### 實作流程
1. [需求檢查清單](./requirements-checklist.md) - 完成需求分析
2. [SDD 流程](./sdd-process.md) - 撰寫規格
3. [本文件] - 驗證規格
4. 進入實作階段

## 決策記錄

### 當前決策 (2026-01-13)

**建立規格驗證機制的原因**:
- 原因 1: 減少實作返工（目標：從 15% 降到 3%）
- 原因 2: 確保規格可直接轉換為代碼和測試
- 原因 3: 在低成本階段（規格）發現問題，而非高成本階段（實作）
- 原因 4: 提高規格品質，減少實作中的困惑

**驗證標準的選擇**:
- 標準 1: 能否直接實作 - 所有必要資訊都有
- 標準 2: 能否直接寫測試 - 測試用例可直接生成
- 標準 3: 沒有歧義 - 不同人理解一致

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
