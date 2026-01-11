# 技術規格文檔 - 用戶註冊流程重構

**Feature**: 用戶註冊流程重構
**Version**: 1.0
**Created**: 2026-01-10
**Status**: 規格撰寫中

---

## 目錄

1. [Backend 規格](#backend-規格)
   - [Database Schema](#database-schema)
   - [Models](#models)
   - [Controllers](#controllers)
   - [Policies](#policies)
   - [Middleware](#middleware)
   - [API Endpoints](#api-endpoints)
2. [Frontend 規格](#frontend-規格)
3. [資料遷移規格](#資料遷移規格)
4. [測試規格](#測試規格)

---

## Backend 規格

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

#### 1. User Model

**檔案**: `app/Models/User.php`

**新增常數**:
```php
// Roles
public const ROLE_USER = 'user';
public const ROLE_SALESPERSON = 'salesperson';
public const ROLE_ADMIN = 'admin';

// Salesperson Status
public const STATUS_PENDING = 'pending';
public const STATUS_APPROVED = 'approved';
public const STATUS_REJECTED = 'rejected';

// Default reapply days
public const DEFAULT_REAPPLY_DAYS = 7;
```

**新增 Fillable**:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'salesperson_status',
    'salesperson_applied_at',
    'salesperson_approved_at',
    'rejection_reason',
    'can_reapply_at',
    'is_paid_member',
];
```

**新增 Casts**:
```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'salesperson_applied_at' => 'datetime',
        'salesperson_approved_at' => 'datetime',
        'can_reapply_at' => 'datetime',
        'is_paid_member' => 'boolean',
    ];
}
```

**新增 Relationships**:
```php
/**
 * Get the salesperson profile for this user.
 */
public function salespersonProfile(): HasOne
{
    return $this->hasOne(SalespersonProfile::class);
}
```

**新增 Helper Methods**:
```php
/**
 * Check if user is a general user.
 */
public function isUser(): bool
{
    return $this->role === self::ROLE_USER;
}

/**
 * Check if user is a salesperson (any status).
 */
public function isSalesperson(): bool
{
    return $this->role === self::ROLE_SALESPERSON;
}

/**
 * Check if user is an approved salesperson.
 */
public function isApprovedSalesperson(): bool
{
    return $this->role === self::ROLE_SALESPERSON
        && $this->salesperson_status === self::STATUS_APPROVED;
}

/**
 * Check if user is a pending salesperson.
 */
public function isPendingSalesperson(): bool
{
    return $this->role === self::ROLE_SALESPERSON
        && $this->salesperson_status === self::STATUS_PENDING;
}

/**
 * Check if user is an admin.
 */
public function isAdmin(): bool
{
    return $this->role === self::ROLE_ADMIN;
}

/**
 * Check if user can reapply for salesperson.
 */
public function canReapply(): bool
{
    if ($this->salesperson_status !== self::STATUS_REJECTED) {
        return false;
    }

    if (!$this->can_reapply_at) {
        return true;
    }

    return $this->can_reapply_at->isPast();
}

/**
 * Upgrade user to salesperson.
 */
public function upgradeToSalesperson(array $profileData): void
{
    $this->update([
        'role' => self::ROLE_SALESPERSON,
        'salesperson_status' => self::STATUS_PENDING,
        'salesperson_applied_at' => now(),
        'rejection_reason' => null,
        'can_reapply_at' => null,
    ]);

    $this->salespersonProfile()->updateOrCreate(
        ['user_id' => $this->id],
        $profileData
    );
}

/**
 * Approve salesperson application.
 */
public function approveSalesperson(): void
{
    $this->update([
        'salesperson_status' => self::STATUS_APPROVED,
        'salesperson_approved_at' => now(),
        'rejection_reason' => null,
        'can_reapply_at' => null,
    ]);
}

/**
 * Reject salesperson application.
 */
public function rejectSalesperson(string $reason, int $reapplyDays = self::DEFAULT_REAPPLY_DAYS): void
{
    $this->update([
        'role' => self::ROLE_USER,
        'salesperson_status' => self::STATUS_REJECTED,
        'rejection_reason' => $reason,
        'can_reapply_at' => now()->addDays($reapplyDays),
    ]);
}
```

#### 2. Company Model

**檔案**: `app/Models/Company.php`

**更新 Fillable**:
```php
protected $fillable = [
    'name',
    'tax_id',
    'is_personal',
    'created_by',
];
```

**更新 Casts**:
```php
protected function casts(): array
{
    return [
        'is_personal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

**移除 Relationships**:
```php
// 移除 industry() 關聯
// 移除 approver() 關聯
// 移除 approvalLogs() 關聯
```

**保留 Relationships**:
```php
/**
 * Get the user who created this company.
 */
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}

/**
 * Get the salesperson profiles for this company.
 */
public function salespersonProfiles(): HasMany
{
    return $this->hasMany(SalespersonProfile::class);
}
```

**新增 Scopes**:
```php
/**
 * Scope: registered companies (with tax_id).
 */
public function scopeRegistered(Builder $query): Builder
{
    return $query->whereNotNull('tax_id');
}

/**
 * Scope: personal workshops (without tax_id).
 */
public function scopePersonal(Builder $query): Builder
{
    return $query->whereNull('tax_id')
        ->where('is_personal', true);
}
```

#### 3. SalespersonProfile Model

**檔案**: `app/Models/SalespersonProfile.php`

**更新 Fillable**:
```php
protected $fillable = [
    'user_id',
    'company_id', // nullable
    'full_name',
    'phone',
    'bio',
    'specialties',
    'service_regions',
    'avatar_data',
    'avatar_mime',
    'avatar_size',
    // 移除 approval_status, rejected_reason, approved_by, approved_at
];
```

**移除審核相關關聯**:
```php
// 移除 approver() 關聯
// 移除 approvalLogs() 關聯
```

**新增 Accessor**:
```php
/**
 * Get the approval status from user.
 */
public function getApprovalStatusAttribute(): ?string
{
    return $this->user?->salesperson_status;
}
```

---

### Controllers

#### 1. AuthController

**檔案**: `app/Http/Controllers/Api/AuthController.php`

**新增 Methods**:

```php
/**
 * Register a general user.
 *
 * POST /api/auth/register
 */
public function register(RegisterRequest $request): JsonResponse
{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => User::ROLE_USER,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ], 201);
}

/**
 * Register directly as salesperson.
 *
 * POST /api/auth/register-salesperson
 */
public function registerSalesperson(RegisterSalespersonRequest $request): JsonResponse
{
    DB::beginTransaction();

    try {
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);

        // Create salesperson profile
        $user->salespersonProfile()->create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'specialties' => $request->specialties,
            'service_regions' => $request->service_regions,
        ]);

        DB::commit();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('salespersonProfile'),
            'token' => $token,
            'message' => '註冊成功！您的業務員資料正在審核中，預計 1-3 個工作天完成。',
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

#### 2. SalespersonController (新增)

**檔案**: `app/Http/Controllers/Api/SalespersonController.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpgradeSalespersonRequest;
use App\Http\Requests\UpdateSalespersonProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SalespersonController extends Controller
{
    /**
     * Upgrade current user to salesperson.
     *
     * POST /api/salesperson/upgrade
     */
    public function upgrade(UpgradeSalespersonRequest $request): JsonResponse
    {
        $user = $request->user();

        // Check if already salesperson
        if ($user->isSalesperson()) {
            return response()->json([
                'error' => '您已經是業務員',
            ], 400);
        }

        // Check if rejected and can reapply
        if ($user->salesperson_status === User::STATUS_REJECTED && !$user->canReapply()) {
            return response()->json([
                'error' => '請於 ' . $user->can_reapply_at->format('Y-m-d') . ' 後重新申請',
                'can_reapply_at' => $user->can_reapply_at,
            ], 429);
        }

        DB::beginTransaction();

        try {
            $user->upgradeToSalesperson([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'bio' => $request->bio,
                'specialties' => $request->specialties,
                'service_regions' => $request->service_regions,
            ]);

            DB::commit();

            return response()->json([
                'user' => $user->fresh()->load('salespersonProfile'),
                'message' => '升級成功！您的業務員資料正在審核中，預計 1-3 個工作天完成。',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get salesperson status.
     *
     * GET /api/salesperson/status
     */
    public function status(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isSalesperson()) {
            return response()->json([
                'is_salesperson' => false,
            ]);
        }

        return response()->json([
            'is_salesperson' => true,
            'status' => $user->salesperson_status,
            'applied_at' => $user->salesperson_applied_at,
            'approved_at' => $user->salesperson_approved_at,
            'rejection_reason' => $user->rejection_reason,
            'can_reapply_at' => $user->can_reapply_at,
            'can_reapply' => $user->canReapply(),
        ]);
    }

    /**
     * Update salesperson profile.
     *
     * PUT /api/salesperson/profile
     */
    public function updateProfile(UpdateSalespersonProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSalesperson()) {
            return response()->json([
                'error' => '僅業務員可更新個人資料',
            ], 403);
        }

        $user->salespersonProfile()->update($request->validated());

        return response()->json([
            'profile' => $user->salespersonProfile,
            'message' => '個人資料已更新',
        ]);
    }

    /**
     * Search approved salespeople.
     *
     * GET /api/salespeople
     */
    public function index(): JsonResponse
    {
        $salespeople = User::where('role', User::ROLE_SALESPERSON)
            ->where('salesperson_status', User::STATUS_APPROVED)
            ->with('salespersonProfile.company')
            ->paginate(20);

        return response()->json($salespeople);
    }
}
```

#### 3. AdminController (更新)

**檔案**: `app/Http/Controllers/Api/AdminController.php`

**新增 Methods**:

```php
/**
 * Get pending salesperson applications.
 *
 * GET /api/admin/salesperson-applications
 */
public function salespersonApplications(): JsonResponse
{
    $applications = User::where('role', User::ROLE_SALESPERSON)
        ->where('salesperson_status', User::STATUS_PENDING)
        ->with('salespersonProfile')
        ->orderBy('salesperson_applied_at', 'asc')
        ->paginate(20);

    return response()->json($applications);
}

/**
 * Approve salesperson application.
 *
 * POST /api/admin/salesperson-applications/{id}/approve
 */
public function approveSalesperson(int $id): JsonResponse
{
    $user = User::findOrFail($id);

    if ($user->salesperson_status !== User::STATUS_PENDING) {
        return response()->json([
            'error' => '此申請無法審核',
        ], 400);
    }

    $user->approveSalesperson();

    return response()->json([
        'user' => $user->load('salespersonProfile'),
        'message' => '已批准業務員申請',
    ]);
}

/**
 * Reject salesperson application.
 *
 * POST /api/admin/salesperson-applications/{id}/reject
 */
public function rejectSalesperson(RejectSalespersonRequest $request, int $id): JsonResponse
{
    $user = User::findOrFail($id);

    if ($user->salesperson_status !== User::STATUS_PENDING) {
        return response()->json([
            'error' => '此申請無法審核',
        ], 400);
    }

    $user->rejectSalesperson(
        $request->rejection_reason,
        $request->reapply_days ?? User::DEFAULT_REAPPLY_DAYS
    );

    return response()->json([
        'user' => $user,
        'message' => '已拒絕業務員申請',
    ]);
}
```

**移除 Methods**:
```php
// 移除 approveCompany()
// 移除 rejectCompany()
```

#### 4. CompanyController (更新)

**檔案**: `app/Http/Controllers/Api/CompanyController.php`

**更新 store() Method**:

```php
/**
 * Create a new company.
 *
 * POST /api/companies
 */
public function store(StoreCompanyRequest $request): JsonResponse
{
    // Only approved salespeople can create companies
    if (!$request->user()->isApprovedSalesperson()) {
        return response()->json([
            'error' => '僅審核通過的業務員可建立公司',
        ], 403);
    }

    $company = Company::create([
        'name' => $request->name,
        'tax_id' => $request->tax_id,
        'is_personal' => $request->is_personal ?? false,
        'created_by' => $request->user()->id,
    ]);

    return response()->json([
        'company' => $company,
        'message' => '公司建立成功',
    ], 201);
}

/**
 * Search companies.
 *
 * GET /api/companies/search
 */
public function search(Request $request): JsonResponse
{
    // Search by tax_id
    if ($request->has('tax_id')) {
        $company = Company::where('tax_id', $request->tax_id)->first();

        return response()->json([
            'exists' => !is_null($company),
            'company' => $company,
        ]);
    }

    // Search by name (fuzzy)
    if ($request->has('name')) {
        $companies = Company::where('name', 'like', '%' . $request->name . '%')
            ->limit(10)
            ->get();

        return response()->json($companies);
    }

    return response()->json([
        'error' => '請提供 tax_id 或 name 參數',
    ], 400);
}
```

---

### Policies

#### 1. SalespersonPolicy (新增)

**檔案**: `app/Policies/SalespersonPolicy.php`

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class SalespersonPolicy
{
    /**
     * Determine if user can view salesperson dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->isSalesperson();
    }

    /**
     * Determine if user can create companies.
     */
    public function createCompany(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }

    /**
     * Determine if user can create ratings.
     */
    public function createRating(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }

    /**
     * Determine if user can be searched.
     */
    public function canBeSearched(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }
}
```

#### 2. CompanyPolicy (更新)

**檔案**: `app/Policies/CompanyPolicy.php`

**更新 create() Method**:
```php
public function create(User $user): bool
{
    return $user->isApprovedSalesperson();
}
```

**移除審核相關 Methods**:
```php
// 移除 approve()
// 移除 reject()
```

---

### Middleware

#### 1. EnsureApprovedSalesperson (新增)

**檔案**: `app/Http/Middleware/EnsureApprovedSalesperson.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedSalesperson
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isApprovedSalesperson()) {
            return response()->json([
                'error' => '需要審核通過的業務員身份',
            ], 403);
        }

        return $next($request);
    }
}
```

#### 2. EnsureSalesperson (新增)

**檔案**: `app/Http/Middleware/EnsureSalesperson.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSalesperson
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isSalesperson()) {
            return response()->json([
                'error' => '需要業務員身份',
            ], 403);
        }

        return $next($request);
    }
}
```

#### 3. EnsureAdmin (新增)

**檔案**: `app/Http/Middleware/EnsureAdmin.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json([
                'error' => '需要管理員身份',
            ], 403);
        }

        return $next($request);
    }
}
```

---

### API Endpoints

#### Authentication Endpoints

```
POST /api/auth/register
Request:
{
    "name": "string",
    "email": "string|email|unique",
    "password": "string|min:8"
}
Response 201:
{
    "user": User,
    "token": "string"
}

POST /api/auth/register-salesperson
Request:
{
    "name": "string",
    "email": "string|email|unique",
    "password": "string|min:8",
    "full_name": "string",
    "phone": "string",
    "bio": "string|nullable",
    "specialties": "string|nullable",
    "service_regions": "array|nullable"
}
Response 201:
{
    "user": User (with salespersonProfile),
    "token": "string",
    "message": "註冊成功！..."
}
```

#### Salesperson Endpoints

```
POST /api/salesperson/upgrade
Middleware: auth:sanctum
Request:
{
    "full_name": "string",
    "phone": "string",
    "bio": "string|nullable",
    "specialties": "string|nullable",
    "service_regions": "array|nullable"
}
Response 200:
{
    "user": User (with salespersonProfile),
    "message": "升級成功！..."
}
Response 429 (Too Early):
{
    "error": "請於 YYYY-MM-DD 後重新申請",
    "can_reapply_at": "datetime"
}

GET /api/salesperson/status
Middleware: auth:sanctum
Response 200:
{
    "is_salesperson": boolean,
    "status": "pending|approved|rejected|null",
    "applied_at": "datetime|null",
    "approved_at": "datetime|null",
    "rejection_reason": "string|null",
    "can_reapply_at": "datetime|null",
    "can_reapply": boolean
}

PUT /api/salesperson/profile
Middleware: auth:sanctum, salesperson
Request:
{
    "company_id": "integer|nullable|exists:companies,id",
    "full_name": "string",
    "phone": "string",
    "bio": "string|nullable",
    "specialties": "string|nullable",
    "service_regions": "array|nullable"
}
Response 200:
{
    "profile": SalespersonProfile,
    "message": "個人資料已更新"
}

GET /api/salespeople
Response 200:
{
    "data": [User (with salespersonProfile)],
    "links": {...},
    "meta": {...}
}
```

#### Admin Endpoints

```
GET /api/admin/salesperson-applications
Middleware: auth:sanctum, admin
Response 200:
{
    "data": [User (with salespersonProfile)],
    "links": {...},
    "meta": {...}
}

POST /api/admin/salesperson-applications/{id}/approve
Middleware: auth:sanctum, admin
Response 200:
{
    "user": User (with salespersonProfile),
    "message": "已批准業務員申請"
}

POST /api/admin/salesperson-applications/{id}/reject
Middleware: auth:sanctum, admin
Request:
{
    "rejection_reason": "string|required",
    "reapply_days": "integer|min:0|max:90|nullable"
}
Response 200:
{
    "user": User,
    "message": "已拒絕業務員申請"
}
```

#### Company Endpoints

```
GET /api/companies/search
Query Parameters:
- tax_id: string (精確搜尋)
- name: string (模糊搜尋)

Response 200 (tax_id search):
{
    "exists": boolean,
    "company": Company|null
}

Response 200 (name search):
[
    {
        "id": integer,
        "name": "string",
        "tax_id": "string|null",
        "is_personal": boolean
    }
]

POST /api/companies
Middleware: auth:sanctum, approved_salesperson
Request:
{
    "name": "string|required|max:200",
    "tax_id": "string|nullable|max:50|unique:companies",
    "is_personal": "boolean"
}
Validation Rules:
- If is_personal=false, tax_id is required
Response 201:
{
    "company": Company,
    "message": "公司建立成功"
}
Response 422 (tax_id duplicate):
{
    "errors": {
        "tax_id": ["統一編號已被使用"]
    }
}
```

---

## Frontend 規格

### 頁面結構

#### 1. 註冊頁面

**路徑**: `/register`

**元件**: `app/(auth)/register/page.tsx`

**UI 流程**:
```
Step 1: 選擇註冊方式
  □ 一般使用者
  □ 業務員

Step 2A: 一般使用者註冊
  - Email
  - 密碼
  - 確認密碼

Step 2B: 業務員註冊
  - Email
  - 密碼
  - 確認密碼
  - 姓名
  - 聯絡電話
  - 專長領域（可選）
  - 自我介紹（可選）
  - 服務區域（可選）
```

**API 呼叫**:
- `POST /api/auth/register` (一般使用者)
- `POST /api/auth/register-salesperson` (業務員)

#### 2. 升級為業務員頁面

**路徑**: `/salesperson/upgrade`

**元件**: `app/(dashboard)/salesperson/upgrade/page.tsx`

**權限**: 需登入 + 非業務員

**UI**:
```
升級為業務員

填寫業務員資料：
- 姓名 *
- 聯絡電話 *
- 專長領域
- 自我介紹
- 服務區域

[提交申請]
```

**API 呼叫**:
- `POST /api/salesperson/upgrade`

#### 3. 業務員審核狀態顯示

**元件**: `components/SalespersonStatusBadge.tsx`

**顯示邏輯**:
```typescript
if (status === 'pending') {
  return (
    <div className="status-pending">
      🟡 審核中（預計 1-3 個工作天）

      ✅ 目前您可以：
        - 瀏覽所有公司和評分
        - 使用業務員儀表板
        - 使用數據分析工具
        - 編輯個人資料

      ⏳ 審核通過後即可：
        - 建立和管理公司
        - 發布評分和評論
        - 出現在業務員搜尋列表
    </div>
  );
}

if (status === 'rejected') {
  return (
    <div className="status-rejected">
      ❌ 業務員申請未通過

      拒絕原因：{rejection_reason}

      您可以：
      {can_reapply ? (
        <button>修改資料並重新申請</button>
      ) : (
        <p>請於 {can_reapply_at} 後重新申請</p>
      )}
    </div>
  );
}

if (status === 'approved') {
  return (
    <div className="status-approved">
      ✅ 已審核通過
    </div>
  );
}
```

#### 4. 建立公司頁面（簡化版）

**路徑**: `/companies/create`

**元件**: `app/(dashboard)/companies/create/page.tsx`

**權限**: 需登入 + 審核通過的業務員

**UI 流程**:
```
Step 1: 選擇公司類型
  □ 註冊公司（有統一編號）
  □ 個人工作室（無統一編號）

Step 2A: 註冊公司
  統一編號: [________] [檢查]

  → 如果已存在:
    ⚠️  三商美邦人壽股份有限公司（統編：12345678）已存在
    [加入此公司] [重新輸入]

  → 如果不存在:
    公司名稱: [________]
    [建立公司]

Step 2B: 個人工作室
  工作室名稱: [________]
  [建立工作室]
```

**API 呼叫**:
- `GET /api/companies/search?tax_id={tax_id}` (檢查統編)
- `POST /api/companies` (建立公司)
- `PUT /api/salesperson/profile` (加入既有公司)

#### 5. 管理員審核介面

**路徑**: `/admin/salesperson-applications`

**元件**: `app/(admin)/salesperson-applications/page.tsx`

**權限**: 需登入 + 管理員

**UI**:
```
待審核業務員列表

| 姓名 | Email | 電話 | 申請時間 | 操作 |
|------|-------|------|---------|------|
| 張三 | zhang@example.com | 0912-345-678 | 2026-01-09 | [查看] [批准] [拒絕] |

點擊 [拒絕]:
  拒絕原因: [____________]
  等待天數: [7] 天後可重新申請
  [確認拒絕]
```

**API 呼叫**:
- `GET /api/admin/salesperson-applications`
- `POST /api/admin/salesperson-applications/{id}/approve`
- `POST /api/admin/salesperson-applications/{id}/reject`

---

## 資料遷移規格

### 遷移腳本

**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_migrate_existing_data.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 將所有現有業務員設為已審核
        DB::table('users')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('salesperson_profiles')
                    ->whereColumn('salesperson_profiles.user_id', 'users.id');
            })
            ->update([
                'role' => User::ROLE_SALESPERSON,
                'salesperson_status' => User::STATUS_APPROVED,
                'salesperson_approved_at' => now(),
            ]);

        // 2. 將沒有 salesperson_profile 的使用者設為一般使用者
        DB::table('users')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('salesperson_profiles')
                    ->whereColumn('salesperson_profiles.user_id', 'users.id');
            })
            ->whereNull('role')
            ->update([
                'role' => User::ROLE_USER,
            ]);

        // 3. 將所有現有公司設為非個人工作室
        DB::table('companies')
            ->whereNull('is_personal')
            ->update([
                'is_personal' => false,
            ]);
    }

    public function down(): void
    {
        // Rollback: 清除遷移的資料
        DB::table('users')->update([
            'role' => null,
            'salesperson_status' => null,
            'salesperson_approved_at' => null,
        ]);
    }
};
```

---

## 測試規格

### 單元測試

#### 1. UserModelTest

**檔案**: `tests/Unit/Models/UserTest.php`

**測試案例**:
```php
test('user can upgrade to salesperson')
test('approved salesperson can create company')
test('pending salesperson cannot create company')
test('rejected user can reapply after waiting period')
test('rejected user cannot reapply before waiting period')
test('user helper methods work correctly')
```

#### 2. CompanyModelTest

**檔案**: `tests/Unit/Models/CompanyTest.php`

**測試案例**:
```php
test('company tax_id must be unique')
test('company can be personal workshop')
test('multiple personal workshops allowed')
test('registered companies scope works')
test('personal workshops scope works')
```

### 整合測試

#### 1. AuthControllerTest

**檔案**: `tests/Feature/Controllers/AuthControllerTest.php`

**測試案例**:
```php
test('user can register as general user')
test('user can register as salesperson')
test('salesperson registration creates profile')
test('salesperson registration sets status to pending')
```

#### 2. SalespersonControllerTest

**檔案**: `tests/Feature/Controllers/SalespersonControllerTest.php`

**測試案例**:
```php
test('user can upgrade to salesperson')
test('salesperson cannot upgrade again')
test('rejected user must wait before reapplying')
test('approved salesperson can update profile')
test('pending salesperson can update profile')
test('only approved salespeople appear in search')
```

#### 3. CompanyControllerTest

**檔案**: `tests/Feature/Controllers/CompanyControllerTest.php`

**測試案例**:
```php
test('approved salesperson can create company')
test('pending salesperson cannot create company')
test('general user cannot create company')
test('company tax_id must be unique')
test('can create personal workshop without tax_id')
test('can search company by tax_id')
test('can search company by name')
```

#### 4. AdminControllerTest

**檔案**: `tests/Feature/Controllers/AdminControllerTest.php`

**測試案例**:
```php
test('admin can view pending applications')
test('admin can approve salesperson')
test('admin can reject salesperson with reason')
test('rejection sets reapply waiting period')
test('non-admin cannot access admin endpoints')
```

---

**規格文檔版本**: 1.0
**完成時間**: 2026-01-10
**下一步**: 建立任務清單 (tasks.md)
