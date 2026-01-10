# Backend 開發規範 (Laravel 11)

**專案**: YAMU Backend API
**框架**: Laravel 11 + PHP 8.4
**開發方法**: OpenSpec Specification-Driven Development (SDD)
**最後更新**: 2026-01-11

---

## 🚀 快速開始

### 使用 OpenSpec Commands 開發新功能

```bash
# 在專案根目錄執行
/implement [功能描述]
```

這會自動執行完整的 Backend SDD 流程:
1. Create Proposal → 確認需求
2. Write Specs → API + DB Schema + Business Rules + Tests
3. Break Down Tasks → 拆解開發任務
4. Validate → 驗證規格完整性
5. Implement → 實作 Laravel 程式碼
6. Archive → 歸檔到規範庫

**Commands 參考**: `../.claude/commands/README.md`

---

## 📁 專案結構

```
my_profile_laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # API Controllers
│   │   ├── Middleware/     # 中介層
│   │   └── Requests/       # Form Requests (驗證)
│   ├── Models/             # Eloquent Models
│   ├── Services/           # 業務邏輯服務
│   ├── Repositories/       # 資料存取層 (可選)
│   ├── Policies/           # 授權策略
│   └── Exceptions/         # 自定義例外
├── database/
│   ├── migrations/         # 資料庫遷移
│   ├── seeders/           # 資料種子
│   └── factories/         # Model Factories
├── tests/
│   ├── Feature/           # Feature Tests (API 測試)
│   ├── Unit/              # Unit Tests (邏輯測試)
│   └── Pest.php           # Pest 配置
├── routes/
│   ├── api.php            # API 路由
│   └── web.php            # Web 路由
├── config/                # 配置檔案
├── docker/                # Docker 配置
├── docs/                  # API 文檔 📚
│   ├── README.md          # 文檔索引
│   ├── api-reference.md   # API 參考手冊
│   ├── database-schema.md # 資料庫 Schema
│   └── deployment.md      # 部署指南
└── CLAUDE.md              # 本文件
```

---

## 🛠️ 技術棧

### Core
- **Framework**: Laravel 11
- **Language**: PHP 8.4
- **Database**: MySQL 8.0
- **Cache**: Redis (可選)

### Development
- **Testing**: Pest 3.x
- **Static Analysis**: PHPStan Level 9
- **Code Style**: Laravel Pint
- **API Docs**: OpenAPI 3.1

### Authentication
- **JWT**: tymon/jwt-auth
- **Tokens**: Access Token + Refresh Token
- **Expiry**: Access 60min, Refresh 20160min (14 days)

---

## 📊 系統規格

完整的 Backend 規格請參考 OpenSpec 規範庫:

- **API 規格**: `../openspec/specs/backend/api.md` (31 個端點)
- **資料庫 Schema**: `../openspec/specs/backend/database-schema.md` (15 張表)
- **業務規則**: `../openspec/specs/backend/business-rules.md`
- **測試規格**: `../openspec/specs/backend/tests.md` (201 個測試)
- **規格總覽**: `../openspec/specs/backend/README.md`

---

## 📚 核心文檔 (必讀)

### 技術文檔 (docs/)

1. **[文檔索引](./docs/README.md)** - 所有文檔的入口
2. **[API 參考手冊](./docs/api-reference.md)** - 完整 API 端點說明
   - 認證 API (登入、註冊、Token 刷新)
   - 業務員 API (CRUD、搜尋、審核)
   - 管理 API (統計、使用者管理)
3. **[資料庫 Schema](./docs/database-schema.md)** - 資料表結構與關聯
   - ER Diagram
   - 表結構定義
   - 索引與約束
4. **[部署指南](./docs/deployment.md)** - Docker 部署流程

---

## 🔧 開發流程

### 1. 環境設置

```bash
cd my_profile_laravel

# 啟動 Docker 容器
docker-compose up -d

# 安裝依賴
docker exec -it my_profile_laravel_app composer install

# 執行資料庫遷移
docker exec -it my_profile_laravel_app php artisan migrate

# 執行資料種子
docker exec -it my_profile_laravel_app php artisan db:seed

# 測試 API
curl http://localhost:8080/api/health
```

**服務端口**:
- API: http://localhost:8080
- MySQL: localhost:3307
- OpenAPI Docs: http://localhost:8080/docs/api

### 2. 開發新功能

**推薦方式** - 使用 OpenSpec Commands:

```bash
cd /path/to/project/root
/implement 新增評分系統 API
```

**手動方式** - 按步驟執行:

1. **建立變更提案**
   ```bash
   /proposal 新增評分系統 API
   ```

2. **撰寫詳細規格**
   ```bash
   /spec rating-system
   ```
   產出: `../openspec/changes/rating-system/specs/`
   - `api.md` - API 端點定義
   - `database.md` - 資料庫 Schema
   - `business-rules.md` - 業務規則
   - `tests.md` - 測試案例

