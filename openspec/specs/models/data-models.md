# Data Models Specification

**Database**: MySQL 8.0
**Charset**: utf8mb4_general_ci
**Engine**: InnoDB

---

## Core Tables

### users
**Purpose**: User accounts (三種角色)

```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'salesperson', 'user') NOT NULL,
  status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
  email_verified_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  INDEX idx_email (email),
  INDEX idx_username (username),
  INDEX idx_role (role)
);
```

**Fields**:
- `password_hash`: bcrypt hashed password
- `role`: User role (RBAC)
- `status`: Account status
  - `pending`: Awaiting admin approval
  - `active`: Approved and active
  - `inactive`: Disabled by admin

---

### salesperson_profiles
**Purpose**: 業務員個人資料

```sql
CREATE TABLE salesperson_profiles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  company_id INT NULL,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(10) NULL,
  bio TEXT NULL,
  specialties TEXT NULL,
  service_regions JSON NULL,
  avatar_data MEDIUMBLOB NULL,
  avatar_mime VARCHAR(50) NULL,
  avatar_size INT NULL,
  approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  approved_by INT NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_approval_status (approval_status)
);
```

**Special Fields**:
- `service_regions`: JSON array of region names
- `avatar_data`: BLOB for image storage (max 16MB)
- `approval_status`: Profile approval state

**Approval Rules**:
- Initial creation: `pending`
- General updates (bio, phone): Keep current status
- Avatar upload: Reset to `pending`

---

### companies
**Purpose**: 公司資訊 (機敏資料，需審核)

```sql
CREATE TABLE companies (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(200) NOT NULL,
  tax_id VARCHAR(8) UNIQUE NOT NULL,
  industry_id INT NOT NULL,
  address VARCHAR(255) NULL,
  phone VARCHAR(20) NULL,
  approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  created_by INT NOT NULL,
  approved_by INT NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (industry_id) REFERENCES industries(id),
  FOREIGN KEY (created_by) REFERENCES users(id),
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE idx_tax_id (tax_id),
  INDEX idx_industry_id (industry_id),
  INDEX idx_approval_status (approval_status)
);
```

**Validation**:
- `tax_id`: Exactly 8 digits, unique
- `approval_status`: Always requires admin approval

---

### certifications
**Purpose**: 證照/資格證明 (需審核)

```sql
CREATE TABLE certifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  issuer VARCHAR(200) NOT NULL,
  issue_date DATE NOT NULL,
  expiry_date DATE NULL,
  file_data MEDIUMBLOB NULL,
  file_mime VARCHAR(50) NULL,
  file_size INT NULL,
  approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  approved_by INT NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_approval_status (approval_status)
);
```

**File Storage**:
- Supported formats: JPG, PNG, PDF
- Max size: 5MB
- Storage: BLOB in database

---

### experiences
**Purpose**: 工作經歷 (一般資料，無需審核)

```sql
CREATE TABLE experiences (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  company VARCHAR(200) NOT NULL,
  position VARCHAR(200) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NULL,
  description TEXT NULL,
  sort_order INT DEFAULT 0,
  approval_status ENUM('pending', 'approved') DEFAULT 'approved',
  approved_by INT NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id)
);
```

**Note**: Work experiences默認為`approved`狀態，不需要審核流程。

---

## System Tables

### industries
**Purpose**: 產業類別 (系統設定)

```sql
CREATE TABLE industries (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) UNIQUE NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE idx_name (name),
  UNIQUE idx_slug (slug)
);
```

**Seeded Data** (10 categories):
- 科技資訊, 金融服務, 製造業, 醫療健康, 教育培訓
- 零售批發, 房地產, 餐飲服務, 旅遊觀光, 媒體娛樂

---

### regions
**Purpose**: 地區選項 (系統設定)

```sql
CREATE TABLE regions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  parent_id INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (parent_id) REFERENCES regions(id) ON DELETE SET NULL,
  UNIQUE idx_slug (slug),
  INDEX idx_parent_id (parent_id)
);
```

