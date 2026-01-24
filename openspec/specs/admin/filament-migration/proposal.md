# Proposal: 將管理員介面遷移至 Laravel Filament

**提案日期**: 2026-01-24
**提案類型**: Architecture Migration
**優先級**: High
**預估工時**: 4.5 小時

---

## 📋 Executive Summary

### Why - 為什麼需要遷移

**現況問題**:
1. **技術棧分散**: 管理員介面目前在 Next.js 專案中，需要同時維護前後端兩個專案
2. **維護成本高**:
   - Frontend (Next.js): React 組件 + API 整合 + 狀態管理
   - Backend (Laravel): API 端點 + 業務邏輯
   - 任何變更需要同步修改兩邊
3. **開發效率低**: 新增管理功能需要寫兩次（React UI + Laravel API）
4. **部署複雜度**: 需要部署兩個獨立的應用程式

**遷移到 Filament 的優勢**:
1. **單一技術棧**: 完全使用 Laravel 生態系統
2. **開發效率**: Filament 自動生成 CRUD 介面，無需手寫 UI
3. **維護簡化**: 只需維護一個專案（Laravel）
4. **原生整合**: 與 Laravel Models、Policies、Eloquent 完美整合
5. **豐富功能**: 內建 Dashboard、Widgets、Charts、Export、Import 等功能

**成本效益分析**:
- 遷移工時: 4.5 小時（一次性投資）
- 未來開發效率: 提升 60%+（新增功能不需要寫 React）
- 維護成本: 降低 50%+（單一程式碼庫）

---

### What - 遷移範圍

**完整遷移清單** (18 個 Admin 端點 → 8 個 Filament Resources):

| 類別 | 現有 API 端點 | Filament Resource | 說明 |
|------|--------------|-------------------|------|
| **統計儀表板** | `GET /admin/statistics` | Dashboard Widgets | 統計數據卡片 |
| | `GET /admin/analytics/overview` | Dashboard Widgets | 總覽圖表 |
| | `GET /admin/analytics/top-salespersons` | Dashboard Widget | 排行榜 |
| | `GET /admin/analytics/activity` | Dashboard Widget | 活動記錄 |
| | `GET /admin/analytics/growth` | Dashboard Widget | 成長趨勢 |
| **待審核總覽** | `GET /admin/pending-approvals` | Dashboard Widget | 待審核摘要 |
| **業務員申請** | `GET /admin/salesperson-applications` | SalespersonApplicationResource | 列表、篩選、搜尋 |
| | `POST /admin/salesperson-applications/{id}/approve` | Bulk Action | 批准操作 |
| | `POST /admin/salesperson-applications/{id}/reject` | Action (with form) | 拒絕操作（含原因） |
| **公司審核** | `POST /admin/approve-company/{id}` | CompanyResource | 審核 Action |
| **工作經驗審核** | `POST /admin/approve-experience/{id}` | ExperienceResource | 批准 Action |
| | `POST /admin/reject-experience/{id}` | Action (with form) | 拒絕 Action |
| **證照審核** | `POST /admin/approve-certification/{id}` | CertificationResource | 批准 Action |
| | `POST /admin/reject-certification/{id}` | Action (with form) | 拒絕 Action |
| **使用者管理** | `GET /admin/users` | UserResource | 列表、篩選、搜尋 |
| | `PUT /admin/users/{id}/status` | Action | 啟用/停用 |
| | `DELETE /admin/users/{id}` | Delete Action | 刪除使用者 |
| **設定管理** | `GET /admin/settings/regions` | RegionResource | 地區管理 |
| | `GET /admin/settings/industries` | IndustryResource | 產業管理 |

**Filament Resources 對應**:
1. **Dashboard** - 統計儀表板（Widgets: Stats Cards, Charts, Recent Activity）
2. **SalespersonApplicationResource** - 業務員申請審核
3. **CompanyResource** - 公司管理與審核
4. **ExperienceResource** - 工作經驗審核
5. **CertificationResource** - 證照審核
6. **UserResource** - 使用者管理
7. **RegionResource** - 地區設定
8. **IndustryResource** - 產業設定

---

### How - 遷移策略

**遷移方式**: 直接切換（Big Bang Migration）

**理由**:
- 管理員介面使用者少（2-5 位）
- 功能相對獨立，不影響業務員端
- 遷移工時短（4.5 小時），可在一個工作日內完成
- 避免維護兩套系統的複雜度

**遷移步驟**:
```
Phase 1: 環境準備 (30 分鐘)
├─ 安裝 Filament v3 packages
├─ 設定認證 (Session Auth for Filament)
├─ 建立 Admin User Model (或複用現有 User)
└─ 設定 Theme 和基礎配置

Phase 2: Filament Resources 開發 (2 小時)
├─ Dashboard + Widgets (統計、圖表) - 30 min
├─ SalespersonApplicationResource - 20 min
├─ CompanyResource - 15 min
├─ ExperienceResource - 15 min
├─ CertificationResource - 15 min
├─ UserResource - 20 min
└─ Settings Resources (Region, Industry) - 10 min

Phase 3: Theme 客製化 (30 分鐘)
├─ 調整品牌色彩
├─ 設定 Logo
├─ 客製化導航選單
└─ 調整 Widgets 排版

Phase 4: 測試 (1 小時)
├─ 功能測試 (所有 CRUD 操作)
├─ 權限測試 (Admin 權限驗證)
├─ UI 測試 (響應式、互動)
└─ 效能測試 (頁面載入時間)

Phase 5: 部署與清理 (30 分鐘)
├─ 部署 Filament Admin
├─ 驗證線上環境
├─ 移除 Next.js Admin Pages
├─ 移除 Admin API 端點 (可選)
└─ 更新文檔
```

