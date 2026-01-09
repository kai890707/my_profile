# OpenSpec 規範驅動開發

**開發功能**: $ARGUMENTS

**開發方法**: Specification-Driven Development (SDD)

---

## 核心理念

**規範先行，程式碼後行**

1. 先撰寫完整、明確的規格文件
2. 規格通過驗證後才開始實作
3. 實作嚴格遵循規格
4. 完成後歸檔到規範庫

**目標**:
- ✅ 降低幻覺與誤解
- ✅ 減少錯誤開發
- ✅ 確保需求理解正確
- ✅ 可追溯、可維護

---

## 執行流程

```
/implement [功能描述]
    ↓
Step 1: Create Proposal (建立變更提案)
    → openspec/changes/<feature-name>/proposal.md

Step 2: Write Specifications (撰寫詳細規格)
    → openspec/changes/<feature-name>/specs/
        ├── api.md              # API 端點規格
        ├── data-model.md       # 資料模型設計
        └── business-rules.md   # 業務規則定義

Step 3: Break Down Tasks (拆解實作任務)
    → openspec/changes/<feature-name>/tasks.md

Step 4: Validate Specs (驗證規格完整性)
    ✓ 檢查必要欄位是否完整
    ✓ 檢查規格是否清晰無歧義
    ✓ 檢查是否與現有系統衝突

Step 5: Implement (實作開發)
    → 嚴格按照 tasks.md 執行
    → 使用 TodoWrite 追蹤進度
    → 每個任務完成立即驗證

Step 6: Archive (歸檔到規範庫)
    → 合併 specs/ 到 openspec/specs/
    → 移動到 openspec/changes/archived/
```

---

## 重要原則

### 1. 規範驅動

❌ **禁止**:
- 未撰寫規格就開始寫程式
- 規格模糊、不完整就開始實作
- 實作過程中隨意偏離規格

✅ **必須**:
- 規格必須詳細到「無需解釋就能理解」
- 所有 API 端點都有完整的 Request/Response 範例
- 所有資料表都有完整的欄位定義與約束
- 所有業務規則都有明確的驗證邏輯
- 發散需求推理邊界場景
- 每項提案都必須提出三種可用的解決方案

---

### 2. 最小化幻覺

**問題**: AI 容易「自行腦補」未明確定義的內容

**解決**:
- 在 Proposal 階段使用 `AskUserQuestion` 確認所有模糊點
- 規格中明確列出「In Scope」和「Out of Scope」
- 每個功能都有明確的驗收標準

**範例**:
```markdown
## Scope

### In Scope (本次實作)
- 使用者可以給業務員評分（1-5 星）
- 評分需要登入
- 一個使用者只能給一個業務員評一次分

### Out of Scope (本次不做)
- ❌ 評分後的推薦演算法
- ❌ 評分通知功能
- ❌ 評分申訴機制
```

---

### 3. 錯誤預防

**策略 1: 規格審查點**
- Proposal 完成後暫停，確認需求理解正確
- Specs 完成後暫停，確認技術方案可行
- Tasks 拆解後暫停，確認執行順序合理

**策略 2: 漸進式實作**
- 一次只處理一個 Task（in_progress）
- 每個 Task 完成立即測試驗證
- 發現錯誤立即修復，不累積技術債

**策略 3: 規格一致性檢查**
- API 規格與資料模型必須對應
- 業務規則必須在程式碼中實現
- 測試案例必須覆蓋所有驗收標準

---

## 工作流程詳細說明

### Step 1: Create Proposal

**任務**: 建立變更提案，明確定義「為什麼」和「做什麼」

**產出**: `openspec/changes/<feature-name>/proposal.md`

**內容檢查清單**:
- [ ] **Why** - 問題陳述清晰（為什麼需要這個功能）
- [ ] **What** - 解決方案明確（這個功能的核心價值）
- [ ] **Scope** - 範圍清楚（In Scope / Out of Scope）
- [ ] **Success Criteria** - 驗收標準可測試

**決策點**: 
- 使用 `AskUserQuestion` 確認所有模糊點

---

### Step 2: Write Specifications

**任務**: 撰寫詳細的技術規格

**產出**:
1. `specs/api.md` - API 端點規格
2. `specs/data-model.md` - 資料模型設計
3. `specs/business-rules.md` - 業務規則定義

**API 規格必須包含**:
```markdown
### POST /api/endpoint

**Authentication**: Required / Not required
**Authorization**: Role requirements

**Request Body**:
```json
{
  "field": "value"
}
```

**Validation**:
- `field`: required, type, constraints

**Response (200 OK)**:
```json
{
  "status": "success",
  "data": {}
}
```

**Error Responses**:
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
```

