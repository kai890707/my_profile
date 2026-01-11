# 資料模型規格

**專案**: 前後端 API 不一致修復
**版本**: 1.0
**最後更新**: 2026-01-11

---

## 概述

本文檔定義 Experiences 和 Certifications 相關的資料模型規格，包含資料表結構、Eloquent Models、API Resources 和 Relationships。

**修復範圍**:
- 建立 Experience Model
- 建立 Certification Model
- 建立對應的 API Resources
- 定義資料庫關聯

---

## 資料庫架構

### Entity Relationship Diagram (ERD)

```
┌──────────────┐
│    Users     │
└──────┬───────┘
       │
       │ 1:1
       ▼
┌──────────────┐         ┌───────────────┐
│ Salesperson  │────<─┤  Companies    │
│  Profiles    │  N:1    └───────────────┘
└──────┬───────┘
       │
       ├── 1:N ────────► ┌───────────────┐
       │                 │  Experiences  │
       │                 └───────────────┘
       │
       └── 1:N ────────► ┌───────────────┐
                         │ Certifications│
                         └───────────────┘
```

---

## 資料表結構

### 1. experiences 資料表

**用途**: 儲存業務員的工作經驗

**Migration 檔案**: `2026_01_09_132427_create_experiences_table.php` (已存在)

**欄位定義**:

| 欄位名 | 型別 | 約束 | 索引 | 預設值 | 說明 |
|-------|------|------|-----|-------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | PRIMARY | - | 主鍵 |
| user_id | BIGINT UNSIGNED | NULLABLE | INDEX, FK | NULL | 使用者 ID（外鍵到 users） |
| company | VARCHAR(200) | NOT NULL | - | - | 公司名稱 |
| position | VARCHAR(200) | NOT NULL | - | - | 職位 |
| start_date | DATE | NOT NULL | - | - | 開始日期 |
| end_date | DATE | NULLABLE | - | NULL | 結束日期 |
| description | TEXT | NULLABLE | - | NULL | 工作描述 |
| approval_status | ENUM('pending', 'approved', 'rejected') | NOT NULL | - | 'approved' | 審核狀態 |
| rejected_reason | TEXT | NULLABLE | - | NULL | 拒絕原因 |
| approved_by | BIGINT UNSIGNED | NULLABLE | FK | NULL | 審核者 ID（外鍵到 users） |
| approved_at | TIMESTAMP | NULLABLE | - | NULL | 審核時間 |
| sort_order | INT UNSIGNED | NOT NULL | - | 0 | 排序順序 |
| created_at | TIMESTAMP | NULLABLE | - | NULL | 建立時間 |
| updated_at | TIMESTAMP | NULLABLE | - | NULL | 更新時間 |

**索引定義**:
```sql
-- 主鍵索引（自動建立）
PRIMARY KEY (id)

-- 使用者索引（加速查詢）
INDEX idx_user_id (user_id)
```

**外鍵定義**:
```sql
-- 關聯到 users 表
FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE

-- 關聯到 users 表（審核者）
FOREIGN KEY (approved_by)
    REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
```

