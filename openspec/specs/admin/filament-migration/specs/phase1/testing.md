# Filament Phase 1: 測試規格

**專案**: YAMU Backend - Filament Admin Panel
**Phase**: 1 - 審核功能測試
**測試覆蓋目標**: >= 80%

---

## 🎯 Testing Strategy

### Test Pyramid

```
             ┌─────────────────┐
             │  Manual Tests   │  10%
             │  (UI Testing)   │
         ┌───┴─────────────────┴───┐
         │   Feature Tests (Pest)  │  60%
         │   (Filament Resources)  │
     ┌───┴─────────────────────────┴───┐
     │      Unit Tests (Pest)          │  30%
     │   (Models, Services)            │
     └─────────────────────────────────┘
```

### Testing Tools

- **Pest 3.x**: Feature Tests & Unit Tests
- **Livewire Testing**: Filament Resource Tests
- **Browser Testing**: Manual (Phase 1), Playwright (Phase 2+)
- **PHPStan**: Static Analysis (Level 9)

---

## 🧪 Test Cases

### 1. Installation Tests

**檔案**: `tests/Feature/Filament/InstallationTest.php`

```php
<?php

namespace Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function filament_admin_panel_is_accessible(): void
    {
        $response = $this->get('/filament/admin');

        // 未登入應重導向到登入頁面
        $response->assertRedirect('/filament/admin/login');
    }

    /** @test */
    public function filament_login_page_is_accessible(): void
    {
        $response = $this->get('/filament/admin/login');

        $response->assertOk()
            ->assertSee('YAMU Admin')  // 檢查 Brand Name
            ->assertSee('Email')
            ->assertSee('Password');
    }

    /** @test */
    public function spatie_permission_tables_exist(): void
    {
        $this->assertDatabaseTableExists('roles');
        $this->assertDatabaseTableExists('permissions');
        $this->assertDatabaseTableExists('model_has_roles');
        $this->assertDatabaseTableExists('model_has_permissions');
        $this->assertDatabaseTableExists('role_has_permissions');
    }

    /** @test */
    public function default_roles_are_created(): void
    {
        $this->seed(\Database\Seeders\FilamentAdminSeeder::class);

        $this->assertDatabaseHas('roles', [
            'name' => 'super_admin',
            'guard_name' => 'admin_session',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'guard_name' => 'admin_session',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'reviewer',
            'guard_name' => 'admin_session',
        ]);
    }

    /** @test */
    public function salesperson_application_permissions_are_created(): void
    {
        $this->seed(\Database\Seeders\FilamentAdminSeeder::class);

        $permissions = [
            'view_salesperson::application',
            'view_any_salesperson::application',
            'approve_salesperson::application',
            'reject_salesperson::application',
            'bulk_approve_salesperson::application',
        ];

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'admin_session',
            ]);
        }
    }
}
```

---

### 2. Authentication Tests

**檔案**: `tests/Feature/Filament/AuthenticationTest.php`

```php
<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 建立 roles
        Role::create(['name' => 'super_admin', 'guard_name' => 'admin_session']);
    }

    /** @test */
    public function admin_can_login_to_filament(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password_hash' => bcrypt('password'),
        ]);
        $admin->assignRole('super_admin');

        $response = $this->post('/filament/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/filament/admin');
        $this->assertAuthenticatedAs($admin, 'admin_session');
    }

    /** @test */
    public function non_admin_cannot_login_to_filament(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'password_hash' => bcrypt('password'),
        ]);

        $response = $this->post('/filament/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // 登入失敗 (non-admin 無法訪問)
        $response->assertRedirect();
        $this->assertGuest('admin_session');
    }

    /** @test */
    public function inactive_admin_cannot_login(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'inactive',
            'password_hash' => bcrypt('password'),
        ]);

        $response = $this->post('/filament/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertGuest('admin_session');
    }

    /** @test */
    public function admin_can_logout(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin_session');

        $response = $this->post('/filament/admin/logout');

        $response->assertRedirect('/filament/admin/login');
        $this->assertGuest('admin_session');
    }

    /** @test */
    public function api_jwt_authentication_still_works(): void
    {
        $user = User::factory()->create([
            'password_hash' => bcrypt('password123'),
        ]);

        // JWT 登入應該仍正常運作
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'access_token',
                    'refresh_token',
                ],
            ]);
    }

    /** @test */
    public function session_and_jwt_guards_are_isolated(): void
    {
        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $adminUser->assignRole('super_admin');

        // Admin 登入 Filament (Session)
        $this->actingAs($adminUser, 'admin_session');
        $this->assertAuthenticatedAs($adminUser, 'admin_session');

        // 但 API guard 應該是未認證
        $this->assertGuest('api');
    }
}
```