**資料模型必須包含**:
- 完整的 SQL CREATE TABLE 語句
- 每個欄位的類型、約束、說明
- 所有索引和外鍵定義
- Migration 檔案程式碼
- Model 類別程式碼

**業務規則必須包含**:
- 規則編號（BR-001, BR-002...）
- 規則描述
- 實作方式（資料庫約束 / 應用層驗證）
- 錯誤處理（HTTP 狀態碼、錯誤訊息）

**決策點**: 確認規格完整無歧義

---

### Step 3: Break Down Tasks

**任務**: 將規格拆解為可執行的開發任務

**產出**: `tasks.md`

**格式**:
```markdown
# Implementation Tasks: [功能名稱]

## Phase 1: Database & Models
- [ ] Task 1.1: Create migration for [table_name]
      檔案: app/Database/Migrations/YYYY-MM-DD-XXXXXX_CreateTableName.php
- [ ] Task 1.2: Create Model class
      檔案: app/Models/TableNameModel.php
- [ ] Task 1.3: Test model CRUD operations

## Phase 2: API Endpoints
- [ ] Task 2.1: Create Controller
      檔案: app/Controllers/Api/FeatureController.php
- [ ] Task 2.2: Implement POST /api/endpoint
      方法: FeatureController::create()
...
```

**原則**:
- 每個 Task 必須是原子性的（不可再拆解）
- 每個 Task 必須有明確的產出（檔案、方法）
- Tasks 之間的相依關係必須清楚
- 若Tasks不明確，則回到上一層步驟，重新檢查

---

### Step 4: Validate Specs

**任務**: 驗證規格完整性和一致性

**檢查清單**:

**完整性檢查**:
- [ ] 所有 API 端點都有 Request/Response 範例
- [ ] 所有資料表都有 Migration 和 Model 程式碼
- [ ] 所有業務規則都有實作說明
- [ ] Tasks.md 涵蓋所有需要的檔案

**一致性檢查**:
- [ ] API 的 Request 欄位在資料模型中都有對應
- [ ] 業務規則在 API 和資料模型中都有體現
- [ ] Tasks 的順序符合相依關係（如 Migration 在 Model 之前）

**清晰性檢查**:
- [ ] 規格無歧義（不需要猜測）
- [ ] 範例完整（可直接複製使用）
- [ ] 錯誤處理完整（每個異常情況都有說明）

**決策點**: 
- 所有檢查通過才進入實作階段
- 若檢查不通過則回到上一層步驟，重新檢查
- 「進入實作階段之後就走全自動化開發」，不要停下來

---

### Step 5: Implement

**任務**: 嚴格按照規格和任務清單實作，「進入實作階段之後就走全自動化開發」，不要停下來

**工作原則**:

**1. 初始化任務追蹤**
```javascript
使用 TodoWrite 建立任務清單（對應 tasks.md）
```

**2. 逐步實作**
```
FOR each task in tasks.md:
  1. 標記為 in_progress
  2. 讀取規格（API/Data Model/Business Rules）
  3. 實作程式碼（嚴格遵循規格）
  4. 驗證功能正確
  5. 標記為 completed
  6. 繼續下一個任務
```

**3. 嚴格遵循規格**

❌ **禁止**:
- 偏離規格定義的 API 格式
- 修改資料表結構（與規格不符）
- 跳過業務規則驗證
- 添加規格外的功能

✅ **必須**:
- API Request/Response 與規格完全一致
- 資料表結構與規格完全一致
- 所有業務規則都已實現
- 所有驗收標準都可測試

**4. 錯誤處理**
```
遇到錯誤時:
  1. ❌ 不要標記任務為 completed
  2. 分析錯誤原因
  3. 如果是規格問題 → 返回 Step 2 修正規格
  4. 如果是實作問題 → 修復後繼續
  5. 創建修復子任務（如需要）
```

**5. 完成檢查**

每個任務完成時必須確認:
- [ ] 程式碼可執行，無語法錯誤
- [ ] 功能符合規格定義
- [ ] 已測試基本功能（手動測試或腳本）
- [ ] 沒有破壞現有功能

---

### Step 6: Archive

**任務**: 歸檔到規範庫

**操作**:
1. 合併 `specs/api.md` 到 `openspec/specs/api/endpoints.md`
2. 合併 `specs/data-model.md` 到 `openspec/specs/models/data-models.md`
3. 移動 `openspec/changes/<feature>/` 到 `openspec/changes/archived/`
4. 更新 `openspec/specs/` 的版本記錄

