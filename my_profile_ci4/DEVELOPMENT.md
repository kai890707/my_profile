# 開發工作流程指南

**專案**: 業務推廣系統 (Salesperson Promotion System)
**開發方法**: Specification-Driven Development (SDD)
**工具**: OpenSpec + CodeIgniter 4

---

## 目錄

1. [專案概述](#專案概述)
2. [環境設定](#環境設定)
3. [OpenSpec 工作流程](#openspec-工作流程)
4. [開發流程](#開發流程)
5. [API 開發指南](#api-開發指南)
6. [測試指南](#測試指南)
7. [常用指令](#常用指令)

---

## 專案概述

### 系統架構

```
業務推廣系統
├── Backend API (CodeIgniter 4) - 100% 完成
│   ├── JWT 認證系統
│   ├── 三種角色: Admin, Salesperson, User
│   ├── 35 個 API 端點
│   └── 8 個資料表
├── Frontend (待開發)
└── 部署環境: Docker Compose
```

### 技術棧

- **Backend**: CodeIgniter 4.6.4 (PHP 8.1+)
- **Database**: MySQL 8.0
- **Authentication**: JWT (firebase/php-jwt)
- **Containerization**: Docker + Docker Compose
- **Development Methodology**: OpenSpec SDD

### 核心功能

1. **使用者認證** - JWT-based 登入/註冊/刷新令牌
2. **業務員檔案管理** - 個人資料、公司資訊、證照、經歷
3. **搜尋功能** - 多條件搜尋業務員
4. **審核流程** - Admin 審核業務員註冊、公司資訊、證照
5. **權限控制** - RBAC 角色權限管理

---

## 環境設定

### 必要工具

1. **Docker & Docker Compose** - 容器化環境
2. **Node.js & npm** - 用於 OpenSpec (v18+)
3. **Git** - 版本控制
4. **OpenSpec CLI** - SDD 工具

### 安裝步驟

```bash
# 1. Clone 專案
git clone <repository-url>
cd my_profile_ci4

# 2. 安裝 OpenSpec (全域安裝)
npm install -g @fission-ai/openspec@latest

# 3. 設定環境變數
cp env .env
# 編輯 .env 設定資料庫連線等參數

# 4. 啟動 Docker 容器
docker-compose up -d

# 5. 執行資料庫遷移
docker exec -it my_profile_ci4-app-1 php spark migrate

# 6. 執行資料種子 (產業、地區資料)
docker exec -it my_profile_ci4-app-1 php spark db:seed IndustrySeeder
docker exec -it my_profile_ci4-app-1 php spark db:seed RegionSeeder
```

### 驗證安裝

```bash
# 測試 API 是否正常運作
curl http://localhost:8080/api/industries

# 預期輸出: JSON 格式的產業列表
```

---

## OpenSpec 工作流程

### 什麼是 OpenSpec?

OpenSpec 是一套 **Specification-Driven Development (SDD)** 工具，強調「先寫規格，後寫程式」的開發方法。

### 目錄結構

```
openspec/
├── specs/              # 當前系統規範（真實來源）
│   ├── architecture/   # 系統架構文件
│   ├── api/           # API 端點規範
│   └── models/        # 資料模型定義
└── changes/           # 變更提案（待實作功能）
    └── feature-name/  # 單一功能變更
        ├── proposal.md       # 提案說明
        ├── tasks.md         # 實作任務清單
        └── specs/          # 詳細規格
            ├── api.md      # API 變更
            └── data-model.md  # 資料模型變更
```

### SDD 開發循環

```
1. 提案 (Proposal)
   ↓
2. 撰寫規格 (Specification)
   ↓
3. 拆解任務 (Tasks)
   ↓
4. 實作 (Implementation)
   ↓
5. 測試 (Testing)
   ↓
6. 歸檔 (Archive to specs/)
```

---

## 開發流程

### 新增功能的完整流程

**⚠️ 重要**: 所有開發都必須使用 `/implement` 命令，遵循 OpenSpec SDD 流程。

#### 使用 /implement 命令

```bash
/implement [功能描述]
```

**範例**:
```bash
/implement 新增業務員評分與評論功能
/implement 修復購物車計算錯誤
/implement 優化搜尋效能
```

---

### 開發流程詳解

#### Step 1: 建立變更提案 (Proposal)

**命令**: `/implement` 會自動執行，或使用 `/proposal [功能描述]`

**任務**: 明確定義需求和範圍

**關鍵原則**:
- ✅ 使用 `AskUserQuestion` 確認所有模糊點
- ✅ 明確區分 In Scope（本次做）和 Out of Scope（本次不做）
- ✅ 定義可測試的驗收標準
- ❌ 不要猜測或假設未明確的需求

**產出**: `openspec/changes/<feature-name>/proposal.md`

```markdown
# Proposal: <功能名稱>

**Status**: Draft
**Priority**: High / Medium / Low

## Why (問題陳述)
[為什麼需要這個功能]

## What (解決方案)
[這個功能的核心內容]

## Scope (範圍)

### In Scope (本次實作)
- ✅ 功能 1
- ✅ 功能 2

### Out of Scope (未來擴充)
- ❌ 功能 3 - 原因
- ❌ 功能 4 - 原因

## Success Criteria (驗收標準)
- [ ] 標準 1: 具體、可測試
- [ ] 標準 2: 具體、可測試
```

---

#### Step 2: 撰寫詳細規格 (Specifications)

**命令**: `/implement` 會自動執行，或使用 `/spec <feature-name>`

**任務**: 撰寫完整、無歧義的技術規格

**關鍵原則**:
- ✅ 規格必須詳細到「無需解釋就能實作」
- ✅ 所有 API 都有完整 Request/Response 範例
- ✅ 所有資料表都有完整 Migration 和 Model 程式碼
- ✅ 所有業務規則都有明確實作說明

**產出**:
1. `specs/api.md` - API 端點規格
2. `specs/data-model.md` - 資料模型設計
3. `specs/business-rules.md` - 業務規則定義
4. `tasks.md` - 實作任務清單

**API 規格範例** (`specs/api.md`):

```markdown
### POST /api/<endpoint>
**Description**: 功能說明
**Authentication**: Required/Not required
**Authorization**: Role requirements

**Request Body**:
```json
{
  "field": "value"
}
```

**Response (200 OK)**:
```json
{
  "status": "success",
  "data": {}
}
```
```

**資料模型規格** (`specs/data-model.md`):

```markdown
## New Table: <table_name>

### Schema
```sql
CREATE TABLE table_name (...);
```

### Migration File
```php
// CodeIgniter 4 migration code
```

### Model Class
```php
// Model properties and methods
```
```

---

#### Step 3: 驗證規格完整性 (Validate)

**任務**: 確保規格完整、一致、無歧義

**檢查清單**:

**完整性檢查**:
- [ ] 所有 API 端點都有 Request/Response 範例
- [ ] 所有資料表都有 Migration 和 Model 程式碼
- [ ] 所有業務規則都有實作說明

**一致性檢查**:
- [ ] API 的 Request 欄位在資料模型中都有對應
- [ ] 業務規則在 API 和資料模型中都有體現

**清晰性檢查**:
- [ ] 規格無歧義（不需要猜測）
- [ ] 範例完整（可直接使用）
- [ ] 錯誤處理完整

**決策點**: 所有檢查通過才進入實作階段

---

#### Step 4: 拆解實作任務 (Tasks)

**產出**: `tasks.md`

**範例**:

```markdown
## Phase 1: Database & Models
- [ ] Task 1.1: Create migration for table
- [ ] Task 1.2: Create Model class
- [ ] Task 1.3: Test model CRUD

## Phase 2: API Endpoints
- [ ] Task 2.1: Create Controller
- [ ] Task 2.2: Implement POST endpoint
...
```

---

#### Step 5: 實作開發 (Implement)

**命令**: `/implement` 會自動執行，或使用 `/develop <feature-name>`

**任務**: 嚴格按照規格和任務清單實作

**核心原則**:
- ❌ **禁止**: 偏離規格、猜測未定義行為、添加規格外功能
- ✅ **必須**: 嚴格遵循規格、每個任務完成立即驗證、一次只處理一個任務

**工作流程**:

1. **初始化任務追蹤**: 使用 TodoWrite 建立任務清單
2. **逐步實作**: 標記 in_progress → 讀取規格 → 實作 → 驗證 → completed
3. **錯誤處理**: 遇到錯誤立即修復，不標記 completed

**實作步驟**:

1. **建立 Migration**
   ```bash
   docker exec -it my_profile_ci4-app-1 php spark make:migration CreateTableName
   ```

2. **建立 Model**
   ```bash
   docker exec -it my_profile_ci4-app-1 php spark make:model TableNameModel
   ```

3. **建立 Controller**
   ```bash
   docker exec -it my_profile_ci4-app-1 php spark make:controller Api/FeatureController
   ```

4. **更新路由** (`app/Config/Routes.php`)
   ```php
   $routes->group('api', function($routes) {
       $routes->post('endpoint', 'Api\FeatureController::method');
   });
   ```

5. **執行 Migration**
   ```bash
   docker exec -it my_profile_ci4-app-1 php spark migrate
   ```

---

#### Step 6: 歸檔到規範庫 (Archive)

**命令**: `/implement` 會自動執行，或使用 `/archive <feature-name>`

**任務**: 將完成的變更歸檔到 OpenSpec 規範庫

**操作**:

```bash
# 將 API 規格合併到主文件
cat openspec/changes/<feature-name>/specs/api.md >> openspec/specs/api/endpoints.md

# 將資料模型規格合併到主文件
cat openspec/changes/<feature-name>/specs/data-model.md >> openspec/specs/models/data-models.md

# 刪除或歸檔變更提案
mv openspec/changes/<feature-name> openspec/changes/archived/
```

---

## API 開發指南

### Controller 基礎結構

所有 API Controller 都應該繼承 `ResourceController`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class FeatureController extends ResourceController
{
    protected $modelName = 'App\Models\FeatureModel';
    protected $format = 'json';

    public function index()
    {
        return $this->respond([
            'status' => 'success',
            'data' => $this->model->findAll()
        ]);
    }
}
```

### 認證與授權

**需要登入的端點** - 使用 `auth` filter:

```php
$routes->group('api', ['filter' => 'auth'], function($routes) {
    $routes->get('profile', 'Api\UserController::profile');
});
```

**需要特定角色** - 使用 `role` filter:

```php
$routes->group('admin', ['filter' => ['auth', 'role:admin']], function($routes) {
    $routes->get('statistics', 'Api\AdminController::getStatistics');
});
```

**在 Controller 中取得當前使用者**:

```php
protected function getCurrentUserId()
{
    return service('request')->user['id'] ?? null;
}

protected function getCurrentUserRole()
{
    return service('request')->user['role'] ?? null;
}
```

### 統一的 API 回應格式

**成功回應**:

```php
return $this->respond([
    'status' => 'success',
    'data' => $data,
    'message' => '操作成功' // optional
], 200);
```

**錯誤回應**:

```php
return $this->failValidationErrors($errors); // 422
return $this->failUnauthorized('未授權');     // 401
return $this->failForbidden('權限不足');      // 403
return $this->failNotFound('資源不存在');     // 404
return $this->failServerError('伺服器錯誤');  // 500
```

### 資料驗證

在 Model 中定義驗證規則:

```php
protected $validationRules = [
    'field' => 'required|min_length[3]|max_length[100]',
    'email' => 'required|valid_email',
];

protected $validationMessages = [
    'field' => [
        'required' => '欄位為必填',
    ],
];
```

在 Controller 中驗證:

```php
$data = $this->request->getJSON(true);

if (!$this->model->validate($data)) {
    return $this->failValidationErrors($this->model->errors());
}
```

### Base64 檔案上傳處理

處理 Base64 編碼的檔案（頭像、證照等）:

```php
private function processBase64File($base64String)
{
    // 解析 data:image/jpeg;base64,<data> 格式
    if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64String, $matches)) {
        $mime = $matches[1];
        $data = base64_decode($matches[2]);
        $size = strlen($data);

        // 驗證檔案大小 (例如: 5MB 限制)
        if ($size > 5 * 1024 * 1024) {
            return null;
        }

        // 驗證 MIME 類型
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($mime, $allowedMimes)) {
            return null;
        }

        return [
            'data' => $data,
            'mime' => $mime,
            'size' => $size
        ];
    }

    return null;
}
```

---

## 測試指南

### 手動 API 測試

**1. 登入取得 Token**:

```bash
# 登入
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "password123"
  }'

# 儲存回應中的 access_token
export TOKEN="<your-access-token>"
```

**2. 測試需要認證的端點**:

```bash
curl http://localhost:8080/api/user/info \
  -H "Authorization: Bearer $TOKEN"
```

**3. 測試需要特定角色的端點**:

```bash
# Admin only
curl http://localhost:8080/api/admin/statistics \
  -H "Authorization: Bearer $TOKEN"
```

### 自動化測試

使用 PHPUnit 撰寫測試（未來擴充）:

```bash
docker exec -it my_profile_ci4-app-1 vendor/bin/phpunit
```

---

## 常用指令

### Docker 容器管理

```bash
# 啟動容器
docker-compose up -d

# 停止容器
docker-compose down

# 查看容器狀態
docker-compose ps

# 查看容器日誌
docker-compose logs -f app

# 進入容器 shell
docker exec -it my_profile_ci4-app-1 bash
```

### CodeIgniter 指令

```bash
# 建立 Migration
docker exec -it my_profile_ci4-app-1 php spark make:migration CreateTableName

# 執行 Migration
docker exec -it my_profile_ci4-app-1 php spark migrate

# 回滾 Migration
docker exec -it my_profile_ci4-app-1 php spark migrate:rollback

# 建立 Model
docker exec -it my_profile_ci4-app-1 php spark make:model ModelName

# 建立 Controller
docker exec -it my_profile_ci4-app-1 php spark make:controller ControllerName

# 建立 Seeder
docker exec -it my_profile_ci4-app-1 php spark make:seeder SeederName

# 執行 Seeder
docker exec -it my_profile_ci4-app-1 php spark db:seed SeederName

# 查看路由列表
docker exec -it my_profile_ci4-app-1 php spark routes
```

### OpenSpec 指令

```bash
# 建立新變更提案
openspec change create <feature-name>

# 查看所有變更提案
openspec change list

# 顯示變更提案狀態
openspec change status <feature-name>

# 歸檔已完成的變更
openspec change archive <feature-name>

# 驗證規格格式
openspec validate
```

### 資料庫管理

```bash
# 透過 phpMyAdmin 管理 (http://localhost:8081)
# 帳號: root
# 密碼: 123456

# 或使用 MySQL CLI
docker exec -it my_profile_ci4-db-1 mysql -uroot -p123456 my_profile_db
```

---

## 開發最佳實踐

### 1. 永遠先寫規格

- 在寫程式前，先撰寫完整的 API 和資料模型規格
- 規格應該清楚定義輸入、輸出、驗證規則、錯誤處理
- 讓規格成為團隊溝通和驗收的基準

### 2. 遵循 RESTful 原則

- `GET` - 查詢資料（不修改）
- `POST` - 建立新資源
- `PUT` - 完整更新資源
- `PATCH` - 部分更新資源（本專案較少使用）
- `DELETE` - 刪除資源

### 3. 統一的錯誤處理

- 使用適當的 HTTP 狀態碼
- 提供清楚的錯誤訊息（中文）
- 驗證錯誤應包含所有欄位的錯誤詳情

### 4. 安全性考量

- 所有敏感操作都需要認證
- 使用 RBAC 控制權限
- 密碼使用 bcrypt 雜湊
- SQL 使用 Query Builder 防止注入
- CORS 僅允許特定來源

### 5. 資料庫設計

- 使用 Foreign Key 維護參照完整性
- 適當的索引提升查詢效能
- BLOB 儲存檔案時注意大小限制
- Soft Delete 用於重要資料（如 users）

### 6. 程式碼風格

- 遵循 PSR-12 編碼標準
- 使用有意義的變數和函式名稱
- 避免過度巢狀的邏輯（max 3 層）
- 單一職責原則：一個方法只做一件事

---

## 範例：評分功能開發

完整的範例請參考：`openspec/changes/example-rating-feature/`

這個範例展示如何使用 OpenSpec 開發一個完整的新功能（評分與評論系統），包含：

- **proposal.md** - 問題陳述、解決方案、範圍定義
- **tasks.md** - 18 個實作任務分 5 個階段
- **specs/api.md** - 7 個新 API 端點規格
- **specs/data-model.md** - ratings 資料表設計、Model 類別

這是一個「僅供參考」的範例，並未實際實作。可以作為開發新功能時的模板參考。

---

## 故障排除

### 問題 1: Migration 失敗

```bash
# 檢查資料庫連線
docker exec -it my_profile_ci4-db-1 mysql -uroot -p123456 -e "SHOW DATABASES;"

# 檢查 Migration 檔案語法
docker exec -it my_profile_ci4-app-1 php spark migrate:status
```

### 問題 2: JWT Token 驗證失敗

```bash
# 檢查 .env 的 JWT_SECRET 是否正確設定
# 檢查 token 是否過期 (access_token 有效期 1 小時)
# 使用 refresh_token 取得新的 access_token
```

### 問題 3: CORS 錯誤

檢查 `app/Config/Cors.php` 的設定：

```php
'allowedOrigins' => [
    'http://localhost:3000',  // 加入你的前端網址
],
```

### 問題 4: 權限不足 (403)

- 檢查使用者角色是否正確
- 檢查路由的 filter 設定是否正確
- 使用正確角色的帳號測試

---

## 參考資源

- [CodeIgniter 4 使用手冊](https://codeigniter.com/user_guide/)
- [OpenSpec GitHub](https://github.com/Fission-AI/OpenSpec)
- [RESTful API 設計指南](https://restfulapi.net/)
- [JWT Introduction](https://jwt.io/introduction)

---

## 聯絡資訊

如有問題或建議，請聯繫專案維護者。

**最後更新**: 2026-01-08