**Migration 程式碼**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('company', 200);
            $table->string('position', 200);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->text('rejected_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('user_id');

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
```

**資料量估算**:
- 預期業務員數：1,000（第一年）
- 平均每人經驗數：3 筆
- 總資料量：~3,000 筆
- 資料大小：~1MB（第一年）

**效能考量**:
- 讀寫比：80:20（讀多寫少）
- 快取策略：使用者經驗列表快取 10 分鐘
- 分片策略：單表可支援 100 萬筆，暫不分片

---

### 2. certifications 資料表

**用途**: 儲存業務員的證照資料（含檔案）

**Migration 檔案**: `2026_01_09_132426_create_certifications_table.php` (已存在)

**欄位定義**:

| 欄位名 | 型別 | 約束 | 索引 | 預設值 | 說明 |
|-------|------|------|-----|-------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | PRIMARY | - | 主鍵 |
| user_id | BIGINT UNSIGNED | NOT NULL | INDEX, FK | - | 使用者 ID（外鍵到 users） |
| name | VARCHAR(200) | NOT NULL | - | - | 證照名稱 |
| issuer | VARCHAR(200) | NOT NULL | - | - | 發證單位 |
| issue_date | DATE | NULLABLE | - | NULL | 發證日期 |
| expiry_date | DATE | NULLABLE | - | NULL | 到期日期 |
| description | TEXT | NULLABLE | - | NULL | 證照說明 |
| file_data | MEDIUMBLOB | NULLABLE | - | NULL | 檔案內容（Base64 解碼後） |
| file_mime | VARCHAR(50) | NULLABLE | - | NULL | 檔案 MIME 類型 |
| file_size | INT UNSIGNED | NULLABLE | - | NULL | 檔案大小（bytes） |
| approval_status | ENUM('pending', 'approved', 'rejected') | NOT NULL | INDEX | 'pending' | 審核狀態 |
| rejected_reason | TEXT | NULLABLE | - | NULL | 拒絕原因 |
| approved_by | BIGINT UNSIGNED | NULLABLE | FK | NULL | 審核者 ID（外鍵到 users） |
| approved_at | TIMESTAMP | NULLABLE | - | NULL | 審核時間 |
| created_at | TIMESTAMP | NULLABLE | - | NULL | 建立時間 |
| updated_at | TIMESTAMP | NULLABLE | - | NULL | 更新時間 |

**索引定義**:
```sql
-- 主鍵索引（自動建立）
PRIMARY KEY (id)

-- 使用者索引（加速查詢）
INDEX idx_user_id (user_id)

-- 審核狀態索引（管理員篩選用）
INDEX idx_approval_status (approval_status)
```

**外鍵定義**:
```sql
-- 關聯到 users 表
FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE

-- 關聯到 users 表（審核者）
FOREIGN KEY (approved_by)
    REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
```

**Migration 程式碼**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 200);
            $table->string('issuer', 200);
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->string('file_mime', 50)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('approval_status');

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Add MEDIUMBLOB column using raw SQL (database-specific)
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE certifications ADD COLUMN file_data BLOB NULL');
        } else {
            DB::statement('ALTER TABLE certifications ADD COLUMN file_data MEDIUMBLOB NULL AFTER description');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
```

**檔案儲存策略**:
- 儲存方式：資料庫 MEDIUMBLOB 欄位
- 最大檔案大小：16MB (MEDIUMBLOB 限制)
- 支援格式：image/jpeg, image/png, image/jpg, application/pdf
- 編碼方式：前端 Base64 → 後端解碼 → 儲存二進制

**資料量估算**:
- 預期業務員數：1,000（第一年）
- 平均每人證照數：2 筆
- 平均檔案大小：500KB
- 總資料量：~1GB（第一年）

**效能考量**:
- 讀寫比：70:30（讀多寫略多）
- 快取策略：不快取檔案內容（太大）
- GET API 不回傳 file_data（避免傳輸過大）
- 檔案下載需單獨端點（未來考慮）

---

## Eloquent Models

### 1. Experience Model

**檔案位置**: `app/Models/Experience.php`

**完整程式碼**:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Experience Model
 *
 * @property int $id
 * @property int $user_id
 * @property string $company
 * @property string $position
 * @property string $start_date
 * @property string|null $end_date
 * @property string|null $description
 * @property string $approval_status
 * @property string|null $rejected_reason
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read User $user
 * @property-read User|null $approver
 */
class Experience extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'experiences';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'company',
        'position',
        'start_date',
        'end_date',
        'description',
        'approval_status',
        'rejected_reason',
        'approved_by',
        'approved_at',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'user_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'approval_status' => 'string',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the user that owns the experience.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this experience.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include approved experiences.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope a query to only include pending experiences.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope a query to only include rejected experiences.
     */
    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Scope a query to order by sort_order and start_date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
            ->orderBy('start_date', 'desc');
    }
}
```

**說明**:
- **$fillable**: 允許批量賦值的欄位
- **$casts**: 欄位類型轉換（日期、整數等）
- **Relationships**:
  - `user()`: BelongsTo - 屬於哪個使用者
  - `approver()`: BelongsTo - 誰審核的（可選）
- **Scopes**:
  - `approved()`: 只查詢已審核通過的
  - `pending()`: 只查詢待審核的
  - `rejected()`: 只查詢已拒絕的
  - `ordered()`: 按 sort_order 和 start_date 排序

---

### 2. Certification Model

**檔案位置**: `app/Models/Certification.php`

**完整程式碼**:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Certification Model
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $issuer
 * @property string|null $issue_date
 * @property string|null $expiry_date
 * @property string|null $description
 * @property string|null $file_data
 * @property string|null $file_mime
 * @property int|null $file_size
 * @property string $approval_status
 * @property string|null $rejected_reason
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read User $user
 * @property-read User|null $approver
 */
class Certification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'certifications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'issuer',
        'issue_date',
        'expiry_date',
        'description',
        'file_data',
        'file_mime',
        'file_size',
        'approval_status',
        'rejected_reason',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'user_id' => 'integer',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
        'approval_status' => 'string',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'file_data', // 隱藏二進制資料，避免序列化時出錯
    ];

    /**
     * Get the user that owns the certification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this certification.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include approved certifications.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope a query to only include pending certifications.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope a query to only include rejected certifications.
     */
    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Check if the certification has an uploaded file.
     */
    public function hasFile(): bool
    {
        return !empty($this->file_data);
    }

    /**
     * Get the file extension from MIME type.
     */
    public function getFileExtension(): ?string
    {
        if (!$this->file_mime) {
            return null;
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];

        return $mimeToExt[$this->file_mime] ?? null;
    }
}
```