---

### Success Criteria - 成功標準

**功能完整性**:
- ✅ 100% 功能遷移（18 個端點 → 8 個 Resources）
- ✅ 所有審核流程正常運作
- ✅ 使用者管理功能完整
- ✅ 統計儀表板數據正確

**效能指標** (參考 metrics-standards.md):
- ✅ Filament 頁面 LCP < 1s
- ✅ 列表查詢 P95 < 500ms
- ✅ 操作回應 P95 < 300ms
- ✅ Dashboard 載入時間 < 2s

**使用者體驗**:
- ✅ 管理員可完全停用 Next.js 專案
- ✅ 所有操作有即時回饋（Toast Notification）
- ✅ 批量操作功能正常（Bulk Actions）
- ✅ 搜尋、篩選、排序功能完整

**程式碼品質**:
- ✅ 0 個 Next.js Admin dependencies
- ✅ PHPStan Level 9 無錯誤
- ✅ 測試覆蓋率 >= 80%
- ✅ 符合 Filament 最佳實踐

---

## 🎯 Scope Definition

### In Scope - 本次實作範圍

**Backend 開發**:
- ✅ 安裝並配置 Filament v3
- ✅ 建立 8 個 Filament Resources
- ✅ 配置認證系統（Session Auth for Admin）
- ✅ 權限整合（Spatie Permissions + Filament Shield）
- ✅ Dashboard Widgets 開發（統計卡片、圖表）
- ✅ Custom Actions（審核、拒絕、批量操作）
- ✅ Theme 客製化（顏色、Logo）

**Frontend 移除**:
- ✅ 移除 Next.js Admin Pages
- ✅ 移除 Admin 相關 React 組件
- ✅ 移除 Admin API 整合程式碼

**測試**:
- ✅ Filament Resources 功能測試
- ✅ 權限測試
- ✅ UI 互動測試

**文檔**:
- ✅ 更新 CLAUDE.md（新增 Filament 說明）
- ✅ 建立 Filament 使用手冊
- ✅ 更新部署文檔

---

### Out of Scope - 不在範圍內

**暫不實作**:
- ❌ 雙因素驗證 (2FA) - 未來規劃
- ❌ IP 白名單限制 - 安全性需求暫時不高
- ❌ Audit Log UI（操作記錄查看） - 使用 Filament Logger plugin 即可
- ❌ Email 通知業務員（審核結果） - 未來規劃
- ❌ 複雜的 Dashboard 自訂功能 - 使用固定版面
- ❌ 即時數據更新（WebSocket/Polling） - 使用手動重新整理
- ❌ 頭像儲存方式改為檔案系統 - 繼續使用 Base64

**保持現狀**:
- ❌ 業務員端 API 端點（不受影響）
- ❌ JWT 認證機制（業務員端繼續使用）
- ❌ 資料庫 Schema（不需要新增表）
- ❌ 現有業務邏輯（Services, Models）

**未來功能**:
- 未來 Phase 2: Email 通知系統
- 未來 Phase 3: 進階 Analytics（更多圖表類型）
- 未來 Phase 4: 批量匯入/匯出功能增強

---

### Assumptions - 假設條件

**技術假設**:
1. ✅ Laravel 版本 >= 11.x（目前是 Laravel 12）
2. ✅ PHP 版本 >= 8.2（目前是 PHP 8.4）
3. ✅ MySQL 版本 >= 8.0
4. ✅ Composer 已安裝且可正常運作
5. ✅ Docker 環境運行正常

**業務假設**:
1. ✅ 管理員人數 2-5 位（小團隊）
2. ✅ 每日審核量 20-100 個項目
3. ✅ 尖峰時段待審核項目 < 500 個
4. ✅ 管理員使用桌面瀏覽器（Chrome/Firefox/Safari）
5. ✅ 不需要行動裝置優化（管理員主要在辦公室使用）

**資料假設**:
1. ✅ 使用者總數 1,000-5,000 人
2. ✅ 業務員數量 500-2,000 人
3. ✅ 公司數量 300-1,000 家
4. ✅ 資料成長率: 每月 +5-10%

---

## 🔧 Technical Design

### 1. Filament Resources 架構

#### Dashboard (Home Page)

**Widgets**:
```php
// app/Filament/Widgets/StatsOverview.php
class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('總業務員數', User::where('role', 'salesperson')->count())
                ->description('活躍業務員: ' . User::where('salesperson_status', 'approved')->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('待審核項目', $this->getPendingCount())
                ->description('需要處理的審核')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('總公司數', Company::count())
                ->description('已驗證公司')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
        ];
    }
}

// app/Filament/Widgets/RecentApplications.php
class RecentApplications extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return User::query()
            ->where('role', 'salesperson')
            ->where('salesperson_status', 'pending')
            ->latest('salesperson_applied_at')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('姓名'),
            TextColumn::make('email')->label('Email'),
            TextColumn::make('salesperson_applied_at')
                ->label('申請時間')
                ->dateTime('Y-m-d H:i'),
        ];
    }
}

// app/Filament/Widgets/ApplicationChart.php
class ApplicationChart extends ChartWidget
{
    protected static ?string $heading = '業務員申請趨勢';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // 取得過去 30 天的申請數據
        $data = User::where('role', 'salesperson')
            ->where('salesperson_applied_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(salesperson_applied_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => '申請數',
                    'data' => $data->pluck('count')->toArray(),
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
```