---

### 3. Salesperson Application Resource Tests

**檔案**: `tests/Feature/Filament/SalespersonApplicationResourceTest.php`

```php
<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SalespersonApplicationResource;
use App\Filament\Resources\SalespersonApplicationResource\Pages\ListSalespersonApplications;
use App\Filament\Resources\SalespersonApplicationResource\Pages\ViewSalespersonApplication;
use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalespersonApplicationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 建立 super_admin role
        $role = Role::create(['name' => 'super_admin', 'guard_name' => 'admin_session']);

        // 建立 admin user
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->admin->assignRole($role);
    }

    /** @test */
    public function admin_can_view_salesperson_applications_list(): void
    {
        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertSuccessful();
    }

    /** @test */
    public function list_shows_only_pending_applications(): void
    {
        // 建立待審核申請
        $pending = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $pending->id]);

        // 建立已批准申請
        $approved = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
            'salesperson_applied_at' => now(),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $approved->id]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$approved]);
    }

    /** @test */
    public function table_columns_are_displayed(): void
    {
        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        $profile = SalespersonProfile::factory()->create([
            'user_id' => $applicant->id,
            'full_name' => 'Test Salesperson',
            'phone' => '0912345678',
        ]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertCanSeeTableRecords([$applicant])
            ->assertSee('Test Salesperson')
            ->assertSee('0912345678')
            ->assertSee($applicant->email);
    }

    /** @test */
    public function admin_can_search_by_name(): void
    {
        $john = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        SalespersonProfile::factory()->create([
            'user_id' => $john->id,
            'full_name' => 'John Doe',
        ]);

        $jane = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        SalespersonProfile::factory()->create([
            'user_id' => $jane->id,
            'full_name' => 'Jane Smith',
        ]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$john])
            ->assertCanNotSeeTableRecords([$jane]);
    }

    /** @test */
    public function admin_can_approve_application(): void
    {
        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $applicant->id]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->callTableAction('approve', $applicant);

        $applicant->refresh();

        expect($applicant->salesperson_status)->toBe(User::STATUS_APPROVED);
        expect($applicant->salesperson_approved_at)->not->toBeNull();
        expect($applicant->rejection_reason)->toBeNull();
        expect($applicant->can_reapply_at)->toBeNull();
    }

    /** @test */
    public function admin_can_reject_application(): void
    {
        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $applicant->id]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->callTableAction('reject', $applicant, data: [
                'rejection_reason' => '資料不完整',
                'reapply_days' => 30,
            ]);

        $applicant->refresh();

        expect($applicant->salesperson_status)->toBe(User::STATUS_REJECTED);
        expect($applicant->rejection_reason)->toBe('資料不完整');
        expect($applicant->can_reapply_at)->not->toBeNull();
        expect($applicant->role)->toBe(User::ROLE_USER);  // 降回 user 角色
    }

    /** @test */
    public function admin_can_bulk_approve_applications(): void
    {
        $applicants = User::factory()->count(3)->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);

        foreach ($applicants as $applicant) {
            SalespersonProfile::factory()->create(['user_id' => $applicant->id]);
        }

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->callTableBulkAction('approve_all', $applicants);

        foreach ($applicants as $applicant) {
            $applicant->refresh();
            expect($applicant->salesperson_status)->toBe(User::STATUS_APPROVED);
        }
    }

    /** @test */
    public function admin_can_view_application_details(): void
    {
        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);
        $profile = SalespersonProfile::factory()->create([
            'user_id' => $applicant->id,
            'full_name' => 'Test Salesperson',
            'bio' => 'This is my bio',
        ]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ViewSalespersonApplication::class, ['record' => $applicant->id])
            ->assertSuccessful()
            ->assertSee('Test Salesperson')
            ->assertSee('This is my bio')
            ->assertSee($applicant->email);
    }

    /** @test */
    public function applications_are_ordered_by_applied_date_fifo(): void
    {
        // 建立三個申請，不同時間
        $oldest = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now()->subDays(3),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $oldest->id]);

        $middle = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now()->subDays(2),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $middle->id]);

        $newest = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now()->subDay(),
        ]);
        SalespersonProfile::factory()->create(['user_id' => $newest->id]);

        $this->actingAs($this->admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertCanSeeTableRecords([$oldest, $middle, $newest], inOrder: true);
    }

    /** @test */
    public function non_admin_cannot_access_resource(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertForbidden();
    }
}
```

