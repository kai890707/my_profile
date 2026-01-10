# 撰寫詳細規格

**變更名稱**: $ARGUMENTS

**任務**: 撰寫完整、無歧義的技術規格文件

---

## 🔴 重要：使用軟體架構師 Agent

**所有 Specification 階段必須使用 `software-architect` agent 進行技術設計**

```
啟動 software-architect agent 進行系統架構設計：
- 將業務需求轉化為技術方案
- 設計資料庫結構與索引策略
- 設計 API 架構與介面
- 考慮效能、安全性、可擴展性
- 輸出完整的技術規格文件
```

**software-architect 負責**：
- ✅ 系統架構設計（分層架構、清潔架構、微服務等）
- ✅ 資料庫設計與優化（索引、分片、快取策略）
- ✅ API 設計（RESTful、版本控制、安全性）
- ✅ 效能設計（QPS 目標、回應時間、擴展策略）
- ✅ 安全架構（認證、授權、加密、OWASP Top 10）
- ✅ 領域驅動設計（DDD）原則應用

**工作流程**：
```
Task tool:
- subagent_type: software-architect
- prompt: 根據 proposal.md 設計技術架構和規格
```

詳見：`.claude/agents/software-architect.md`

---

## 前置條件

- ✅ Proposal 已完成並通過確認
- ✅ 變更目錄已存在: `openspec/changes/<feature-name>/`
- ✅ 所有模糊問題已解決

---

## 目標

撰寫詳細到「無需解釋就能實作」的規格文件：

1. **API 規格** - 所有端點的完整定義
2. **資料模型** - 資料庫結構和 Migration 程式碼
3. **業務規則** - 所有驗證邏輯和約束條件
4. **架構設計** - 系統架構和技術選型

---

## 工作流程（由 software-architect agent 執行）

### Step 1: 撰寫 API 規格

**產出**: `openspec/changes/<feature-name>/specs/api.md`

#### 必須包含的內容

對於每個 API 端點：

```markdown
### [HTTP_METHOD] /api/path

**Description**: [一句話說明端點功能]

**Authentication**: Required / Not required
**Authorization**: [角色要求，如 admin, salesperson, user, or public]

**Request Body** (如適用):
```json
{
  "field1": "value",
  "field2": 123
}
```

**Validation**:
- `field1`: required|string|max_length[100]
- `field2`: required|integer|min[1]|max[5]

**Business Rules**:
- BR-001: [參照業務規則編號]
- BR-002: [參照業務規則編號]

**Response (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "field1": "value"
  },
  "message": "操作成功"
}
```

**Error Responses**:
- 400 Bad Request: 輸入驗證失敗
  ```json
  {
    "status": "error",
    "message": "驗證失敗",
    "errors": {
      "field1": ["field1 為必填"]
    }
  }
  ```
- 401 Unauthorized: 未登入
- 403 Forbidden: 權限不足
- 404 Not Found: 資源不存在
- 422 Unprocessable Entity: 業務規則違反

**Examples**:
```bash
# 成功案例
curl -X POST http://localhost:8080/api/endpoint \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "field1": "value",
    "field2": 5
  }'

# 失敗案例（驗證錯誤）
curl -X POST http://localhost:8080/api/endpoint \
  -H "Content-Type: application/json" \
  -d '{
    "field1": "",
    "field2": 10
  }'
```
```

#### 檢查清單

- [ ] 所有端點都有完整的 Request/Response 範例
- [ ] 所有欄位都有驗證規則
- [ ] 所有錯誤情況都有說明
- [ ] 包含實際可用的 curl 範例

---

### Step 2: 撰寫資料模型規格

**產出**: `openspec/changes/<feature-name>/specs/data-model.md`

#### 必須包含的內容

**1. 資料表結構**

```markdown
## New Table: <table_name>

### Purpose
[說明這個資料表的用途]

### Schema

```sql
CREATE TABLE table_name (
  id INT PRIMARY KEY AUTO_INCREMENT COMMENT '主鍵',
  field1 VARCHAR(100) NOT NULL COMMENT '欄位說明',
  field2 INT NOT NULL DEFAULT 0 COMMENT '欄位說明',
  created_at DATETIME NOT NULL COMMENT '建立時間',
  updated_at DATETIME NOT NULL COMMENT '更新時間',

  INDEX idx_field1 (field1),
  FOREIGN KEY (field2) REFERENCES other_table(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Field Definitions

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | INT | NO | AUTO_INCREMENT | 主鍵 |
| field1 | VARCHAR(100) | NO | - | 欄位說明 |
| field2 | INT | NO | 0 | 欄位說明 |
| created_at | DATETIME | NO | - | 建立時間 |
| updated_at | DATETIME | NO | - | 更新時間 |

### Constraints

1. **PRIMARY KEY (id)**: ...
2. **FOREIGN KEY (field2)**: ...
3. **UNIQUE (field1)**: ...

### Indexes

1. **idx_field1**: 用於加速 field1 的查詢
```