**Seeded Data** (22 regions):
- 北部: 台北市, 新北市, 基隆市, 桃園市, 新竹市, 新竹縣
- 中部: 苗栗縣, 台中市, 彰化縣, 南投縣, 雲林縣
- 南部: 嘉義市, 嘉義縣, 台南市, 高雄市, 屏東縣
- 東部: 宜蘭縣, 花蓮縣, 台東縣
- 離島: 澎湖縣, 金門縣, 連江縣

---

### approval_logs
**Purpose**: 審核記錄 (稽核追蹤)

```sql
CREATE TABLE approval_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  approvable_type VARCHAR(50) NOT NULL,
  approvable_id INT NOT NULL,
  action ENUM('approved', 'rejected') NOT NULL,
  admin_id INT NOT NULL,
  reason TEXT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (admin_id) REFERENCES users(id),
  INDEX idx_approvable (approvable_type, approvable_id),
  INDEX idx_admin_id (admin_id)
);
```

**approvable_type Options**:
- `user`: User registration approval
- `company`: Company information approval
- `certification`: Certification approval
- `experience`: Experience approval (rarely used)

---

## Entity Relationships

```
users (1) ─────< (N) salesperson_profiles
                         │
                         └──< (1) companies
                                   │
                                   └──< (1) industries

users (1) ─────< (N) certifications
users (1) ─────< (N) experiences
users (1) ─────< (N) approval_logs (as admin_id)

regions (1) ─────< (N) regions (self-reference for parent_id)
```

## Data Integrity Rules

1. **Cascade Delete**:
   - Delete user → Delete salesperson_profiles, certifications, experiences

2. **Set NULL on Delete**:
   - Delete company → Set salesperson_profiles.company_id = NULL
   - Delete admin → Set approved_by = NULL

3. **Unique Constraints**:
   - users.email, users.username
   - companies.tax_id
   - industries.name, industries.slug
   - regions.slug

4. **Required Fields**:
   - All tables: created_at, updated_at
   - Users: username, email, password_hash, role
   - Salesperson: user_id, full_name
   - Companies: name, tax_id, industry_id

---

## Migration Files

All migrations are located in `app/Database/Migrations/`:
- `2026-01-07-122625_CreateUsersTable.php`
- `2026-01-07-122916_CreateIndustriesTable.php`
- `2026-01-07-122926_CreateCompaniesTable.php`
- `2026-01-07-122929_ModifyExperiencesTable.php`
- `2026-01-07-122932_CreateCertificationsTable.php`
- `2026-01-07-122936_CreateRegionsTable.php`
- `2026-01-07-122939_CreateApprovalLogsTable.php`
- `2026-01-07-123719_CreateSalespersonProfilesTable.php`

## Model Classes

All models are located in `app/Models/`:
- `UserModel.php`
- `SalespersonProfileModel.php`
- `CompanyModel.php`
- `CertificationModel.php`
- `ExperienceModel.php`
- `IndustryModel.php`
- `RegionModel.php`
- `ApprovalLogModel.php`

**Model Features**:
- Auto timestamps: `created_at`, `updated_at`
- Soft deletes: `deleted_at` (users only)
- Password hashing: Automatic in UserModel
- Validation rules: Built-in field validation

---

## Feature: Swagger API Documentation

**Added**: 2026-01-08
**Change**: swagger-api-documentation

### No Database Changes

本功能不需要新增資料表，Swagger API 文件是基於現有 Controller 註解動態生成的文件系統。

### Configuration Structure

#### SwaggerController Class

**File**: `app/Controllers/Api/SwaggerController.php`

**Purpose**: 提供 Swagger UI 介面和 OpenAPI JSON 規格端點

**Class Properties**:
| Property | Type | Description |
|----------|------|-------------|
| $format | string | 回應格式（預設 json） |
| $controllersPath | string | Controller 掃描路徑 |
| $enabled | bool | Swagger 是否啟用 |

**Class Methods**:
| Method | Visibility | Return Type | Description |
|--------|-----------|-------------|-------------|
| __construct() | public | void | 初始化 Controller，設定掃描路徑 |
| index() | public | Response | 顯示 Swagger UI HTML |
| json() | public | Response | 返回 OpenAPI JSON |
| getSwaggerUI() | protected | string | 生成 Swagger UI HTML 內容 |

#### Environment Variables

