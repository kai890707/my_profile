# Proposal: 改進業務員公司選擇的 UX

**提案編號**: 20260118-improve-company-selection
**提案日期**: 2026-01-18
**優先級**: High - 本週內完成
**提案人**: Product Manager
**狀態**: Pending Review

---

## 1. 背景與目標

### 1.1 業務背景

目前業務員註冊流程存在嚴重的 UX 問題，導致業務員無法順利完成註冊：

**現有問題**：
1. ❌ **讓業務員輸入「公司 ID」** - 業務員不會知道也無法取得公司的內部 ID
2. ❌ **自營業者沒有填寫名稱欄位** - 缺少工作室/個人營業名稱的輸入介面
3. ❌ **無法搜尋公司** - 業務員只能盲目輸入 ID，無法透過公司名稱查找

**業務影響**：
- 業務員註冊流程中斷，無法完成檔案建立
- 自營業者無法正確填寫營業資訊
- 用戶體驗極差，可能導致註冊放棄

### 1.2 目標使用者

**主要使用者**：
- **業務員（受雇於公司）**：需要搜尋並選擇任職公司
- **自營業者（個人工作室）**：需要填寫個人營業名稱

**使用情境**：
- 新業務員註冊時填寫公司資訊
- 業務員變更任職公司（未來功能）

### 1.3 成功指標

**量化指標**：
- 業務員註冊完成率 >= 95%（目前因公司選擇問題可能 < 50%）
- 公司搜尋回應時間 < 500ms（P95）
- 自營業者資料完整率 = 100%（營業名稱必填）
- 表單錯誤率 < 5%（透過即時驗證降低）

**質化指標**：
- 使用者不需要詢問「公司 ID 是什麼」
- 搜尋介面直覺易用
- 自營業者與公司業務員的流程清晰區分

---

## 2. 功能描述

### 2.1 核心功能

#### 功能 1: 公司搜尋選擇（受雇業務員）

**描述**：業務員可透過「公司名稱」或「統一編號」搜尋並選擇任職公司

**主要流程**：
1. 使用者選擇「我任職於某家公司」
2. 在 Autocomplete 搜尋框輸入公司名稱或統編（名稱優先）
3. 系統即時搜尋（Debounce 300ms）並顯示結果清單
4. 使用者從清單中選擇公司
5. 表單自動填入公司資訊

**搜尋邏輯**：
- **公司名稱**：模糊搜尋（LIKE %keyword%）
- **統一編號**：精確匹配（= keyword）
- **結果限制**：最多顯示 10 筆
- **排序**：精確匹配優先，再依公司名稱排序

**結果呈現**：
```
┌─────────────────────────────────────┐
│ 🏢 台灣科技股份有限公司              │
│    統編：12345678                    │
├─────────────────────────────────────┤
│ 🏢 台灣科技有限公司                  │
│    統編：87654321                    │
├─────────────────────────────────────┤
│ ➕ 找不到公司？點此新增              │
└─────────────────────────────────────┘
```

#### 功能 2: 新增公司（搜尋不到時）

**描述**：當業務員搜尋不到公司時，可自行新增公司資料

**新增流程**：
1. 使用者點擊「找不到公司？點此新增」
2. 開啟新增公司對話框（Dialog）
3. 填寫公司資訊：
   - 公司名稱（必填）
   - 統一編號（可選，8 位數字驗證）
4. 提交後立即建立公司並自動選擇

**驗證規則**：
- 公司名稱：1-255 字元，不可空白
- 統一編號：8 位數字（可選）
- 重複檢查：名稱+統編組合不可重複

**成功回饋**：
- Toast 提示：「公司新增成功」
- 自動選擇剛建立的公司
- 關閉對話框

#### 功能 3: 自營業者營業名稱（個人工作室）

**描述**：自營業者可填寫個人營業名稱（工作室名稱或個人姓名）

**實作方式**：採用 **個人公司（Personal Company）** 模型
- 建立一筆 `companies` 記錄，設定 `type = 'personal'`
- `name` 欄位儲存營業名稱
- `tax_id` 為 null（個人工作室通常無統編）
- `salesperson_profiles.company_id` 關聯到此記錄

**填寫流程**：
1. 使用者選擇「我是自營業者」
2. 輸入營業名稱（必填）
   - 範例：「王小明設計工作室」、「李大華個人工作室」
3. 系統自動建立 `is_personal: true` 的公司記錄
4. 關聯到業務員檔案

**驗證規則**：
- 營業名稱：必填，1-255 字元

#### 功能 4: 公司/自營切換確認

**描述**：當使用者在「公司業務員」與「自營業者」間切換時，顯示確認對話框

**切換情境 1：公司 → 自營**
```
⚠️ 確認切換為自營業者？

切換後，您先前選擇的公司資訊將被清除。

[取消]  [確認切換]
```

**切換情境 2：自營 → 公司**
```
⚠️ 確認切換為公司業務員？

切換後，您的自營業者名稱將被清除。

[取消]  [確認切換]
```

**行為**：
- 使用者確認後才執行切換
- 清除原欄位資料
- 顯示對應的輸入介面

### 2.2 使用情境範例

#### 情境 1: 受雇業務員搜尋公司

**使用者**：張小明，任職於「台灣科技股份有限公司」
**流程**：
1. 張小明選擇「我任職於某家公司」
2. 在搜尋框輸入「台灣科技」
3. 系統顯示 3 筆搜尋結果（包含統編）
4. 張小明選擇正確的公司（統編：12345678）
5. 公司資訊自動填入，完成選擇