---

#### SalespersonApplicationResource

**功能**: 業務員申請審核（最重要的功能）

```php
// app/Filament/Resources/SalespersonApplicationResource.php
class SalespersonApplicationResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = '業務員申請';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', User::ROLE_SALESPERSON)
            ->where('salesperson_status', User::STATUS_PENDING)
            ->with('salespersonProfile')
            ->orderBy('salesperson_applied_at', 'asc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('基本資料')
                    ->schema([
                        TextInput::make('name')->label('姓名')->disabled(),
                        TextInput::make('email')->label('Email')->disabled(),
                        DateTimePicker::make('salesperson_applied_at')
                            ->label('申請時間')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('業務員資料')
                    ->schema([
                        Textarea::make('salespersonProfile.bio')
                            ->label('個人簡介')
                            ->disabled(),
                        TextInput::make('salespersonProfile.years_of_experience')
                            ->label('年資')
                            ->disabled(),
                        // 更多欄位...
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('salespersonProfile.years_of_experience')
                    ->label('年資')
                    ->sortable(),

                TextColumn::make('salesperson_applied_at')
                    ->label('申請時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                BadgeColumn::make('salesperson_status')
                    ->label('狀態')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->filters([
                SelectFilter::make('salesperson_status')
                    ->label('狀態')
                    ->options([
                        'pending' => '待審核',
                        'approved' => '已批准',
                        'rejected' => '已拒絕',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('批准')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->approveSalesperson();
                        Notification::make()
                            ->success()
                            ->title('已批准業務員申請')
                            ->send();
                    })
                    ->visible(fn (User $record) => $record->salesperson_status === 'pending'),

                Action::make('reject')
                    ->label('拒絕')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('拒絕原因')
                            ->required()
                            ->maxLength(500),
                        TextInput::make('reapply_days')
                            ->label('可重新申請天數')
                            ->numeric()
                            ->default(30)
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->rejectSalesperson(
                            $data['rejection_reason'],
                            $data['reapply_days'] ?? 30
                        );
                        Notification::make()
                            ->success()
                            ->title('已拒絕業務員申請')
                            ->send();
                    })
                    ->visible(fn (User $record) => $record->salesperson_status === 'pending'),
            ])
            ->bulkActions([
                BulkAction::make('approve_all')
                    ->label('批量批准')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            if ($record->salesperson_status === 'pending') {
                                $record->approveSalesperson();
                            }
                        }
                        Notification::make()
                            ->success()
                            ->title('已批准 ' . $records->count() . ' 個申請')
                            ->send();
                    }),
            ]);
    }
}
```

---

#### UserResource

**功能**: 使用者管理（CRUD + 狀態管理）

```php
// app/Filament/Resources/UserResource.php
class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = '使用者管理';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                Select::make('role')
                    ->options([
                        'admin' => '管理員',
                        'salesperson' => '業務員',
                        'user' => '一般使用者',
                    ])
                    ->required(),
                Select::make('status')
                    ->options([
                        'active' => '啟用',
                        'inactive' => '停用',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'admin',
                        'success' => 'salesperson',
                        'secondary' => 'user',
                    ]),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role'),
                SelectFilter::make('status'),
            ])
            ->actions([
                EditAction::make(),

                Action::make('toggle_status')
                    ->label(fn (User $record) => $record->status === 'active' ? '停用' : '啟用')
                    ->icon('heroicon-o-power')
                    ->color(fn (User $record) => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        if ($record->role === 'admin') {
                            Notification::make()
                                ->danger()
                                ->title('無法停用管理員帳號')
                                ->send();
                            return;
                        }

                        $record->status = $record->status === 'active' ? 'inactive' : 'active';
                        $record->save();

                        Notification::make()
                            ->success()
                            ->title('使用者狀態已更新')
                            ->send();
                    }),

                DeleteAction::make()
                    ->before(function (DeleteAction $action, User $record) {
                        if ($record->role === 'admin') {
                            Notification::make()
                                ->danger()
                                ->title('無法刪除管理員帳號')
                                ->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }
}
```

---

### 2. 認證整合方案

**問題**: 目前使用 JWT 認證（API），Filament 預設使用 Session 認證

**解決方案**: 雙認證機制（API 用 JWT, Filament 用 Session）

**實作步驟**:

1. **配置 Filament Auth**:
```php
// config/filament.php
return [
    'auth' => [
        'guard' => 'web', // 使用 web guard (session)
        'pages' => [
            'login' => \App\Filament\Pages\Login::class,
        ],
    ],
    'middleware' => [
        'auth' => [
            \Filament\Http\Middleware\Authenticate::class,
        ],
    ],
];
```

2. **建立 Admin Middleware**:
```php
// app/Http/Middleware/FilamentAdminMiddleware.php
class FilamentAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
```

3. **User Model 調整**:
```php
// app/Models/User.php
class User extends Authenticatable implements FilamentUser
{
    public function canAccessFilament(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
```

**結果**:
- ✅ API 端點繼續使用 JWT (`Authorization: Bearer {token}`)
- ✅ Filament Admin 使用 Session (`Cookie: laravel_session`)
- ✅ 兩者互不干擾，獨立運作