**File**: `.env`

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| SWAGGER_ENABLED | boolean | true | 是否啟用 Swagger UI |
| SWAGGER_OPENAPI_VERSION | string | 3.0.0 | OpenAPI 規格版本 |
| SWAGGER_API_TITLE | string | API Documentation | API 文件標題 |
| SWAGGER_API_VERSION | string | 1.0.0 | API 版本號 |
| SWAGGER_UI_DEEP_LINKING | boolean | true | 是否啟用深度連結 |
| SWAGGER_CACHE_ENABLED | boolean | false | 是否快取 OpenAPI JSON |

#### Dependencies

**Composer**: `zircote/swagger-php: ^4.0`

---


---

## Feature: User Registration Refactor

**Added**: 2026-01-11
**Change**: user-registration-refactor

### Database Schema

#### 1. Users Table Migration

**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_add_salesperson_fields_to_users_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // 使用者角色
            $table->enum('role', ['user', 'salesperson', 'admin'])
                ->default('user')
                ->after('password');

            // 業務員審核狀態
            $table->enum('salesperson_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->after('role')
                ->comment('null=一般使用者, pending=未審核, approved=已審核, rejected=已拒絕');

            // 業務員申請/升級時間
            $table->timestamp('salesperson_applied_at')
                ->nullable()
                ->after('salesperson_status');

            // 業務員審核通過時間
            $table->timestamp('salesperson_approved_at')
                ->nullable()
                ->after('salesperson_applied_at');

            // 審核拒絕原因
            $table->text('rejection_reason')
                ->nullable()
                ->after('salesperson_approved_at');

            // 可重新申請的時間
            $table->timestamp('can_reapply_at')
                ->nullable()
                ->after('rejection_reason');

            // 付費會員標記（預留）
            $table->boolean('is_paid_member')
                ->default(false)
                ->after('can_reapply_at');

            // Indexes
            $table->index('role');
            $table->index('salesperson_status');
            $table->index(['role', 'salesperson_status'], 'idx_role_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('idx_role_status');
            $table->dropIndex(['salesperson_status']);
            $table->dropIndex(['role']);

            $table->dropColumn([
                'role',
                'salesperson_status',
                'salesperson_applied_at',
                'salesperson_approved_at',
                'rejection_reason',
                'can_reapply_at',
                'is_paid_member',
            ]);
        });
    }
};
```

#### 2. Companies Table Migration (簡化)

**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_simplify_companies_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            // 1. 新增 is_personal 欄位
            $table->boolean('is_personal')
                ->default(false)
                ->after('tax_id')
                ->comment('是否為個人工作室');

            // 2. 將 tax_id 改為 nullable
            $table->string('tax_id', 50)
                ->nullable()
                ->change();

            // 3. 移除不需要的欄位
            $table->dropForeign(['industry_id']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['industry_id']);
            $table->dropIndex(['approval_status']);

            $table->dropColumn([
                'industry_id',
                'address',
                'phone',
                'approval_status',
                'rejected_reason',
                'approved_by',
                'approved_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            // 恢復欄位
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // 移除新增的欄位
            $table->dropColumn('is_personal');

            // 恢復 foreign keys 和 indexes
            $table->foreign('industry_id')
                ->references('id')
                ->on('industries')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('industry_id');
            $table->index('approval_status');

            // 將 tax_id 改回 not nullable
            $table->string('tax_id', 50)
                ->nullable(false)
                ->change();
        });
    }
};
```

#### 3. SalespersonProfiles Table Migration

**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_make_company_id_nullable_in_salesperson_profiles.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // 將 company_id 改為 nullable（支援獨立業務員）
            $table->unsignedBigInteger('company_id')
                ->nullable()
                ->change();

            // 移除舊的審核欄位（改用 Users table 的 salesperson_status）
            if (Schema::hasColumn('salesperson_profiles', 'approval_status')) {
                $table->dropColumn([
                    'approval_status',
                    'rejected_reason',
                    'approved_by',
                    'approved_at',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // 恢復 company_id 為 not nullable
            $table->unsignedBigInteger('company_id')
                ->nullable(false)
                ->change();

            // 恢復審核欄位
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
        });
    }
};
```

---

### Models
---