**預期結果**：
- `salesperson_profiles.company_id = 42`（台灣科技的 ID）
- 表單顯示「台灣科技股份有限公司（統編：12345678）」

#### 情境 2: 業務員搜尋不到公司並新增

**使用者**：李大華，任職於新創公司「未來科技有限公司」
**流程**：
1. 李大華搜尋「未來科技」，無結果
2. 點擊「找不到公司？點此新增」
3. 填寫公司資訊：
   - 名稱：未來科技有限公司
   - 統編：98765432
4. 提交後，系統建立公司並自動選擇
5. Toast 提示「公司新增成功」

**預期結果**：
- 新建 `companies` 記錄（id=100, type='registered'）
- `salesperson_profiles.company_id = 100`

#### 情境 3: 自營業者填寫營業名稱

**使用者**：王小美，個人設計工作室
**流程**：
1. 王小美選擇「我是自營業者」
2. 輸入營業名稱：「王小美設計工作室」
3. 系統自動建立個人公司記錄（is_personal: true）
4. 關聯到業務員檔案

**預期結果**：
- 新建 `companies` 記錄（id=101, type='personal', name='王小美設計工作室', tax_id=null）
- `salesperson_profiles.company_id = 101`

#### 情境 4: 使用者切換公司/自營

**使用者**：陳小強，原本選擇公司，改為自營
**流程**：
1. 陳小強原本選擇「台灣科技股份有限公司」
2. 改選「我是自營業者」
3. 系統彈出確認對話框
4. 陳小強確認切換
5. 公司資訊清除，顯示營業名稱輸入框

**預期結果**：
- `salesperson_profiles.company_id` 從 42 變更為 null（暫時）
- 等待使用者填寫營業名稱後建立個人公司

---

## 3. 功能範圍

### 3.1 In Scope（本次實作）

#### Frontend
- ✅ **公司搜尋 Combobox**（shadcn/ui）
  - 支援公司名稱模糊搜尋
  - 支援統一編號精確搜尋
  - 即時搜尋（Debounce 300ms）
  - 鍵盤導航（Arrow Keys / Enter）
  - 無障礙性（ARIA labels）

- ✅ **新增公司 Dialog**
  - 公司名稱輸入（必填，max: 255）
  - 統一編號輸入（可選，8 位數字驗證）
  - 即時表單驗證
  - 成功後 Toast 提示

- ✅ **自營業者營業名稱輸入**
  - 營業名稱欄位（必填，max: 255）
  - 即時驗證
  - 範例文字提示（Placeholder）

- ✅ **切換確認對話框**（Alert Dialog）
  - 公司 ↔ 自營切換時觸發
  - 清楚說明資料清除影響
  - 取消 / 確認按鈕

- ✅ **表單驗證與錯誤處理**
  - 必填欄位驗證
  - 格式驗證（統編 8 位數）
  - 錯誤訊息顯示
  - Loading 狀態

- ✅ **無障礙性改進**
  - WCAG AA 標準
  - 鍵盤完整操作
  - Screen Reader 支援
  - 色彩對比 >= 4.5:1

#### Backend（最小變動）
- ✅ **API 調整**（如需要）
  - `searchCompanies` 確認支援 name/tax_id 模糊搜尋
  - `createCompany` 確認支援 is_personal 參數
  - 搜尋結果限制 10 筆
  - 回應時間優化（< 500ms）

- ✅ **驗證規則確認**
  - 公司名稱：required, max:255
  - 統一編號：nullable, digits:8
  - 營業名稱：required_if:is_personal, max:255

### 3.2 Out of Scope（不在範圍內）

- ❌ **公司資料審核機制**：新增的公司直接生效，不需審核（未來可加）
- ❌ **公司資料編輯功能**：業務員不能編輯已選擇的公司資訊
- ❌ **公司資料批次匯入**：管理員功能，不在本次範圍
- ❌ **公司 Logo 上傳**：視覺優化功能，延後實作
- ❌ **公司地址、電話等詳細資訊**：註冊階段只填名稱和統編
- ❌ **搜尋歷史記錄**：Nice to have，優先級低
- ❌ **常用公司快速選擇**：需要數據分析支持，延後

---

## 4. 詳細需求

### 4.1 功能需求

#### FR-001: 公司搜尋 Combobox（Must Have）

**描述**：業務員可透過 Autocomplete 搜尋框搜尋公司名稱或統編

**驗收標準**：
- [ ] 輸入公司名稱關鍵字，300ms 後自動搜尋
- [ ] 輸入統一編號，精確匹配公司
- [ ] 搜尋結果顯示「公司名稱 + 統編」（如有）
- [ ] 最多顯示 10 筆結果
- [ ] 超過 10 筆時顯示「請輸入更精確的關鍵字」
- [ ] 支援鍵盤上下選擇、Enter 確認
- [ ] 支援 Esc 關閉下拉選單
- [ ] 選擇後自動填入公司資訊

**API 整合**：
```typescript
// 呼叫 API
const { data } = await searchCompanies({
  name: keyword,      // 公司名稱關鍵字
  tax_id: keyword     // 或統一編號
})

// 預期回應
{
  exists: true,
  companies: [
    { id: 1, name: "台灣科技股份有限公司", tax_id: "12345678", type: "registered" },
    { id: 2, name: "台灣科技有限公司", tax_id: "87654321", type: "registered" }
  ]
}
```