---

### 4. Permission Tests

**檔案**: `tests/Feature/Filament/PermissionTest.php`

```php
<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\FilamentAdminSeeder::class);
    }

    /** @test */
    public function super_admin_has_all_permissions(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $superAdmin->assignRole('super_admin');

        $allPermissions = Permission::where('guard_name', 'admin_session')->pluck('name');

        foreach ($allPermissions as $permission) {
            expect($superAdmin->can($permission))->toBeTrue();
        }
    }

    /** @test */
    public function admin_has_limited_permissions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('admin');

        // Admin 有審核權限
        expect($admin->can('approve_salesperson::application'))->toBeTrue();
        expect($admin->can('reject_salesperson::application'))->toBeTrue();

        // Admin 沒有刪除權限
        expect($admin->can('delete_salesperson::application'))->toBeFalse();
    }

    /** @test */
    public function reviewer_can_only_view_and_approve(): void
    {
        $reviewer = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $reviewer->assignRole('reviewer');

        // Reviewer 可以查看和審核
        expect($reviewer->can('view_salesperson::application'))->toBeTrue();
        expect($reviewer->can('approve_salesperson::application'))->toBeTrue();
        expect($reviewer->can('reject_salesperson::application'))->toBeTrue();

        // Reviewer 不能刪除或批量操作
        expect($reviewer->can('delete_salesperson::application'))->toBeFalse();
        expect($reviewer->can('bulk_approve_salesperson::application'))->toBeFalse();
    }

    /** @test */
    public function user_model_can_access_panel_method_works(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);
        $admin->assignRole('super_admin');

        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $salesperson = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        $panel = filament()->getCurrentPanel();

        expect($admin->canAccessPanel($panel))->toBeTrue();
        expect($user->canAccessPanel($panel))->toBeFalse();
        expect($salesperson->canAccessPanel($panel))->toBeFalse();
    }

    /** @test */
    public function inactive_admin_cannot_access_panel(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'inactive',
        ]);
        $admin->assignRole('super_admin');

        $panel = filament()->getCurrentPanel();

        expect($admin->canAccessPanel($panel))->toBeFalse();
    }
}
```

---

### 5. Performance Tests

**檔案**: `tests/Feature/Filament/PerformanceTest.php`

```php
<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SalespersonApplicationResource\Pages\ListSalespersonApplications;
use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'super_admin', 'guard_name' => 'admin_session']);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->admin->assignRole($role);
    }

    /** @test */
    public function list_page_has_no_n_plus_1_queries(): void
    {
        // 建立 20 個申請
        $applicants = User::factory()->count(20)->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);

        foreach ($applicants as $applicant) {
            SalespersonProfile::factory()->create(['user_id' => $applicant->id]);
        }

        $this->actingAs($this->admin, 'admin_session');

        // 啟用 Query Log
        DB::enableQueryLog();

        Livewire::test(ListSalespersonApplications::class);

        $queries = DB::getQueryLog();

        // 預期查詢數應 <= 15 個 (有 Eager Loading)
        expect(count($queries))->toBeLessThanOrEqual(15);

        // 檢查是否有使用 Eager Loading
        $hasEagerLoading = collect($queries)->some(function ($query) {
            return str_contains($query['query'], 'salesperson_profiles');
        });

        expect($hasEagerLoading)->toBeTrue();
    }

    /** @test */
    public function navigation_badge_query_is_efficient(): void
    {
        // 建立 100 個待審核申請
        User::factory()->count(100)->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
            'salesperson_applied_at' => now(),
        ]);

        DB::enableQueryLog();

        $count = \App\Filament\Resources\SalespersonApplicationResource::getNavigationBadge();

        $queries = DB::getQueryLog();

        // 應該只有一個 COUNT query
        expect(count($queries))->toBe(1);
        expect($queries[0]['query'])->toContain('count');
    }

    /** @test */
    public function permission_check_uses_cache(): void
    {
        $this->actingAs($this->admin, 'admin_session');

        // 第一次檢查 (會建立快取)
        $this->admin->can('approve_salesperson::application');

        DB::enableQueryLog();

        // 第二次檢查 (應使用快取)
        $canApprove = $this->admin->can('approve_salesperson::application');

        $queries = DB::getQueryLog();

        // 應該沒有額外的資料庫查詢 (使用快取)
        expect(count($queries))->toBe(0);
        expect($canApprove)->toBeTrue();
    }
}
```