---

### 3. 權限架構

**使用套件**: Spatie Laravel Permission + Filament Shield

**安裝**:
```bash
composer require spatie/laravel-permission
composer require bezhansalleh/filament-shield
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan shield:install
```

**權限定義**:
```php
// database/seeders/RolePermissionSeeder.php
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // 建立 Super Admin 角色（所有權限）
        $superAdmin = Role::create(['name' => 'super_admin']);

        // 建立 Admin 角色（模組權限）
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_salesperson_applications',
            'approve_salesperson_applications',
            'reject_salesperson_applications',
            'view_users',
            'edit_users',
            'delete_users',
            // ... 更多權限
        ]);

        // 建立 Reviewer 角色（僅審核權限）
        $reviewer = Role::create(['name' => 'reviewer']);
        $reviewer->givePermissionTo([
            'view_salesperson_applications',
            'approve_salesperson_applications',
            'reject_salesperson_applications',
        ]);
    }
}
```

**Filament Resource 權限整合**:
```php
// app/Filament/Resources/UserResource.php
class UserResource extends Resource
{
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_users');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_users');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('edit_users');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete_users');
    }
}
```

---

### 4. Theme 客製化方案

**目標**: 符合品牌風格，專業且易用

**客製化項目**:

1. **品牌顏色**:
```php
// config/filament.php
return [
    'theme' => [
        'colors' => [
            'primary' => '#3B82F6', // 藍色
            'success' => '#10B981', // 綠色
            'warning' => '#F59E0B', // 橘色
            'danger' => '#EF4444',  // 紅色
        ],
    ],
];
```

2. **Logo 設定**:
```php
// app/Providers/FilamentServiceProvider.php
Filament::serving(function () {
    Filament::registerNavigationGroups([
        '審核管理' => [
            'sort' => 1,
            'icon' => 'heroicon-o-clipboard-document-check',
        ],
        '系統管理' => [
            'sort' => 2,
            'icon' => 'heroicon-o-cog-6-tooth',
        ],
    ]);
});
```

3. **自訂 CSS**:
```css
/* resources/css/filament.css */
.filament-main {
    background-color: #F9FAFB;
}

.filament-sidebar {
    border-right: 1px solid #E5E7EB;
}

.filament-stats-card {
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
```

---

## 📊 Development Scope Judgment

```json
{
  "backend": true,
  "frontend": false,
  "ui_design": true,
  "architecture": true,
  "database": true,
  "filament_migration": true,
  "priority": "high",

  "scope_details": {
    "backend": {
      "install_filament": true,
      "create_resources": 8,
      "create_widgets": 4,
      "auth_integration": "session",
      "permissions": "spatie + shield",
      "estimated_hours": 3.0
    },
    "ui_design": {
      "theme_customization": "light",
      "brand_colors": true,
      "logo": true,
      "estimated_hours": 0.5
    },
    "architecture": {
      "auth_refactor": "dual (JWT + Session)",
      "middleware": true,
      "estimated_hours": 0.5
    },
    "database": {
      "new_tables": 2,
      "migrations": ["roles", "permissions"],
      "estimated_hours": 0.5
    },
    "frontend": {
      "remove_nextjs_admin": true,
      "cleanup": true,
      "estimated_hours": 0.5
    },
    "testing": {
      "resource_tests": true,
      "permission_tests": true,
      "ui_tests": true,
      "estimated_hours": 1.0
    },
    "documentation": {
      "update_claude_md": true,
      "filament_guide": true,
      "estimated_hours": 0.5
    }
  },

  "total_estimated_hours": 4.5,
  "developer_count": 1,
  "timeline_days": 1
}
```

---

## 🚀 Migration Strategy

### Phase 1: 環境準備 (30 分鐘)

**任務清單**:
```bash
# 1. 安裝 Filament Packages
composer require filament/filament:"^3.0"
composer require bezhansalleh/filament-shield
composer require spatie/laravel-permission

# 2. 執行安裝指令
php artisan filament:install --panels

# 3. 建立 Admin Panel
php artisan make:filament-panel admin

# 4. 發布配置檔案
php artisan vendor:publish --tag=filament-config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 5. 執行 Migrations
php artisan migrate

# 6. 建立 Super Admin
php artisan make:filament-user
```

**驗收標準**:
- ✅ 可訪問 `/admin` 路由
- ✅ 登入畫面正常顯示
- ✅ Admin 帳號可登入

---

### Phase 2: Filament Resources 開發 (2 小時)

**2.1 Dashboard + Widgets (30 分鐘)**:
```bash
# 建立 Widgets
php artisan make:filament-widget StatsOverview --type=stats-overview
php artisan make:filament-widget RecentApplications --type=table
php artisan make:filament-widget ApplicationChart --type=chart
php artisan make:filament-widget PendingApprovals --type=stat

# 實作 Widgets
# - StatsOverview: 4 個統計卡片
# - RecentApplications: 最近 10 個申請
# - ApplicationChart: 申請趨勢圖（過去 30 天）
# - PendingApprovals: 待審核數量
```

**驗收標準**:
- ✅ Dashboard 顯示 4 個統計數字
- ✅ 圖表正確渲染
- ✅ 資料即時更新

---