**2. Migration 檔案**

```markdown
## Migration File

**File**: `app/Database/Migrations/YYYY-MM-DD-XXXXXX_CreateTableNameTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableNameTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'field1' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            // ... 其他欄位
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('field1');
        $this->forge->addForeignKey('field2', 'other_table', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('table_name');
    }

    public function down()
    {
        $this->forge->dropTable('table_name');
    }
}
```
```

**3. Model 類別**

```markdown
## Model Class

**File**: `app/Models/TableNameModel.php`

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class TableNameModel extends Model
{
    protected $table = 'table_name';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'field1',
        'field2',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'field1' => 'required|max_length[100]',
        'field2' => 'required|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'field1' => [
            'required' => 'field1 為必填',
        ],
    ];

    // Custom methods
    public function getByField1($value)
    {
        return $this->where('field1', $value)->findAll();
    }
}
```
```

#### 檢查清單

- [ ] 所有資料表都有完整的 CREATE TABLE 語句
- [ ] 所有欄位都有類型、約束、註解
- [ ] 所有索引和外鍵都已定義
- [ ] Migration 檔案程式碼完整
- [ ] Model 類別程式碼完整
- [ ] 驗證規則已定義

---

### Step 3: 撰寫業務規則規格

**產出**: `openspec/changes/<feature-name>/specs/business-rules.md`

#### 格式

```markdown
# Business Rules: <功能名稱>

---

## BR-001: [規則標題]

**Rule**: [規則描述]

**Implementation**: [如何實作]
- Database constraint / Application validation

**Example**:
```
[具體範例說明規則如何運作]
```

**Error Handling**:
- HTTP Status: 400 / 403 / 422
- Error Message: "具體錯誤訊息"

---

## BR-002: [下一條規則]

...
```

#### 範例

```markdown
## BR-001: One Rating Per User-Salesperson Pair

**Rule**: 每個使用者對每個業務員只能評分一次

**Implementation**:
- Database: UNIQUE constraint on (user_id, salesperson_id)
- Application: 如果已存在評分，執行 UPDATE 而非 INSERT

**Example**:
```
User A 對 Salesperson X 評分 → 建立 rating #1
User A 再次對 Salesperson X 評分 → 更新 rating #1
User A 對 Salesperson Y 評分 → 建立 rating #2（不同業務員）
```

**Error Handling**:
- HTTP Status: 200 OK (更新成功)
- Response: 返回更新後的評分資料
```

#### 檢查清單

- [ ] 所有業務規則都有編號（BR-001, BR-002...）
- [ ] 每條規則都說明如何實作（資料庫 / 應用層）
- [ ] 每條規則都有具體範例
- [ ] 每條規則都定義錯誤處理

---

### Step 4: 拆解實作任務

**產出**: `openspec/changes/<feature-name>/tasks.md`

#### 格式

```markdown
# Implementation Tasks: <功能名稱>

**Status**: Draft
**Estimated Time**: X hours

---

## Phase 1: Database & Models (估時: X 小時)

- [ ] **Task 1.1**: Create migration for <table_name>
      **檔案**: app/Database/Migrations/YYYY-MM-DD-XXXXXX_CreateTableName.php
      **產出**: Migration 檔案
      **預估**: 15 分鐘

- [ ] **Task 1.2**: Create Model class
      **檔案**: app/Models/TableNameModel.php
      **產出**: Model 類別
      **預估**: 20 分鐘

- [ ] **Task 1.3**: Execute migration and test
      **指令**: php spark migrate
      **驗證**: 資料表已建立，結構正確
      **預估**: 10 分鐘

---

## Phase 2: API Endpoints (估時: X 小時)

- [ ] **Task 2.1**: Create Controller
      **檔案**: app/Controllers/Api/FeatureController.php
      **產出**: Controller 骨架
      **預估**: 15 分鐘

- [ ] **Task 2.2**: Implement POST /api/endpoint
      **檔案**: app/Controllers/Api/FeatureController.php
      **方法**: create()
      **規格**: 參照 specs/api.md 的 POST /api/endpoint
      **預估**: 30 分鐘

- [ ] **Task 2.3**: Update routes
      **檔案**: app/Config/Routes.php
      **產出**: 新增路由定義
      **預估**: 10 分鐘

- [ ] **Task 2.4**: Test API endpoint
      **工具**: curl 或 API 測試腳本
      **驗證**: 符合 specs/api.md 的範例
      **預估**: 15 分鐘

---

## Phase 3: Business Rules Implementation (估時: X 小時)

- [ ] **Task 3.1**: Implement BR-001
      **規則**: [規則標題]
      **位置**: Controller 驗證邏輯
      **預估**: 20 分鐘

---

## Phase 4: Testing & Documentation (估時: X 小時)

- [ ] **Task 4.1**: Manual testing
      **範圍**: 測試所有驗收標準
      **預估**: 30 分鐘