**說明**:
- **$fillable**: 允許批量賦值的欄位（含 file_data）
- **$casts**: 欄位類型轉換
- **$hidden**: 隱藏 file_data（避免序列化大量二進制資料）
- **Relationships**:
  - `user()`: BelongsTo - 屬於哪個使用者
  - `approver()`: BelongsTo - 誰審核的（可選）
- **Scopes**: 同 Experience
- **Helper Methods**:
  - `hasFile()`: 檢查是否有上傳檔案
  - `getFileExtension()`: 從 MIME type 取得副檔名

---

### 3. User Model 擴充

**檔案位置**: `app/Models/User.php`

**新增 Relationships**:
```php
/**
 * Get the experiences for the user.
 */
public function experiences(): HasMany
{
    return $this->hasMany(Experience::class);
}

/**
 * Get the certifications for the user.
 */
public function certifications(): HasMany
{
    return $this->hasMany(Certification::class);
}
```

**完整關聯定義**:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    // ... 其他程式碼

    /**
     * Get the salesperson profile for the user.
     */
    public function salespersonProfile(): HasOne
    {
        return $this->hasOne(SalespersonProfile::class);
    }

    /**
     * Get the experiences for the user.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class)->ordered();
    }

    /**
     * Get the certifications for the user.
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }
}
```

---

## API Resources

### 1. ExperienceResource

**檔案位置**: `app/Http/Resources/ExperienceResource.php`

**完整程式碼**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Experience
 */
class ExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'company' => $this->company,
            'position' => $this->position,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'description' => $this->description,
            'approval_status' => $this->approval_status,
            'rejected_reason' => $this->rejected_reason,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

**說明**:
- 欄位轉換：日期格式化為 ISO 8601 (`Y-m-d` 或完整時間戳)
- 不包含關聯資料（user, approver），保持回應精簡
- 所有欄位都保留（前端需要完整資訊）

---

### 2. CertificationResource

**檔案位置**: `app/Http/Resources/CertificationResource.php`

**完整程式碼**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Certification
 */
class CertificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'issuer' => $this->issuer,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'description' => $this->description,
            'file_data' => null, // 永遠回傳 null（效能考量）
            'file_mime' => $this->file_mime,
            'file_size' => $this->file_size,
            'approval_status' => $this->approval_status,
            'rejected_reason' => $this->rejected_reason,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

**說明**:
- **重要**: `file_data` 永遠回傳 `null`，避免傳輸大量二進制資料
- 保留 `file_mime` 和 `file_size`，讓前端知道檔案類型和大小
- 如需下載檔案，應提供單獨的下載端點（未來考慮）