**2.2 SalespersonApplicationResource (20 分鐘)**:
```bash
php artisan make:filament-resource SalespersonApplication --model=User

# 實作功能:
# - Table: 姓名、Email、年資、申請時間、狀態
# - Filters: 狀態篩選
# - Actions: 批准、拒絕（含原因表單）
# - Bulk Actions: 批量批准
```

**驗收標準**:
- ✅ 列表正確顯示待審核申請
- ✅ 批准功能正常
- ✅ 拒絕功能正常（含原因）
- ✅ 批量批准功能正常

---

**2.3 CompanyResource (15 分鐘)**:
```bash
php artisan make:filament-resource Company

# 實作功能:
# - Table: 公司名稱、統編、狀態、建立時間
# - Filters: 審核狀態
# - Actions: 批准、拒絕
```

---

**2.4 ExperienceResource (15 分鐘)**:
```bash
php artisan make:filament-resource Experience

# 實作功能:
# - Table: 職位、公司、期間、狀態
# - Relations: 關聯到 User
# - Actions: 批准、拒絕（含原因）
```

---

**2.5 CertificationResource (15 分鐘)**:
```bash
php artisan make:filament-resource Certification

# 實作功能:
# - Table: 證照名稱、核發單位、取得日期、狀態
# - Actions: 批准、拒絕
```

---

**2.6 UserResource (20 分鐘)**:
```bash
php artisan make:filament-resource User

# 實作功能:
# - Table: 姓名、Email、角色、狀態、建立時間
# - Filters: 角色、狀態
# - Actions: 編輯、啟用/停用、刪除
# - Business Rules: 不能停用/刪除管理員、不能操作自己
```

---

**2.7 Settings Resources (10 分鐘)**:
```bash
php artisan make:filament-resource Region
php artisan make:filament-resource Industry

# 實作功能:
# - Table: 名稱、排序
# - CRUD: 新增、編輯、刪除
```

---

### Phase 3: Theme 客製化 (30 分鐘)

**任務清單**:
```bash
# 1. 建立自訂 Theme
php artisan make:filament-theme

# 2. 配置品牌顏色
# - 修改 config/filament.php
# - 設定 primary, success, warning, danger 顏色

# 3. 設定 Logo
# - 放置 Logo 圖檔到 public/images/
# - 配置 Filament logo 路徑

# 4. 客製化導航選單
# - 建立導航群組（審核管理、系統管理）
# - 設定圖示、排序

# 5. 調整 Widgets 排版
# - 設定 columnSpan
# - 調整 sort order
```

**驗收標準**:
- ✅ 品牌顏色正確套用
- ✅ Logo 正確顯示
- ✅ 導航選單分組清晰
- ✅ Dashboard 排版美觀

---

### Phase 4: 測試 (1 小時)

**4.1 功能測試 (30 分鐘)**:
```php
// tests/Feature/Filament/SalespersonApplicationResourceTest.php
test('admin can view salesperson applications', function () {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    $response = get('/admin/salesperson-applications');
    $response->assertOk();
});

test('admin can approve salesperson application', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->salespersonPending()->create();

    actingAs($admin);

    livewire(SalespersonApplicationResource\Pages\ListSalespersonApplications::class)
        ->callTableAction('approve', $applicant);

    expect($applicant->fresh()->salesperson_status)->toBe('approved');
});

// 更多測試...
```

**測試清單**:
- ✅ 所有 Resources 可正常訪問
- ✅ 所有 Actions 執行正確
- ✅ Bulk Actions 正常運作
- ✅ Filters 過濾正確
- ✅ Pagination 正常

---

**4.2 權限測試 (15 分鐘)**:
```php
test('non-admin cannot access filament', function () {
    $user = User::factory()->user()->create();
    actingAs($user);

    $response = get('/admin');
    $response->assertRedirect('/admin/login');
});

test('reviewer can only view applications', function () {
    $reviewer = User::factory()->create(['role' => 'reviewer']);
    actingAs($reviewer);

    // Can view
    expect($reviewer->can('view_salesperson_applications'))->toBeTrue();

    // Cannot edit users
    expect($reviewer->can('edit_users'))->toBeFalse();
});
```

---

**4.3 UI 測試 (15 分鐘)**:
```php
// 使用 Filament Testing Helpers
test('dashboard widgets render correctly', function () {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    livewire(\App\Filament\Widgets\StatsOverview::class)
        ->assertSee('總業務員數')
        ->assertSee('待審核項目');
});
```

---

### Phase 5: 部署與清理 (30 分鐘)

**5.1 部署 Filament (10 分鐘)**:
```bash
# 1. 編譯 Assets
npm run build

# 2. 優化 Filament
php artisan filament:optimize

# 3. 清除快取
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# 4. 執行 Migrations (生產環境)
php artisan migrate --force

# 5. 建立 Admin 帳號
php artisan make:filament-user
```

---

**5.2 移除 Next.js Admin (10 分鐘)**:
```bash
cd /path/to/frontend

# 1. 移除 Admin Pages
rm -rf app/admin

# 2. 移除 Admin 組件
rm -rf components/admin

# 3. 移除 Admin API 整合
rm -rf lib/api/admin

# 4. 更新路由
# 移除 app/admin 相關路由配置

# 5. 清理依賴（如果有特定 admin 套件）
# npm uninstall <admin-specific-packages>
```

---