**UI 組件**：shadcn/ui Combobox
- Placeholder: "搜尋公司名稱或統一編號"
- Empty State: "找不到公司？點此新增"

---

#### FR-002: 新增公司 Dialog（Must Have）

**描述**：當搜尋不到公司時，業務員可自行新增公司資料

**驗收標準**：
- [ ] 點擊「找不到公司？點此新增」開啟對話框
- [ ] 公司名稱欄位：必填，即時驗證
- [ ] 統一編號欄位：可選，8 位數字驗證
- [ ] 提交前檢查名稱+統編是否重複
- [ ] 提交成功後 Toast 提示「公司新增成功」
- [ ] 自動選擇剛建立的公司
- [ ] 自動關閉對話框

**API 整合**：
```typescript
// 呼叫 API
const { data } = await createCompany({
  name: "未來科技有限公司",
  tax_id: "98765432",         // nullable
  is_personal: false           // 公司類型
})

// 預期回應
{
  id: 100,
  name: "未來科技有限公司",
  tax_id: "98765432",
  type: "registered"
}
```

**驗證規則**：
- 公司名稱：required, max:255, 不可全空白
- 統一編號：nullable, digits:8

**錯誤處理**：
- 名稱+統編重複：「此公司已存在，請使用搜尋功能」
- API 失敗：「新增失敗，請稍後再試」+ Retry 按鈕

---

#### FR-003: 自營業者營業名稱輸入（Must Have）

**描述**：自營業者可填寫營業名稱，系統建立個人公司記錄

**驗收標準**：
- [ ] 選擇「我是自營業者」顯示營業名稱輸入框
- [ ] 營業名稱欄位：必填
- [ ] Placeholder: "例：王小明設計工作室"
- [ ] 即時驗證（1-255 字元）
- [ ] 儲存時自動建立 is_personal: true 的公司記錄
- [ ] 自動關聯到 salesperson_profiles.company_id

**實作邏輯**：
```typescript
// Frontend 流程
if (isSelfEmployed) {
  // 1. 建立個人公司
  const personalCompany = await createCompany({
    name: businessName,       // 營業名稱
    tax_id: null,             // 個人無統編
    is_personal: true
  })

  // 2. 儲存公司 ID
  await saveCompany({
    company_id: personalCompany.id,
    is_self_employed: true
  })
}
```

**Backend 資料**：
```sql
-- 建立個人公司記錄
INSERT INTO companies (name, tax_id, type)
VALUES ('王小明設計工作室', NULL, 'personal');

-- 關聯到業務員
UPDATE salesperson_profiles
SET company_id = 101
WHERE id = 1;
```

---

#### FR-004: 切換確認對話框（Must Have）

**描述**：在公司業務員與自營業者間切換時，顯示確認對話框防止誤操作

**驗收標準**：
- [ ] 從「公司」切換到「自營」觸發確認框
- [ ] 從「自營」切換到「公司」觸發確認框
- [ ] 確認框清楚說明資料清除影響
- [ ] 提供「取消」和「確認切換」按鈕
- [ ] 點擊「取消」保持原選擇
- [ ] 點擊「確認」執行切換並清除原資料

**對話框內容**：

**公司 → 自營**：
```
標題: ⚠️ 確認切換為自營業者？
內容: 切換後，您先前選擇的公司資訊將被清除。
按鈕: [取消] [確認切換]
```

**自營 → 公司**：
```
標題: ⚠️ 確認切換為公司業務員？
內容: 切換後，您的自營業者名稱將被清除。
按鈕: [取消] [確認切換]
```

**UI 組件**：shadcn/ui Alert Dialog

---

#### FR-005: 搜尋結果呈現（Must Have）

**描述**：搜尋結果清楚顯示公司名稱和統編，支援重複名稱區分

**驗收標準**：
- [ ] 每筆結果顯示公司名稱（主要）
- [ ] 統編顯示在第二行（次要、較小字體）
- [ ] 個人公司顯示「個人工作室」標籤
- [ ] 精確匹配結果排在最前
- [ ] 結果清單支援鍵盤上下選擇
- [ ] 選中項目有明顯視覺回饋（高亮）

**呈現格式**：
```
┌─────────────────────────────────────┐
│ 🏢 台灣科技股份有限公司              │  ← 公司名稱（16px, 粗體）
│    統編：12345678                    │  ← 統編（14px, 灰色）
├─────────────────────────────────────┤
│ 👤 王小明設計工作室                  │  ← 個人工作室
│    個人工作室                         │  ← 標籤（無統編）
└─────────────────────────────────────┘
```

**色彩對比**：
- 公司名稱：#000000（黑色）
- 統編：#666666（深灰）
- 背景對比 >= 4.5:1（符合 WCAG AA）

---

### 4.2 資料需求

#### 資料模型：個人公司方案

**採用方案**：使用現有 `companies` 表，透過 `type` 欄位區分