---

## 🎭 Manual Testing Checklist

### UI Testing (手動測試)

**登入流程**:
- [ ] 訪問 `/filament/admin` 自動重導向到登入頁面
- [ ] 登入頁面顯示品牌 Logo/名稱
- [ ] 使用正確帳號密碼可登入
- [ ] 使用錯誤密碼顯示錯誤訊息
- [ ] 登入後重導向到 Dashboard

**列表頁面**:
- [ ] 待審核申請正確顯示
- [ ] 姓名、Email、電話正確顯示
- [ ] 搜尋功能正常運作
- [ ] 篩選器正常運作
- [ ] 排序功能正常運作
- [ ] Navigation Badge 顯示正確數量

**審核操作**:
- [ ] 批准 Action 顯示確認對話框
- [ ] 批准成功後顯示通知
- [ ] 批准後申請從列表消失
- [ ] 拒絕 Action 需要填寫原因
- [ ] 拒絕成功後顯示通知
- [ ] 批量批准功能正常

**詳情頁面**:
- [ ] 所有資訊正確顯示
- [ ] 基本資料、業務員資訊、聯絡方式、審核狀態正確顯示
- [ ] Header Actions 正常運作

**效能**:
- [ ] 列表頁面載入時間 < 2s
- [ ] 操作回應時間 < 300ms
- [ ] 無明顯卡頓

**響應式**:
- [ ] Desktop (1920x1080) 正常顯示
- [ ] Tablet (768x1024) 正常顯示
- [ ] Mobile (375x667) 正常顯示 (Sidebar 可收合)

---

## 🐛 Common Test Failures

### Issue 1: "Target class [FilamentUser] does not exist"

**原因**: User Model 未實作 FilamentUser 介面

**解決**:
```php
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
```

---

### Issue 2: "No permission found with name approve_salesperson::application"

**原因**: 未執行 Seeder

**解決**:
```bash
php artisan db:seed --class=FilamentAdminSeeder
```

---

### Issue 3: N+1 Query 警告

**原因**: 未使用 Eager Loading

**解決**:
```php
->with(['salespersonProfile', 'salespersonProfile.company'])
```

---

## 📊 Test Coverage Goals

### Target Coverage (Phase 1)

| 類型 | 目標覆蓋率 | 測量方式 |
|------|-----------|----------|
| Feature Tests | >= 80% | PHPUnit --coverage-text |
| Unit Tests | >= 90% | PHPUnit --coverage-text |
| Overall | >= 80% | PHPUnit --coverage-text |

### Run Coverage Report

```bash
# 執行測試並生成覆蓋率報告
docker exec -it my_profile_laravel_app php artisan test --coverage

# 或使用 Composer script
docker exec -it my_profile_laravel_app composer test:coverage
```

**Expected Output**:
```
  Tests:    25 passed (40 assertions)
  Duration: 3.21s

  Code Coverage ...................... 82.5%
    app/Filament/Resources ........... 85.0%
    app/Models/User.php .............. 95.0%
```

---

## ✅ Acceptance Criteria

### Installation
- [ ] All installation tests pass
- [ ] Spatie Permission tables exist
- [ ] Default roles created
- [ ] Default permissions created

### Authentication
- [ ] Admin can login to Filament
- [ ] Non-admin cannot login
- [ ] Inactive admin cannot login
- [ ] API JWT auth still works
- [ ] Guards are isolated

### Resource Tests
- [ ] List page shows only pending applications
- [ ] Table columns displayed correctly
- [ ] Search functionality works
- [ ] Approve action works
- [ ] Reject action works
- [ ] Bulk approve works
- [ ] View page displays all information

### Permissions
- [ ] super_admin has all permissions
- [ ] admin has limited permissions
- [ ] reviewer can only view and approve
- [ ] canAccessPanel() works correctly

### Performance
- [ ] No N+1 queries
- [ ] Query count <= 15 per page
- [ ] Permission checks use cache
- [ ] Navigation badge is efficient

### Coverage
- [ ] Overall coverage >= 80%
- [ ] Feature test coverage >= 80%
- [ ] Critical paths covered (approve, reject)

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