- [ ] **Task 4.2**: Error handling testing
      **範圍**: 測試所有錯誤情況
      **預估**: 20 分鐘

---

## Task Dependencies

```
Task 1.1 → Task 1.2 → Task 1.3
         ↓
       Task 2.1 → Task 2.2 → Task 2.3 → Task 2.4
                           ↓
                         Task 3.1
                           ↓
                         Task 4.1 → Task 4.2
```

---

## Completion Checklist

完成時必須確認:
- [ ] 所有 Tasks 標記為 completed
- [ ] 所有驗收標準可測試
- [ ] 無已知 Bug 或錯誤
- [ ] 程式碼遵循專案規範
```

#### 原則

- 每個 Task 必須是原子性的（不可再拆）
- 每個 Task 必須有明確產出（檔案/方法）
- 每個 Task 預估時間 ≤ 30 分鐘
- Tasks 之間的相依關係必須清楚
- 包含測試和驗證任務

#### 檢查清單

- [ ] 所有需要的檔案都有對應 Task
- [ ] Tasks 順序符合相依關係
- [ ] 每個 Task 都有預估時間
- [ ] 包含測試驗證任務
- [ ] 總預估時間合理

---

## Step 5: 驗證規格完整性

**任務**: 確保規格完整、一致、無歧義

### 完整性檢查

- [ ] **API 規格完整**: 所有端點都有完整定義
- [ ] **資料模型完整**: 所有資料表都有 Migration 和 Model
- [ ] **業務規則完整**: 所有驗證邏輯都已定義
- [ ] **任務清單完整**: 所有實作步驟都已拆解

### 一致性檢查

- [ ] **API ⟷ 資料模型**: Request 欄位在資料表中都有對應
- [ ] **API ⟷ 業務規則**: 錯誤回應對應到業務規則
- [ ] **任務清單 ⟷ 規格**: Tasks 覆蓋所有規格內容

### 清晰性檢查

- [ ] **無歧義**: 規格只有一種理解方式
- [ ] **可實作**: 詳細到無需思考就能實作
- [ ] **可測試**: 所有驗收標準可測試

---

## 品質標準

### Level 2 - 良好 ✅ **最低要求**

- API 有完整 Request/Response 範例
- 資料模型有 Migration 程式碼
- 業務規則明確定義
- 任務清單詳細拆解

### Level 3 - 優秀

- 所有邊界情況都有說明
- 錯誤處理完整覆蓋
- 效能考量已說明
- 安全性檢查已考慮
- 包含完整的測試案例

---

## 常見錯誤

### 錯誤 1: API 規格不夠詳細

❌ 錯誤:
```markdown
### POST /api/ratings
建立評分
```

✅ 正確:
```markdown
### POST /api/ratings

**Description**: 建立或更新業務員評分

**Authentication**: Required
**Authorization**: User (不能評分自己)

**Request Body**:
```json
{
  "salesperson_id": 1,
  "rating": 5,
  "review": "Great service!"
}
```

**Validation**:
- `salesperson_id`: required|integer|exists:salesperson_profiles,id
- `rating`: required|integer|min[1]|max[5]
- `review`: permit_empty|string|max_length[1000]

... (完整的 Response 和 Error Responses)
```

---

### 錯誤 2: 缺少 Migration 程式碼

❌ 錯誤:
```markdown
## New Table: ratings

有這些欄位: id, user_id, salesperson_id, rating, review
```

✅ 正確:
```markdown
## New Table: ratings

### Schema
```sql
CREATE TABLE ratings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ...
);
```

### Migration File
```php
<?php
// 完整的 Migration 程式碼
?>
```

### Model Class
```php
<?php
// 完整的 Model 程式碼
?>
```
```

---

### 錯誤 3: 業務規則太抽象

❌ 錯誤:
```markdown
## BR-001: 評分驗證
評分要符合規則
```

✅ 正確:
```markdown
## BR-001: Rating Value Range

**Rule**: 評分必須是 1-5 之間的整數

**Implementation**:
- Application validation: `rating >= 1 AND rating <= 5`
- Model validation rule: `required|integer|greater_than_equal_to[1]|less_than_equal_to[5]`

**Example**:
```
Valid: 1, 2, 3, 4, 5
Invalid: 0, 6, 3.5, -1, "excellent"
```

**Error Handling**:
- HTTP Status: 422 Unprocessable Entity
- Error Message: "評分必須在 1-5 之間"
```

---

## 完成標準

規格完成時必須:
- ✅ 所有規格檔案已產出
  - `specs/api.md`
  - `specs/data-model.md`
  - `specs/business-rules.md`
  - `tasks.md`
- ✅ 通過完整性、一致性、清晰性檢查
- ✅ 用戶已確認規格正確
- ✅ 無待解決的問題

---

**下一步**: 使用 `/develop <feature-name>` 進入實作階段