**5.3 更新文檔 (10 分鐘)**:
```bash
# 1. 更新 Backend CLAUDE.md
# - 新增 Filament 章節
# - 說明 Admin 訪問方式
# - 列出 Resources 清單

# 2. 建立 Filament 使用手冊
# - 登入方式
# - 功能說明
# - 常見操作

# 3. 更新部署文檔
# - 新增 Filament 部署步驟
# - 環境變數配置
```

**驗收標準**:
- ✅ Filament 在生產環境正常運作
- ✅ Next.js 專案不再有 Admin 相關程式碼
- ✅ 文檔完整更新
- ✅ 管理員可正常使用所有功能

---

## 📈 Success Metrics

### 功能覆蓋率

| 類別 | API 端點數量 | Filament Resource | 覆蓋率 |
|------|-------------|-------------------|--------|
| 統計儀表板 | 5 | Dashboard + 4 Widgets | 100% |
| 待審核總覽 | 1 | Dashboard Widget | 100% |
| 業務員申請 | 3 | SalespersonApplicationResource | 100% |
| 公司審核 | 1 | CompanyResource | 100% |
| 工作經驗審核 | 2 | ExperienceResource | 100% |
| 證照審核 | 2 | CertificationResource | 100% |
| 使用者管理 | 3 | UserResource | 100% |
| 設定管理 | 2 | RegionResource + IndustryResource | 100% |
| **總計** | **18** | **8 Resources + 4 Widgets** | **100%** |

---

### 效能指標 (參考 metrics-standards.md)

**目標值**:
| 指標 | 目標值 | 測量方式 |
|------|--------|----------|
| Filament 首頁 LCP | < 1s | Chrome DevTools |
| 列表頁面載入 | < 500ms | Network Tab |
| 操作回應時間 | < 300ms | 批准/拒絕操作 |
| Dashboard 載入 | < 2s | 包含所有 Widgets |
| 資料庫查詢數 | < 20 queries/page | Laravel Debugbar |
| Memory Usage | < 128MB | php artisan route:list |

**測試結果** (預期):
| 指標 | 實測值 | 狀態 |
|------|--------|------|
| Filament 首頁 LCP | 0.8s | ✅ 達標 |
| 列表頁面載入 | 350ms | ✅ 達標 |
| 操作回應時間 | 180ms | ✅ 達標 |
| Dashboard 載入 | 1.5s | ✅ 達標 |

---

### 程式碼品質指標

**測試覆蓋率**:
```bash
# 目標
- Feature Tests: >= 80%
- Unit Tests: >= 90%
- Overall: >= 85%

# 測試數量
- Filament Resource Tests: 40+ tests
- Widget Tests: 10+ tests
- Permission Tests: 15+ tests
- Total: 65+ new tests
```

**靜態分析**:
```bash
# PHPStan Level 9
vendor/bin/phpstan analyse --level=9

# 預期結果
✓ 0 errors
✓ All Filament Resources pass
✓ All Widgets pass
```

---

### 使用者體驗指標

**操作效率**:
| 任務 | Next.js Admin | Filament Admin | 改善幅度 |
|------|---------------|----------------|----------|
| 批准 1 個申請 | 3 clicks | 2 clicks | -33% |
| 批量批准 10 個 | 30 clicks | 3 clicks | -90% |
| 搜尋使用者 | 輸入 + Enter | 即時搜尋 | 更快 |
| 篩選狀態 | 2 clicks | 1 click | -50% |
| 查看統計 | 切換頁面 | Dashboard 直接看 | 更快 |

**學習曲線**:
- Filament 內建 UI/UX: 符合 Laravel 開發者習慣
- 無需學習 React/Next.js
- 文檔完整，容易上手

---

### 維護成本降低

**Before (Next.js + Laravel)**:
```
新增管理功能流程:
1. 設計 API 規格 (30 min)
2. 實作 Laravel API (1 hour)
3. 實作 React 組件 (1.5 hours)
4. API 整合與狀態管理 (1 hour)
5. 測試 (1 hour)
Total: ~5 hours
```

**After (Filament)**:
```
新增管理功能流程:
1. 建立 Filament Resource (30 min)
2. 定義 Table 和 Form (30 min)
3. 測試 (30 min)
Total: ~1.5 hours
```

**效率提升**: 70%+ (5h → 1.5h)

---

## ⏱️ Timeline Estimation

### 詳細時程規劃

**總工時**: 4.5 小時
**開發人員**: 1 人
**預計天數**: 1 天（工作日）

| Phase | 任務 | 預估時間 | 累積時間 |
|-------|------|----------|----------|
| **Phase 1** | **環境準備** | **30 分鐘** | **0.5h** |
| | 安裝 Filament packages | 10 min | |
| | 執行安裝與配置 | 10 min | |
| | 建立 Admin User | 5 min | |
| | 驗證環境 | 5 min | |
| **Phase 2** | **Resources 開發** | **2 小時** | **2.5h** |
| | Dashboard + Widgets | 30 min | |
| | SalespersonApplicationResource | 20 min | |
| | CompanyResource | 15 min | |
| | ExperienceResource | 15 min | |
| | CertificationResource | 15 min | |
| | UserResource | 20 min | |
| | Settings Resources | 10 min | |
| **Phase 3** | **Theme 客製化** | **30 分鐘** | **3.0h** |
| | 配置品牌顏色 | 10 min | |
| | 設定 Logo | 5 min | |
| | 導航選單設定 | 10 min | |
| | Widgets 排版調整 | 5 min | |
| **Phase 4** | **測試** | **1 小時** | **4.0h** |
| | 功能測試 | 30 min | |
| | 權限測試 | 15 min | |
| | UI 測試 | 15 min | |
| **Phase 5** | **部署與清理** | **30 分鐘** | **4.5h** |
| | 部署 Filament | 10 min | |
| | 移除 Next.js Admin | 10 min | |
| | 更新文檔 | 10 min | |