3. **實作功能**
   ```bash
   /develop rating-system
   ```

4. **歸檔規格**
   ```bash
   /archive rating-system
   ```

---

## 📝 開發規範

### API 設計原則

1. **RESTful 風格**
   - 使用標準 HTTP 方法 (GET, POST, PUT, DELETE)
   - 資源導向的 URL 設計
   - 統一的回應格式

2. **版本控制**
   - API 路由使用 `/api/` 前綴
   - 未來版本使用 `/api/v2/` 等

3. **錯誤處理**
   - 使用標準 HTTP 狀態碼
   - 提供清楚的錯誤訊息
   - 包含錯誤代碼 (error_code)

### 程式碼組織

#### Controllers (app/Http/Controllers)

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Create a new user
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }
}
```

**原則**:
- Controller 只處理 HTTP 請求/回應
- 業務邏輯放在 Service
- 使用 Form Request 進行驗證
- 使用依賴注入

#### Services (app/Services)

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        // 業務邏輯處理
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'active';

        return User::create($data);
    }
}
```

**原則**:
- Service 包含業務邏輯
- 可重用的邏輯抽取成 Service
- Service 方法應該單一職責
- 使用類型提示

#### Models (app/Models)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalespersonProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'bio',
        'years_of_experience',
    ];

    protected $casts = [
        'years_of_experience' => 'integer',
    ];

    /**
     * Get the user that owns the profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**原則**:
- 使用 $fillable 或 $guarded
- 定義關聯方法
- 使用 $casts 轉型
- 使用 HasFactory trait

#### Form Requests (app/Http/Requests)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered',
        ];
    }
}
```

**原則**:
- 驗證邏輯放在 Form Request
- 自定義錯誤訊息
- 使用陣列語法定義規則

### 命名規範

1. **Classes**: PascalCase (`UserController`, `UserService`)
2. **Methods**: camelCase (`createUser`, `getUserById`)
3. **Variables**: camelCase (`$userData`, `$userId`)
4. **Constants**: UPPER_SNAKE_CASE (`MAX_LOGIN_ATTEMPTS`)
5. **Database Tables**: snake_case 複數 (`users`, `salesperson_profiles`)
6. **Database Columns**: snake_case (`user_id`, `created_at`)

### 資料庫設計

#### Migrations (database/migrations)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salesperson_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained();
            $table->text('bio')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->timestamps();

            // 索引
            $table->index('user_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesperson_profiles');
    }
};
```

**原則**:
- 使用 `foreignId()` 和 `constrained()` 定義外鍵
- 為經常查詢的欄位加索引
- 使用 `nullable()` 標記可空欄位
- 提供 `down()` 方法以支援回滾

---

## 🧪 測試策略

**完整指南**: `docs/testing.md`

### 測試命令

```bash
# 運行所有測試
docker exec -it my_profile_laravel_app composer test

# 測試覆蓋率
docker exec -it my_profile_laravel_app composer test:coverage

# PHPStan 靜態分析
docker exec -it my_profile_laravel_app composer analyse

# Code Style 檢查
docker exec -it my_profile_laravel_app composer format
```

### 測試類型

#### 1. Feature Tests (tests/Feature)

測試 API 端點的完整流程：

```php
<?php

use App\Models\User;

test('user can register successfully', function () {
    $response = $this->postJson('/api/auth/register', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'name' => 'Test User',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => ['user', 'access_token'],
            'message',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});
```

#### 2. Unit Tests (tests/Unit)

測試單一功能邏輯：

```php
<?php

use App\Services\UserService;

test('user service creates user with hashed password', function () {
    $service = new UserService();

    $user = $service->createUser([
        'email' => 'test@example.com',
        'password' => 'plaintext',
        'name' => 'Test User',
    ]);

    expect($user->password)->not->toBe('plaintext');
    expect(Hash::check('plaintext', $user->password))->toBeTrue();
});
```

### 測試覆蓋目標

- Feature Tests: 95%+ 覆蓋率
- Unit Tests: 90%+ 覆蓋率
- PHPStan: Level 9 (最嚴格)
- Total: 201+ 測試，80%+ 覆蓋率

---

## 🔐 認證與授權

### JWT 認證流程

```
1. 登入 → POST /api/auth/login
   ↓
2. 取得 Access Token + Refresh Token
   ↓
3. 使用 Access Token 存取 API
   - Header: Authorization: Bearer {access_token}
   ↓
4. Access Token 過期 → POST /api/auth/refresh
   - Body: { refresh_token }
   ↓
5. 取得新的 Access Token
```

### 中介層 (Middleware)

```php
// routes/api.php

Route::middleware('auth:api')->group(function () {
    // 需要認證的路由
    Route::get('/profile', [ProfileController::class, 'show']);
});

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // 需要 admin 角色的路由
    Route::get('/admin/users', [AdminController::class, 'users']);
});
```

### 授權策略 (Policies)

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalespersonProfile;

class SalespersonProfilePolicy
{
    public function update(User $user, SalespersonProfile $profile): bool
    {
        // 只有擁有者或管理員可以更新
        return $user->id === $profile->user_id || $user->role === 'admin';
    }
}
```

---

## 📊 API 回應格式

### 成功回應

```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "Test User"
  },
  "message": "Operation successful"
}
```

### 錯誤回應

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email has already been taken."]
    }
  }
}
```

### 分頁回應

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  },
  "links": {
    "first": "http://api.example.com?page=1",
    "last": "http://api.example.com?page=7",
    "prev": null,
    "next": "http://api.example.com?page=2"
  }
}
```

---

## 📚 參考文檔

### 專案文檔
- [docs/README.md](./docs/README.md) - 技術文檔索引
- [docs/api-reference.md](./docs/api-reference.md) - API 參考手冊
- [docs/database-schema.md](./docs/database-schema.md) - 資料庫 Schema
- [../README.md](../README.md) - 專案總覽

### OpenSpec 規範
- [Backend 規格總覽](../openspec/specs/backend/README.md)
- [API 規格](../openspec/specs/backend/api.md)
- [資料庫 Schema](../openspec/specs/backend/database-schema.md)

### Commands 使用
- [Commands README](../.claude/commands/README.md)
- [工作流程圖](../.claude/commands/WORKFLOW.md)

### Laravel 官方文檔
- [Laravel 11.x Documentation](https://laravel.com/docs/11.x)
- [Laravel API Resources](https://laravel.com/docs/11.x/eloquent-resources)
- [Laravel Testing](https://laravel.com/docs/11.x/testing)

---

## 🐛 常見問題

### Q: 如何新增 API 端點?

A:
1. 撰寫 API 規格 (使用 /spec 命令)
2. 建立 Controller 和 Form Request
3. 定義路由 (routes/api.php)
4. 撰寫測試
5. 更新 OpenAPI 文檔

### Q: 如何處理資料庫變更?

A:
1. 建立 Migration: `php artisan make:migration create_xxx_table`
2. 定義 Schema
3. 執行 Migration: `php artisan migrate`
4. 更新 Model
5. 更新測試

### Q: 如何進行權限控制?

A:
```php
// 使用 Middleware
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Admin only routes
});

// 使用 Policy
$this->authorize('update', $salespersonProfile);
```

### Q: 如何進行資料驗證?

A:
```php
// 建立 Form Request
php artisan make:request CreateUserRequest

// 在 Controller 使用
public function store(CreateUserRequest $request)
{
    // $request->validated() 已驗證過的資料
}
```

---

## ⚠️ 重要原則

### 規範驅動開發

❌ **禁止**:
- 未撰寫 API 規格就開始寫 Controller
- 規格模糊就開始實作
- 實作過程中隨意偏離規格
- 忽略測試

✅ **必須**:
- 先撰寫完整的 API 規格
- 規格包含所有端點、請求/回應格式
- 定義資料庫 Schema
- 撰寫測試案例

### 代碼品質

❌ **禁止**:
- 在 Controller 寫業務邏輯
- 直接在 Controller 查詢資料庫
- 忽略類型提示
- 缺少錯誤處理

✅ **必須**:
- 業務邏輯放在 Service
- 使用 Form Request 驗證
- 使用 PHPStan Level 9
- 完整的錯誤處理

### 安全性

❌ **禁止**:
- 明文儲存密碼
- 忽略 SQL Injection 防護
- 跳過輸入驗證
- 硬編碼機密資訊

✅ **必須**:
- 使用 Hash::make() 加密密碼
- 使用 Eloquent ORM (防 SQL Injection)
- 所有輸入都要驗證
- 機密資訊存在 .env

---

## 🎯 開發檢查清單

開發新功能前檢查:
- [ ] API 規格已完整
- [ ] 資料庫 Schema 已定義
- [ ] 業務規則已明確
- [ ] 測試案例已列出

開發完成後檢查:
- [ ] 所有測試通過
- [ ] PHPStan Level 9 無錯誤
- [ ] Code Style 符合規範
- [ ] OpenAPI 文檔已更新
- [ ] 規格已歸檔

提交前檢查:
- [ ] 測試覆蓋率達標
- [ ] 無安全漏洞
- [ ] 效能符合要求
- [ ] 錯誤處理完整

---

## 🔄 Git Commit 規範

```
feat: Add rating system API
fix: Fix user authentication bug
test: Add tests for salesperson search
docs: Update API documentation
refactor: Refactor user service
```

**類型**:
- `feat`: 新功能
- `fix`: Bug 修復
- `test`: 測試
- `docs`: 文檔
- `refactor`: 重構
- `perf`: 效能優化
- `chore`: 雜項

---

**維護者**: Development Team
**最後更新**: 2026-01-11
**版本**: 1.0