**使用指令**:
```bash
/archive <feature-name>
```

---

## 執行模式

### 完全自動模式（推薦）

```bash
/implement [功能描述]
```

**流程**: Proposal → Specs → Tasks → Validate → Implement → Archive

**暫停點**:
1. Proposal 完成後（確認需求）
2. Specs 完成後（確認規格）
3. Validate 完成後（確認無誤）

---

### 單步執行模式

```bash
/proposal [功能描述]   # 只建立提案
/spec <feature-name>   # 只撰寫規格
/develop <feature-name> # 只執行實作
/archive <feature-name> # 只執行歸檔
```

適用於：需要分次完成，或需要更多控制的情況

---

## 品質保證

### 規格品質標準

**Level 1 - 基本**:
- 有 Proposal
- 有 API 規格
- 有資料模型

**Level 2 - 良好** ✅ **最低要求**:
- Proposal 清晰無歧義
- API 有完整 Request/Response 範例
- 資料模型有 Migration 程式碼
- 業務規則明確定義

**Level 3 - 優秀**:
- 所有邊界情況都有說明
- 錯誤處理完整覆蓋
- 效能考量已說明
- 安全性檢查已考慮

---

### 實作品質標準

**必須達到**:
- [ ] 無語法錯誤
- [ ] 符合規格定義
- [ ] 基本功能可用
- [ ] 無明顯安全漏洞（SQL Injection、XSS）

**建議達到**:
- [ ] 程式碼遵循專案規範
- [ ] 適當的錯誤處理
- [ ] 合理的效能（無 N+1 查詢）

---

## 常見錯誤與預防

### 錯誤 1: 規格不完整就開始實作

**症狀**: 實作過程中頻繁猜測「應該怎麼做」

**預防**:
- 使用 Validate 步驟強制檢查
- Specs 必須詳細到「無需思考就能實作」

---

### 錯誤 2: 實作偏離規格

**症狀**: 完成的功能與規格不符

**預防**:
- 實作前先讀取規格
- 每個 Task 完成立即對照規格檢查
- 使用規格中的範例進行測試

---

### 錯誤 3: 忽略業務規則

**症狀**: 缺少驗證邏輯、錯誤處理不完整

**預防**:
- 業務規則必須在程式碼中實現
- 每個規則都要有對應的驗證或約束
- 測試時必須驗證規則是否生效

---

### 錯誤 4: 累積技術債

**症狀**: 遇到錯誤繼續執行，留待「稍後修復」

**預防**:
- 錯誤立即修復，不標記 completed
- 創建修復子任務追蹤
- 所有任務完成前不進入下一階段

---

## 使用範例

### 範例 1: 新增評分功能

```bash
/implement 新增業務員評分與評論功能
```

**執行流程**:
1. 建立 `openspec/changes/rating-feature/`
2. 產出 `proposal.md` → 確認需求（評分範圍、匿名性、權限）
3. 產出 `specs/api.md` → 定義 7 個 API 端點
4. 產出 `specs/data-model.md` → 定義 ratings 資料表
5. 產出 `specs/business-rules.md` → 定義 11 條業務規則
6. 產出 `tasks.md` → 拆解為 18 個實作任務
7. 驗證規格完整性
8. 逐步實作 18 個任務
9. 歸檔到 `openspec/specs/`

---

### 範例 2: 修復 Bug

```bash
/implement 修復購物車計算錯誤
```

**執行流程**:
1. 建立 `openspec/changes/fix-cart-calculation/`
2. 產出 `proposal.md` → 說明 Bug 現象、根本原因、修復方案
3. 產出 `specs/business-rules.md` → 定義正確的計算規則
4. 產出 `tasks.md` → 拆解修復步驟
5. 實作修復
6. 歸檔（更新相關業務規則文件）

---

## 總結

**核心價值**:
- 🎯 **規範驅動** - 規格先行，降低幻覺
- 📋 **系統化** - 流程標準化，可追溯
- ✅ **品質保證** - 多重驗證，減少錯誤
- 📚 **可維護** - 規範庫持續更新

**適用場景**:
- ✅ 新功能開發
- ✅ Bug 修復
- ✅ 重構優化
- ✅ 所有需要修改程式碼的工作

**不適用場景**:
- ❌ 純探索性研究（使用 Task tool with explore agent）
- ❌ 文件撰寫（直接編輯即可）
- ❌ 臨時性測試

---

**開始執行**: 使用 `/implement [功能描述]` 啟動規範驅動開發流程