**里程碑**:
- ✅ **T+0.5h**: 環境準備完成，可登入 Filament
- ✅ **T+2.5h**: 所有 Resources 開發完成
- ✅ **T+3.0h**: Theme 客製化完成
- ✅ **T+4.0h**: 測試完成，品質達標
- ✅ **T+4.5h**: 部署完成，正式上線

---

## 🚨 Risk Assessment

### 技術風險

| 風險 | 機率 | 影響 | 緩解措施 | 負責人 |
|------|------|------|----------|--------|
| **認證整合問題** | Medium | High | - 先在開發環境測試雙認證機制<br>- 準備 Rollback 方案<br>- JWT API 不受影響 | Backend Dev |
| **資料遷移失敗** | Low | Medium | - 使用現有資料表，無需遷移<br>- Permissions 表單獨建立<br>- 先在測試環境驗證 | Backend Dev |
| **效能問題** | Low | Medium | - 使用 Eloquent Eager Loading<br>- 啟用 Query Caching<br>- 監控 N+1 Query 問題 | Backend Dev |
| **Filament 學習曲線** | Low | Low | - Filament 文檔完整<br>- 社群活躍，問題容易解決<br>- 符合 Laravel 習慣 | Backend Dev |
| **Theme 客製化限制** | Low | Low | - Filament 高度可客製化<br>- 提供完整 Blade 覆寫機制 | UI Designer |

---

### 業務風險

| 風險 | 機率 | 影響 | 緩解措施 | 負責人 |
|------|------|------|----------|--------|
| **管理員無法使用** | Low | High | - 完整功能測試<br>- 管理員訓練<br>- 提供使用手冊 | PM |
| **審核流程中斷** | Low | High | - 在非尖峰時段部署<br>- 保留 API 端點作為備援<br>- 準備 Rollback 計畫 | PM |
| **資料遺失** | Very Low | Critical | - 部署前完整備份<br>- 使用現有資料表<br>- 測試環境完整驗證 | DevOps |
| **功能缺失** | Low | Medium | - 詳細功能對照表<br>- 100% 功能覆蓋檢查<br>- UAT 測試 | QA |

---

### Rollback 計畫

**如果遷移失敗，回退步驟**:
```bash
# 1. 恢復 Next.js Admin Pages (從 Git)
git checkout main -- frontend/app/admin

# 2. 停用 Filament Routes
# 暫時註解 app/Providers/FilamentServiceProvider.php

# 3. 恢復資料庫 (如果有變更)
php artisan migrate:rollback --step=1

# 4. 清除快取
php artisan optimize:clear

# 5. 驗證 API 正常運作
curl http://localhost:8080/api/admin/statistics
```

**Rollback 時間**: < 15 分鐘

---

## 📚 Dependencies

### 必要套件

```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "filament/filament": "^3.2",
    "spatie/laravel-permission": "^6.0",
    "bezhansalleh/filament-shield": "^3.0"
  },
  "require-dev": {
    "pestphp/pest": "^3.0",
    "pestphp/pest-plugin-laravel": "^3.0"
  }
}
```

---

### 外部依賴

**無外部服務依賴**:
- ✅ 不需要新的資料庫
- ✅ 不需要 Redis（可選）
- ✅ 不需要第三方 API
- ✅ 不需要 CDN

---

## 🎓 Training & Documentation

### 管理員訓練計畫

**訓練時間**: 30 分鐘
**訓練對象**: 2-5 位管理員

**訓練內容**:
1. **登入與導航** (5 分鐘)
   - 訪問 `/admin`
   - 輸入帳號密碼
   - 認識 Dashboard
   - 了解導航選單

2. **審核操作** (10 分鐘)
   - 查看待審核申請
   - 批准單一申請
   - 拒絕申請（填寫原因）
   - 批量批准

3. **使用者管理** (5 分鐘)
   - 搜尋使用者
   - 篩選使用者
   - 啟用/停用使用者
   - 刪除使用者

4. **統計查看** (5 分鐘)
   - Dashboard 數據解讀
   - 圖表分析
   - 趨勢觀察

5. **常見問題** (5 分鐘)
   - 忘記密碼怎麼辦
   - 如何快速審核
   - 如何匯出資料

---

### 文檔清單

**必須建立的文檔**:

1. **Filament Admin 使用手冊** (`docs/filament-admin-guide.md`)
   - 功能總覽
   - 操作步驟（附截圖）
   - 常見問題 FAQ

2. **Backend CLAUDE.md 更新**
   - 新增「Filament Admin」章節
   - 說明如何建立 Admin User
   - 列出 Resources 清單

3. **部署文檔更新** (`docs/deployment.md`)
   - 新增 Filament 部署步驟
   - 環境變數配置
   - Rollback 流程

4. **API 文檔更新** (`docs/api-reference.md`)
   - 標記 Admin API 端點已廢棄（或保留）
   - 說明 Filament Admin 替代方案

---

## ✅ Acceptance Criteria

### 功能驗收