**資料表結構**：
```sql
-- companies 表（現有，無需 Migration）
CREATE TABLE companies (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  tax_id VARCHAR(8) NULLABLE,        -- 個人公司可為 null
  type ENUM('registered', 'personal') DEFAULT 'registered',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- salesperson_profiles 表（現有）
CREATE TABLE salesperson_profiles (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  company_id BIGINT NULLABLE,        -- FK to companies
  full_name VARCHAR(255),
  -- ... 其他欄位
  FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

**資料範例**：

| id | name | tax_id | type | 說明 |
|----|------|--------|------|------|
| 1 | 台灣科技股份有限公司 | 12345678 | registered | 一般公司 |
| 2 | 王小明設計工作室 | NULL | personal | 個人工作室 |
| 3 | 李大華個人工作室 | NULL | personal | 個人工作室 |

**資料關聯**：
- `salesperson_profiles.company_id` → `companies.id`
- 所有業務員（含自營）都關聯到 `companies` 表
- 透過 `companies.type` 區分公司類型

**優點**：
- ✅ 無需 Migration，使用現有架構
- ✅ 查詢邏輯統一，無需特殊處理
- ✅ 資料模型清晰，符合語意
- ✅ 未來擴充性佳（可新增公司屬性）

---

### 4.3 權限需求

**權限規則**：
- 所有業務員都可以搜尋公司
- 所有業務員都可以新增公司
- 業務員只能選擇自己的任職公司（不能選擇其他業務員的個人公司）

**個人公司可見性**（未來考慮）：
- 個人公司（is_personal: true）不出現在公開搜尋結果
- 只有該業務員自己可見

**本次實作**：
- ✅ 所有公司都可搜尋（包含個人公司）
- ❌ 個人公司隱私控制（延後實作）

---

### 4.4 UI/UX 需求

#### 頁面/組件結構

```
SalespersonRegistrationForm
├─ EmploymentTypeSelector (公司 / 自營選擇器)
│  ├─ Radio: "我任職於某家公司"
│  └─ Radio: "我是自營業者"
│
├─ CompanySearchCombobox (公司搜尋，條件顯示)
│  ├─ Combobox Input (搜尋輸入框)
│  ├─ CompanyList (搜尋結果清單)
│  │  ├─ CompanyItem (公司項目)
│  │  └─ AddCompanyTrigger (新增公司觸發)
│  └─ AddCompanyDialog (新增公司對話框)
│     ├─ Input: 公司名稱
│     ├─ Input: 統一編號
│     └─ Buttons: 取消 / 確認
│
├─ BusinessNameInput (營業名稱，條件顯示)
│  └─ Input: 營業名稱
│
└─ SwitchConfirmDialog (切換確認對話框)
   ├─ Alert Title
   ├─ Alert Description
   └─ Buttons: 取消 / 確認
```

#### 互動設計

**1. 搜尋互動**
- **觸發**：使用者輸入文字
- **Debounce**：300ms
- **Loading**：顯示 Spinner
- **成功**：顯示結果清單（最多 10 筆）
- **無結果**：顯示「找不到公司？點此新增」
- **失敗**：顯示「搜尋失敗，請稍後再試」

**2. 選擇互動**
- **鍵盤**：Arrow Up/Down 選擇，Enter 確認
- **滑鼠**：點擊選擇
- **視覺回饋**：選中項目高亮顯示（藍色背景）

**3. 新增公司互動**
- **觸發**：點擊「找不到公司？點此新增」
- **開啟**：Dialog 淡入動畫
- **驗證**：即時表單驗證（輸入時）
- **提交**：Loading 狀態（按鈕 Spinner）
- **成功**：Toast 提示 + 自動選擇 + 關閉 Dialog
- **失敗**：錯誤訊息 + Retry 按鈕

**4. 切換互動**
- **觸發**：變更 Employment Type Radio
- **條件**：已填寫原類型資料
- **顯示**：Alert Dialog
- **確認**：清除原資料 + 顯示新介面
- **取消**：保持原選擇

#### 響應式設計

**桌面版（>= 1024px）**：
- Combobox 寬度：100%（最大 600px）
- Dialog 寬度：500px
- 字體大小：16px

**平板版（768px - 1023px）**：
- Combobox 寬度：100%
- Dialog 寬度：90vw（最大 500px）
- 字體大小：16px

**手機版（< 768px）**：
- Combobox 寬度：100%
- Dialog 全螢幕（Sheet）
- 字體大小：16px
- 觸控目標：>= 44x44px

#### 無障礙性（WCAG AA）

**鍵盤導航**：
- Tab：在欄位間移動
- Arrow Up/Down：在搜尋結果間移動
- Enter：確認選擇
- Esc：關閉下拉選單 / Dialog

**ARIA 標籤**：
```html
<input
  role="combobox"
  aria-label="搜尋公司名稱或統一編號"
  aria-expanded="true"
  aria-controls="company-list"
  aria-activedescendant="company-1"
/>

<ul id="company-list" role="listbox">
  <li id="company-1" role="option" aria-selected="true">
    台灣科技股份有限公司
  </li>