---

## Model Factories

### 1. ExperienceFactory

**檔案位置**: `database/factories/ExperienceFactory.php`

**完整程式碼**:
```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-10 years', '-1 year');
        $endDate = $this->faker->optional(0.7)->dateTimeBetween($startDate, 'now');

        return [
            'user_id' => User::factory(),
            'company' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $this->faker->optional(0.8)->paragraph(),
            'approval_status' => 'approved', // 預設已審核
            'rejected_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the experience is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the experience is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'rejected',
            'rejected_reason' => $this->faker->sentence(),
        ]);
    }
}
```

---

### 2. CertificationFactory

**檔案位置**: `database/factories/CertificationFactory.php`

**完整程式碼**:
```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $issueDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $expiryDate = $this->faker->optional(0.6)->dateTimeBetween($issueDate, '+5 years');

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement([
                'PMP 專案管理證照',
                'Google Analytics 認證',
                'AWS Certified Solutions Architect',
                'Microsoft Certified: Azure Administrator',
                'Salesforce Certified Administrator',
            ]),
            'issuer' => $this->faker->company(),
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'description' => $this->faker->optional(0.5)->sentence(),
            'file_data' => null, // 測試時不實際產生檔案
            'file_mime' => $this->faker->randomElement(['image/jpeg', 'application/pdf']),
            'file_size' => $this->faker->numberBetween(100000, 2000000),
            'approval_status' => 'pending', // 預設待審核
            'rejected_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the certification is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the certification is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'rejected',
            'rejected_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the certification has a file.
     */
    public function withFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_data' => 'fake-binary-data', // 測試用假資料
            'file_mime' => 'application/pdf',
            'file_size' => 524288, // 512KB
        ]);
    }
}
```

---

## 資料完整性規則

### 級聯刪除策略

**DI-001: 刪除使用者時的級聯刪除**:
```
User 被刪除
  ↓ CASCADE
Experiences 被刪除
Certifications 被刪除
```

**DI-002: 刪除審核者時的行為**:
```
Approver (User) 被刪除
  ↓ SET NULL
Experiences.approved_by = NULL
Certifications.approved_by = NULL
```

### 交易邊界

**單一資源操作**: 不需要明確交易（Laravel 自動處理）
```php
// 新增經驗 - 單一 INSERT
Experience::create($data);
```

**批次操作**: 使用 DB Transaction
```php
DB::transaction(function () use ($data) {
    foreach ($data['experiences'] as $exp) {
        Experience::create($exp);
    }
});
```

---

## 效能優化

### Eager Loading 策略

**避免 N+1 查詢**:
```php
// ❌ N+1 查詢
$experiences = Experience::where('user_id', $userId)->get();
foreach ($experiences as $exp) {
    echo $exp->user->name; // 每次都查詢 users 表
}

// ✅ Eager Loading
$experiences = Experience::with('user')->where('user_id', $userId)->get();
foreach ($experiences as $exp) {
    echo $exp->user->name; // 只查詢一次 users 表
}
```

**Controller 中的使用**:
```php
// ExperienceController::index
public function index(Request $request)
{
    $experiences = $request->user()
        ->experiences()
        ->with('approver') // Eager load approver
        ->ordered()
        ->get();

    return ExperienceResource::collection($experiences);
}
```

### 索引使用

**已建立的索引**:
- `experiences.user_id` - 加速查詢使用者的經驗
- `certifications.user_id` - 加速查詢使用者的證照
- `certifications.approval_status` - 加速管理員篩選待審核證照

**查詢優化**:
```php
// ✅ 使用索引
Experience::where('user_id', $userId)->get(); // 使用 idx_user_id

// ✅ 使用索引
Certification::where('approval_status', 'pending')->get(); // 使用 idx_approval_status
```

---

## 變更記錄

| 日期 | 版本 | 變更內容 |
|------|------|---------|
| 2026-01-11 | 1.0 | 初始版本，定義 Experience 和 Certification 資料模型 |

---

**文檔狀態**: ✅ Complete
**審核狀態**: Pending Review