**Dashboard**:
- [ ] 顯示 4 個統計卡片（業務員數、待審核、公司數、其他）
- [ ] 顯示申請趨勢圖（過去 30 天）
- [ ] 顯示最近 10 個申請列表
- [ ] 資料即時更新

**業務員申請審核**:
- [ ] 列表顯示所有待審核申請
- [ ] 可搜尋業務員姓名、Email
- [ ] 可篩選狀態（待審核、已批准、已拒絕）
- [ ] 批准功能正常
- [ ] 拒絕功能正常（需填寫原因）
- [ ] 批量批准功能正常
- [ ] 操作後有 Toast 通知

**公司審核**:
- [ ] 列表顯示所有公司
- [ ] 可篩選審核狀態
- [ ] 批准功能正常
- [ ] 拒絕功能正常

**工作經驗審核**:
- [ ] 列表顯示所有待審核經驗
- [ ] 批准功能正常
- [ ] 拒絕功能正常（含原因）
- [ ] 關聯顯示業務員資訊

**證照審核**:
- [ ] 列表顯示所有待審核證照
- [ ] 批准功能正常
- [ ] 拒絕功能正常

**使用者管理**:
- [ ] 列表顯示所有使用者
- [ ] 可搜尋姓名、Email
- [ ] 可篩選角色、狀態
- [ ] 編輯功能正常
- [ ] 啟用/停用功能正常
- [ ] 刪除功能正常
- [ ] 業務規則檢查（不能停用/刪除管理員）

**設定管理**:
- [ ] 地區列表正常顯示
- [ ] 產業列表正常顯示
- [ ] CRUD 功能正常

---

### 非功能驗收

**效能**:
- [ ] Filament 首頁 LCP < 1s
- [ ] 列表頁面載入 < 500ms
- [ ] 操作回應時間 < 300ms
- [ ] Dashboard 載入 < 2s
- [ ] 資料庫查詢數 < 20 queries/page

**安全性**:
- [ ] 非管理員無法訪問 `/admin`
- [ ] Session 認證正常運作
- [ ] 權限檢查正確（Shield）
- [ ] CSRF 保護啟用
- [ ] XSS 防護正常

**可用性**:
- [ ] 在 Chrome/Firefox/Safari 測試通過
- [ ] 響應式設計（桌面、筆電）
- [ ] 操作回饋清晰（Toast Notification）
- [ ] 錯誤訊息友善
- [ ] 確認對話框正常

**相容性**:
- [ ] PHP 8.4 正常運行
- [ ] Laravel 12 相容
- [ ] MySQL 8.0 相容
- [ ] Docker 環境正常

---

### 測試驗收

**測試覆蓋率**:
- [ ] Feature Tests >= 80%
- [ ] Unit Tests >= 90%
- [ ] Overall >= 85%

**測試通過**:
- [ ] 所有 Filament Resource Tests 通過
- [ ] 所有 Widget Tests 通過
- [ ] 所有 Permission Tests 通過
- [ ] PHPStan Level 9 無錯誤
- [ ] Laravel Pint 無格式問題

---

### 文檔驗收

- [ ] Filament Admin 使用手冊完成
- [ ] Backend CLAUDE.md 更新
- [ ] 部署文檔更新
- [ ] API 文檔更新
- [ ] 管理員訓練完成

---

### 清理驗收

- [ ] Next.js Admin Pages 已移除
- [ ] Next.js Admin 組件已移除
- [ ] Admin API 整合程式碼已移除
- [ ] 無相關依賴殘留
- [ ] Git 提交乾淨

---

## 📊 Post-Migration Monitoring

### 監控指標

**第一週監控** (密集):
```
Day 1-3:
- 每小時檢查錯誤日誌
- 監控管理員操作
- 收集使用者回饋

Day 4-7:
- 每日檢查一次
- 統計使用頻率
- 效能數據分析
```

**長期監控** (持續):
```
每週:
- 檢查錯誤日誌
- 效能指標（LCP, 查詢時間）
- 使用者滿意度

每月:
- 功能使用統計
- 維護成本評估
- 優化機會識別
```

---

### 成功指標追蹤

**預期結果** (1 個月後):
- ✅ 管理員滿意度 >= 90%
- ✅ 操作效率提升 >= 50%
- ✅ 維護工時減少 >= 60%
- ✅ 錯誤率 < 1%
- ✅ 無 Rollback 需求

---

## 🎯 Conclusion

### 專案總結

**遷移動機**:
將管理員介面從 Next.js 遷移至 Laravel Filament，實現單一技術棧、降低維護成本、提升開發效率。

**預期效益**:
1. **開發效率**: 新增管理功能時間從 5 小時 → 1.5 小時（提升 70%）
2. **維護成本**: 單一專案維護，降低 50%+ 維護工時
3. **使用者體驗**: Filament 提供更好的 CRUD 介面和批量操作
4. **技術簡化**: 完全使用 Laravel 生態系統，無需 React 知識

**投資回報**:
- 一次性投資: 4.5 小時
- 長期效益: 每個新功能節省 3.5 小時
- ROI: 開發 2 個新功能即回本

**建議執行時間**:
立即執行，選擇非尖峰時段（週間上午），確保有完整一天時間完成遷移與驗證。

---

**提案狀態**: ✅ Ready for Implementation
**下一步**: 執行 Phase 1 環境準備

---

**附錄**:
- [Filament 官方文檔](https://filamentphp.com/docs)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission)
- [Filament Shield](https://github.com/bezhanSalleh/filament-shield)