</ul>
```

**Screen Reader 支援**：
- 搜尋結果數量宣告：「找到 3 筆結果」
- 選中項目宣告：「已選擇 台灣科技股份有限公司」
- 錯誤訊息即時宣告（aria-live="polite"）

**色彩對比**：
- 文字 vs 背景：>= 4.5:1
- 高亮 vs 背景：>= 3:1
- 錯誤訊息：紅色 (#DC2626) vs 白色 = 5.9:1 ✅

---

## 5. 邊界情境處理

### 5.1 異常情況處理

| 情境 | 系統行為 | 錯誤訊息 | 使用者操作 |
|-----|---------|---------|-----------|
| **搜尋失敗（API Error）** | 顯示錯誤提示 | "搜尋失敗，請稍後再試" | Retry 按鈕 |
| **搜尋無結果** | 顯示新增引導 | "找不到公司？點此新增" | 點擊新增 |
| **搜尋結果 > 10 筆** | 只顯示前 10 筆 | "請輸入更精確的關鍵字" | 繼續輸入 |
| **新增公司失敗** | 顯示錯誤 + Retry | "新增失敗，請稍後再試" | 點擊 Retry |
| **公司名稱+統編重複** | 阻止提交 | "此公司已存在，請使用搜尋功能" | 關閉 Dialog |
| **營業名稱為空** | 阻止提交 | "請填寫營業名稱" | 填寫欄位 |
| **統編格式錯誤** | 即時驗證錯誤 | "統一編號必須為 8 位數字" | 修正輸入 |
| **API 回應超時（> 5s）** | 顯示超時錯誤 | "請求超時，請檢查網路連線" | Retry 按鈕 |
| **網路斷線** | 顯示離線提示 | "網路連線中斷，請檢查網路" | 重試 |

### 5.2 Edge Cases

#### Edge Case 1: 公司名稱完全相同

**情境**：
- 資料庫有兩家「台灣科技股份有限公司」
- 統編分別為 12345678 和 87654321

**預期行為**：
- 搜尋結果同時顯示兩筆
- 透過統編區分（顯示在第二行）
- 使用者根據統編選擇正確的公司

**呈現**：
```
┌─────────────────────────────────────┐
│ 🏢 台灣科技股份有限公司              │
│    統編：12345678                    │
├─────────────────────────────────────┤
│ 🏢 台灣科技股份有限公司              │
│    統編：87654321                    │
└─────────────────────────────────────┘
```

#### Edge Case 2: 個人公司（無統編）重名

**情境**：
- 兩位自營業者都叫「王小明設計工作室」
- 都是 is_personal: true，tax_id: null

**預期行為**：
- 搜尋結果顯示兩筆
- 顯示「個人工作室」標籤（無統編可區分）
- **未來改進**：加上業務員姓名區分

**呈現**：
```
┌─────────────────────────────────────┐
│ 👤 王小明設計工作室                  │
│    個人工作室                         │
├─────────────────────────────────────┤
│ 👤 王小明設計工作室                  │
│    個人工作室                         │
└─────────────────────────────────────┘
```

**本次實作**：允許重名（因無統編）
**未來改進**：新增欄位區分（如業務員姓名）

#### Edge Case 3: 搜尋中快速切換輸入

**情境**：
- 使用者輸入「台灣」觸發搜尋
- 300ms 內改輸入「科技」
- 又快速改為「公司」

**預期行為**：
- Debounce 會取消前兩次搜尋
- 只執行最後一次「公司」的搜尋
- 避免不必要的 API 呼叫

**實作**：使用 `useDebouncedValue` (300ms)

#### Edge Case 4: 已選擇公司後切換為自營

**情境**：
- 使用者先選擇「台灣科技股份有限公司」
- 改選「我是自營業者」

**預期行為**：
1. 顯示確認對話框
2. 使用者確認後，清除 company_id
3. 顯示營業名稱輸入框
4. 原公司選擇狀態清除

#### Edge Case 5: 已填營業名稱後切換為公司

**情境**：
- 使用者填寫「王小明設計工作室」
- 改選「我任職於某家公司」

**預期行為**：
1. 顯示確認對話框
2. 使用者確認後，清除營業名稱
3. 顯示公司搜尋框
4. 個人公司記錄暫時保留（不刪除，只是不關聯）

#### Edge Case 6: 同時開啟多個新增公司 Dialog（理論上不可能）

**情境**：
- 使用者快速點擊兩次「新增公司」

**預期行為**：
- Dialog 狀態由 state 控制，只會開啟一個
- 第二次點擊無效

#### Edge Case 7: 搜尋時輸入特殊字元

**情境**：
- 使用者輸入「<script>alert('XSS')</script>」

**預期行為**：
- Frontend：不處理特殊字元，直接傳給 Backend
- Backend：Laravel 自動跳脫 SQL 注入
- 搜尋結果：安全返回（無 XSS 風險）

**安全性**：Laravel Eloquent 自動防護 SQL Injection

#### Edge Case 8: API 回應格式錯誤

**情境**：
- Backend 回應格式與預期不符
- 例：`companies` 為 null 而非空陣列

**預期行為**：
- Frontend 使用 Zod Schema 驗證回應
- 格式錯誤視為 API Error
- 顯示「搜尋失敗，請稍後再試」

**實作**：React Query error boundary

---

## 6. 技術考量

### 6.1 技術架構

**Frontend Stack**：
- Next.js 15 (App Router)
- React 19
- TypeScript (strict mode)
- shadcn/ui (Combobox, Dialog, Alert Dialog)
- React Hook Form (表單管理)
- Zod (Schema 驗證)
- React Query (API 狀態管理)

**Backend Stack**（最小變動）：
- Laravel 11
- MySQL 8.0
- 現有 API（確認支援）

### 6.2 效能考量

**前端效能目標**（參考 metrics-standards.md）：

| 指標 | 目標 | 衡量方式 |
|------|------|----------|
| **搜尋 Debounce** | 300ms | `useDebouncedValue` |
| **API 回應時間** | < 500ms (P95) | Backend 監控 |
| **首次互動延遲（FID）** | < 100ms | Core Web Vitals |
| **Combobox 渲染** | < 16ms (60fps) | React DevTools Profiler |
| **Bundle Size 增加** | < 10KB (gzip) | Next.js Build Analyzer |

**優化策略**：
1. **搜尋優化**
   - Debounce 300ms 減少 API 呼叫
   - 結果限制 10 筆，降低渲染負擔
   - 使用 React Query cache，避免重複請求

2. **組件優化**
   - Combobox 使用虛擬滾動（如結果 > 50 筆，未來）
   - 使用 `React.memo` 避免不必要的重渲染
   - 懶加載 Dialog（條件渲染）

3. **資料庫優化**（Backend）
   - `companies.name` 欄位建立索引
   - `companies.tax_id` 欄位建立索引
   - 使用 `LIMIT 10` 限制查詢結果

### 6.3 安全性考量

**Frontend 安全**：
1. **XSS 防護**
   - React 自動跳脫 HTML
   - 使用者輸入不直接渲染為 HTML

2. **輸入驗證**
   - Zod Schema 嚴格驗證
   - 統編限制 8 位數字
   - 公司名稱限制 255 字元

3. **CSRF 防護**
   - Laravel Sanctum CSRF Token
   - API 請求自動攜帶 Token

**Backend 安全**：
1. **SQL Injection 防護**
   - Laravel Eloquent 自動參數化查詢
   - 不使用原生 SQL

2. **權限檢查**
   - 業務員只能編輯自己的檔案
   - 新增公司不需特殊權限

3. **資料驗證**
   - Laravel Request Validation
   - 統編格式驗證：`digits:8`
   - 公司名稱長度驗證：`max:255`

### 6.4 第三方整合

**無第三方整合**：
- 本功能不涉及外部 API
- 不需要統編驗證服務（未來可考慮）

---

## 7. 驗收標準

### 7.1 功能驗收

#### 公司搜尋功能
- [ ] 輸入公司名稱關鍵字，300ms 後自動搜尋
- [ ] 輸入統一編號，可精確匹配公司
- [ ] 搜尋結果顯示公司名稱和統編（如有）
- [ ] 搜尋結果最多 10 筆
- [ ] 支援鍵盤上下選擇、Enter 確認
- [ ] 選擇公司後自動填入表單
- [ ] 搜尋失敗顯示錯誤訊息 + Retry

#### 新增公司功能
- [ ] 點擊「找不到公司？點此新增」開啟 Dialog
- [ ] 公司名稱必填驗證
- [ ] 統一編號可選，8 位數字驗證
- [ ] 提交成功後 Toast 提示
- [ ] 自動選擇剛建立的公司
- [ ] 自動關閉 Dialog

#### 自營業者功能
- [ ] 選擇「我是自營業者」顯示營業名稱輸入框
- [ ] 營業名稱必填驗證
- [ ] 儲存時自動建立個人公司記錄（is_personal: true）
- [ ] 自動關聯到 salesperson_profiles.company_id

#### 切換確認功能
- [ ] 從公司切換到自營顯示確認對話框
- [ ] 從自營切換到公司顯示確認對話框
- [ ] 確認後清除原資料
- [ ] 取消後保持原選擇

#### 邊界情況
- [ ] 公司名稱重複時顯示統編區分
- [ ] 搜尋無結果時顯示新增引導
- [ ] 搜尋結果 > 10 筆時提示精確關鍵字
- [ ] API 失敗時顯示錯誤訊息 + Retry
- [ ] 網路斷線時顯示離線提示

### 7.2 非功能驗收

#### 效能驗收
- [ ] 搜尋 API 回應時間 < 500ms (P95)
- [ ] Combobox 渲染時間 < 16ms (60fps)
- [ ] 首次互動延遲（FID）< 100ms
- [ ] Bundle Size 增加 < 10KB (gzip)

#### 安全性驗收
- [ ] XSS 攻擊測試：輸入 `<script>` 無效
- [ ] SQL Injection 測試：輸入 `' OR '1'='1` 無效
- [ ] CSRF 測試：無 Token 請求被拒絕
- [ ] 權限測試：業務員無法編輯他人資料

#### 無障礙性驗證（WCAG AA）
- [ ] 鍵盤完整操作（Tab, Arrow, Enter, Esc）
- [ ] ARIA 標籤正確（role, aria-label）
- [ ] Screen Reader 測試通過（NVDA / VoiceOver）
- [ ] 色彩對比 >= 4.5:1
- [ ] 觸控目標 >= 44x44px（手機版）

#### 瀏覽器相容性
- [ ] Chrome 120+ ✅
- [ ] Firefox 120+ ✅
- [ ] Safari 17+ ✅
- [ ] Edge 120+ ✅
- [ ] 手機瀏覽器（iOS Safari, Chrome Android）✅

#### 響應式驗證
- [ ] 桌面版（>= 1024px）佈局正常
- [ ] 平板版（768px - 1023px）佈局正常
- [ ] 手機版（< 768px）佈局正常
- [ ] Dialog 在手機版使用 Sheet（全螢幕）

---

## 8. 風險與依賴

### 8.1 潛在風險

#### 風險 1: Backend API 不支援模糊搜尋
- **描述**：`searchCompanies` API 可能只支援精確匹配
- **機率**：低
- **影響**：高（核心功能無法實作）
- **緩解措施**：
  1. 先測試現有 API 行為
  2. 如不支援，需 Backend 調整為 `LIKE %keyword%`
  3. 預估 Backend 調整時間：1 小時

#### 風險 2: 個人公司方案與現有資料模型不相容
- **描述**：`companies.type` 欄位可能不存在或語意不同
- **機率**：低
- **影響**：中（需改用方案 A：新增欄位）
- **緩解措施**：
  1. 檢查 `companies` 表結構
  2. 如無 `type` 欄位，考慮新增 Migration
  3. 或使用方案 A（新增 `salesperson_profiles.business_name`）

#### 風險 3: 效能問題（公司數量暴增）
- **描述**：如公司數量達 10 萬筆，搜尋可能變慢
- **機率**：低（短期不會達到）
- **影響**：中（搜尋回應時間 > 500ms）
- **緩解措施**：
  1. 確保 `name` 和 `tax_id` 欄位有索引
  2. 使用 `LIMIT 10` 限制結果
  3. 未來考慮引入 Elasticsearch（長期）

#### 風險 4: UX 測試發現 Combobox 不符合期待
- **描述**：使用者測試後發現介面不直覺
- **機率**：中
- **影響**：中（需調整 UI）
- **緩解措施**：
  1. 提供 Placeholder 和範例文字引導
  2. 準備備案：使用 Dialog 搜尋（較傳統但穩定）

### 8.2 依賴項目

#### 依賴 1: Backend API 確認
- **依賴內容**：`searchCompanies` 支援 name/tax_id 模糊搜尋
- **責任方**：Backend 團隊
- **預估時間**：1 小時（測試 + 調整）
- **阻塞**：Frontend 開發前需確認

#### 依賴 2: shadcn/ui Combobox 組件
- **依賴內容**：shadcn/ui Combobox 已安裝且可用
- **責任方**：Frontend 團隊
- **預估時間**：30 分鐘（安裝 + 測試）
- **阻塞**：Frontend 開發前需確認

#### 依賴 3: 資料庫索引
- **依賴內容**：`companies.name` 和 `companies.tax_id` 有索引
- **責任方**：Backend 團隊
- **預估時間**：30 分鐘（Migration）
- **阻塞**：效能測試前需完成

---

## 9. 時程規劃（參考）

**總工作量**：16 小時（2 個工作日）

### Phase 1: 需求確認與準備（2 小時）
- [ ] 確認 Backend API 支援模糊搜尋（1 小時）
- [ ] 確認資料模型方案（30 分鐘）
- [ ] 安裝 shadcn/ui Combobox（30 分鐘）

**完成時間**：Day 1 上午

### Phase 2: Frontend 開發（10 小時）
- [ ] 實作 CompanySearchCombobox（3 小時）
- [ ] 實作 AddCompanyDialog（2 小時）
- [ ] 實作 BusinessNameInput（1 小時）
- [ ] 實作 SwitchConfirmDialog（1 小時）
- [ ] 整合表單驗證（1 小時）
- [ ] 無障礙性改進（1 小時）
- [ ] 響應式調整（1 小時）

**完成時間**：Day 1 下午 + Day 2 上午

### Phase 3: Backend 調整（2 小時，如需要）
- [ ] 調整搜尋 API 支援模糊搜尋（1 小時）
- [ ] 新增資料庫索引（30 分鐘）
- [ ] 驗證規則調整（30 分鐘）

**完成時間**：Day 2 上午（並行）

### Phase 4: 測試與修正（2 小時）
- [ ] 功能測試（30 分鐘）
- [ ] 邊界情況測試（30 分鐘）
- [ ] 無障礙性測試（30 分鐘）
- [ ] Bug 修正（30 分鐘）

**完成時間**：Day 2 下午

### Phase 5: 上線準備（0.5 小時）
- [ ] Code Review（20 分鐘）
- [ ] 文件更新（10 分鐘）

**完成時間**：Day 2 下午

**預計上線時間**：Day 2 結束

---

## 10. 附錄

### 10.1 參考資料

**UI 組件**：
- [shadcn/ui Combobox](https://ui.shadcn.com/docs/components/combobox)
- [shadcn/ui Dialog](https://ui.shadcn.com/docs/components/dialog)
- [shadcn/ui Alert Dialog](https://ui.shadcn.com/docs/components/alert-dialog)

**無障礙性**：
- [WCAG 2.1 AA 標準](https://www.w3.org/WAI/WCAG21/quickref/)
- [ARIA Authoring Practices - Combobox](https://www.w3.org/WAI/ARIA/apg/patterns/combobox/)

**效能標準**：
- `.claude/knowledge/workflow/metrics-standards.md`
- [Core Web Vitals](https://web.dev/vitals/)

**專案規範**：
- `frontend/CLAUDE.md` - Frontend 開發規範
- `openspec/specs/frontend/README.md` - Frontend 規範總覽

### 10.2 未來規劃

**Phase 2 功能（延後實作）**：
1. **公司資料完整性**
   - 新增公司地址、電話、網站等欄位
   - 公司 Logo 上傳功能
   - 公司簡介

2. **搜尋優化**
   - 搜尋歷史記錄
   - 常用公司快速選擇
   - 智慧推薦（根據所在地、產業）

3. **個人公司隱私**
   - 個人公司不出現在公開搜尋
   - 只有該業務員可見

4. **公司資料審核**
   - 新增公司需管理員審核
   - 審核通過後才可使用

5. **統編驗證服務**
   - 整合政府統編查詢 API
   - 自動驗證統編真實性
   - 自動帶入公司名稱

**長期規劃**：
- Elasticsearch 全文搜尋（公司數量 > 10 萬筆時）
- 公司評分系統
- 公司驗證徽章

---

## 11. 開發範圍判斷

```json
{
  "backend": true,
  "frontend": true,
  "ui_design": true,
  "architecture": false,
  "database": false
}
```

**說明**：
- ✅ **Backend**: 需調整搜尋 API（模糊搜尋）、確認驗證規則
- ✅ **Frontend**: 主要開發範圍（Combobox, Dialog, 表單驗證）
- ✅ **UI Design**: 需設計 Combobox 樣式、Dialog 佈局
- ❌ **Architecture**: 使用現有架構，無需變更
- ❌ **Database**: 使用現有資料模型（個人公司方案），無需 Migration

---

## 12. 檔案清單

### 12.1 Frontend 檔案（需修改）

#### 新增檔案
```
frontend/components/salesperson/
├─ CompanySearchCombobox.tsx          (新增，300 行)
├─ AddCompanyDialog.tsx                (新增，200 行)
├─ BusinessNameInput.tsx               (新增，100 行)
├─ SwitchConfirmDialog.tsx             (新增，100 行)
└─ types.ts                            (新增，50 行)
```

#### 修改檔案
```
frontend/
├─ app/salesperson/register/page.tsx   (修改，整合新組件)
├─ lib/api/salesperson.ts              (確認，API 呼叫邏輯)
└─ lib/validations/salesperson.ts      (修改，新增驗證規則)
```

**總計**：
- 新增：5 個檔案，750 行程式碼
- 修改：3 個檔案，預估 +150 行

### 12.2 Backend 檔案（可能需修改）

#### 需確認的檔案
```
my_profile_laravel/
├─ app/Http/Controllers/Api/SalespersonController.php  (確認搜尋邏輯)
├─ app/Http/Requests/SearchCompanyRequest.php          (可能新增)
└─ app/Models/Company.php                              (確認 type 欄位)
```

#### 可能需新增的 Migration（視情況）
```
database/migrations/
└─ xxxx_add_indexes_to_companies_table.php  (新增 name, tax_id 索引)
```

**總計**：
- 修改：1-2 個檔案，預估 +50 行
- Migration：1 個（可選）

---

## 13. 工作量預估

### 13.1 詳細工作量

| 階段 | 工作項目 | 前端 | 後端 | 總計 |
|------|---------|------|------|------|
| **準備階段** | API 確認與測試 | 0.5h | 1h | 1.5h |
| | shadcn/ui 組件安裝 | 0.5h | - | 0.5h |
| **Frontend 開發** | CompanySearchCombobox | 3h | - | 3h |
| | AddCompanyDialog | 2h | - | 2h |
| | BusinessNameInput | 1h | - | 1h |
| | SwitchConfirmDialog | 1h | - | 1h |
| | 表單驗證整合 | 1h | - | 1h |
| | 無障礙性改進 | 1h | - | 1h |
| | 響應式調整 | 1h | - | 1h |
| **Backend 調整** | 搜尋 API 優化 | - | 1h | 1h |
| | 資料庫索引 | - | 0.5h | 0.5h |
| | 驗證規則調整 | - | 0.5h | 0.5h |
| **測試** | 功能測試 | 0.5h | 0.5h | 1h |
| | 邊界測試 | 0.5h | - | 0.5h |
| | 無障礙性測試 | 0.5h | - | 0.5h |
| **總計** | | **12h** | **3.5h** | **15.5h** |

### 13.2 工作量總結

- **Frontend**: 12 小時（1.5 個工作日）
- **Backend**: 3.5 小時（0.5 個工作日）
- **總計**: 15.5 小時（約 2 個工作日）

**風險緩衝**：+0.5 天（處理未預期問題）
**實際預估**：**2.5 個工作日**

---

## 14. 下一步行動

### 14.1 立即行動（本週）

1. **確認 Backend API 支援**（1 小時）
   - [ ] 測試 `searchCompanies` 是否支援模糊搜尋
   - [ ] 確認 `createCompany` 支援 is_personal 參數
   - [ ] 確認 `companies.type` 欄位存在

2. **安裝 Frontend 依賴**（30 分鐘）
   - [ ] 安裝 shadcn/ui Combobox
   - [ ] 測試 Combobox 基本功能

3. **開始 Frontend 開發**（Day 1-2）
   - [ ] 實作 CompanySearchCombobox
   - [ ] 實作 AddCompanyDialog
   - [ ] 實作 BusinessNameInput
   - [ ] 實作 SwitchConfirmDialog

4. **Backend 調整**（Day 2，如需要）
   - [ ] 調整搜尋 API
   - [ ] 新增資料庫索引

5. **測試與上線**（Day 2 下午）
   - [ ] 功能測試
   - [ ] 無障礙性測試
   - [ ] 上線

### 14.2 後續規劃（未來）

- Phase 2: 公司資料完整性（地址、Logo）
- Phase 3: 搜尋優化（歷史記錄、智慧推薦）
- Phase 4: 統編驗證服務整合

---

**提案完成**
**待審核**: 請確認本提案內容，通過後將進入 Specification 階段產出詳細技術規格。
