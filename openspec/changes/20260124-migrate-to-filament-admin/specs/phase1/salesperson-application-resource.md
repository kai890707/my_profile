# Filament Phase 1: Salesperson Application Resource 規格

**專案**: YAMU Backend - Filament Admin Panel
**Phase**: 1 - 業務員申請審核 Resource
**預估時間**: 1 小時

---

## 🎯 Resource Overview

### Purpose

建立 **SalespersonApplicationResource** - 管理員用來審核業務員申請的完整介面。

### Scope

**Phase 1 包含**:
- ✅ 列表頁面 (Table)
- ✅ 詳情頁面 (View/Infolist)
- ✅ 審核 Actions (Approve, Reject)
- ✅ 批量操作 (Bulk Approve)
- ✅ 篩選器 (Filters)
- ✅ 搜尋功能 (Search)
- ✅ 排序功能 (Sortable)

**Phase 1 不包含** (Phase 2+):
- ❌ 編輯功能 (Edit Form)
- ❌ 建立功能 (Create)
- ❌ Email 通知
- ❌ 審核歷史記錄查看

---

## 📊 Data Model

### Eloquent Query

**Model**: `App\Models\User`

**Query Scope**:
```php
User::query()
    ->where('role', User::ROLE_SALESPERSON)
    ->where('salesperson_status', User::STATUS_PENDING)
    ->with([
        'salespersonProfile',
        'salespersonProfile.company',
    ])
    ->orderBy('salesperson_applied_at', 'asc')  // FIFO 順序
```

**重要**:
- 只顯示 `salesperson_status = 'pending'` 的申請
- 使用 Eager Loading 避免 N+1 Query
- 按申請時間排序 (先申請先處理)

---

## 🗂️ Resource Definition

### 檔案位置

**檔案**: `app/Filament/Resources/SalespersonApplicationResource.php`

### Resource Class

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalespersonApplicationResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SalespersonApplicationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = '業務員申請';

    protected static ?string $modelLabel = '業務員申請';

    protected static ?string $pluralModelLabel = '業務員申請';

    protected static ?string $navigationGroup = '審核管理';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Get the query for the resource.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', User::ROLE_SALESPERSON)
            ->where('salesperson_status', User::STATUS_PENDING)
            ->with([
                'salespersonProfile',
                'salespersonProfile.company',
            ])
            ->orderBy('salesperson_applied_at', 'asc');
    }

    /**
     * Define the table.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columns defined below
            ])
            ->filters([
                // Filters defined below
            ])
            ->actions([
                // Actions defined below
            ])
            ->bulkActions([
                // Bulk actions defined below
            ])
            ->defaultSort('salesperson_applied_at', 'asc')
            ->poll('30s')  // 每 30 秒自動刷新
            ->striped();
    }

    /**
     * Define the infolist (view page).
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Schema defined below
            ]);
    }

    /**
     * Get the pages for the resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalespersonApplications::route('/'),
            'view' => Pages\ViewSalespersonApplication::route('/{record}'),
        ];
    }

    /**
     * Get the navigation badge.
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    /**
     * Get the navigation badge color.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getEloquentQuery()->count();

        if ($count === 0) {
            return 'success';
        }

        if ($count > 10) {
            return 'danger';
        }

        return 'warning';
    }
}
```

---

## 📋 Table Columns

### Column Definitions

```php
->columns([
    // 1. 姓名
    Tables\Columns\TextColumn::make('salespersonProfile.full_name')
        ->label('姓名')
        ->searchable()
        ->sortable()
        ->weight('medium')
        ->copyable()
        ->copyMessage('已複製姓名')
        ->default('未填寫'),

    // 2. Email
    Tables\Columns\TextColumn::make('email')
        ->label('Email')
        ->searchable()
        ->copyable()
        ->copyMessage('已複製 Email')
        ->icon('heroicon-m-envelope')
        ->iconColor('primary'),

    // 3. 電話
    Tables\Columns\TextColumn::make('salespersonProfile.phone')
        ->label('電話')
        ->searchable()
        ->copyable()
        ->copyMessage('已複製電話')
        ->icon('heroicon-m-phone')
        ->iconColor('success')
        ->default('未填寫'),

    // 4. 公司
    Tables\Columns\TextColumn::make('salespersonProfile.company.name')
        ->label('公司')
        ->searchable()
        ->default('未填寫'),

    // 5. 申請時間
    Tables\Columns\TextColumn::make('salesperson_applied_at')
        ->label('申請時間')
        ->dateTime('Y-m-d H:i')
        ->sortable()
        ->since()  // 顯示相對時間 (e.g., "2 hours ago")
        ->description(fn (User $record): string => $record->salesperson_applied_at->diffForHumans()),

    // 6. 等待時間
    Tables\Columns\TextColumn::make('waiting_time')
        ->label('等待時間')
        ->state(function (User $record): string {
            $hours = $record->salesperson_applied_at->diffInHours(now());
            if ($hours < 24) {
                return "{$hours} 小時";
            }
            $days = $record->salesperson_applied_at->diffInDays(now());
            return "{$days} 天";
        })
        ->badge()
        ->color(function (User $record): string {
            $hours = $record->salesperson_applied_at->diffInHours(now());
            if ($hours < 24) {
                return 'success';
            } elseif ($hours < 72) {
                return 'warning';
            } else {
                return 'danger';
            }
        }),

    // 7. 狀態
    Tables\Columns\BadgeColumn::make('salesperson_status')
        ->label('狀態')
        ->colors([
            'warning' => 'pending',
            'success' => 'approved',
            'danger' => 'rejected',
        ])
        ->formatStateUsing(fn (string $state): string => match ($state) {
            'pending' => '待審核',
            'approved' => '已批准',
            'rejected' => '已拒絕',
            default => $state,
        }),
])
```

### Column Layout

| 欄位 | 寬度 | Searchable | Sortable | Copyable |
|------|------|------------|----------|----------|
| 姓名 | Auto | ✅ | ✅ | ✅ |
| Email | Auto | ✅ | ❌ | ✅ |
| 電話 | Auto | ✅ | ❌ | ✅ |
| 公司 | Auto | ✅ | ❌ | ❌ |
| 申請時間 | 150px | ❌ | ✅ | ❌ |
| 等待時間 | 100px | ❌ | ❌ | ❌ |
| 狀態 | 100px | ❌ | ❌ | ❌ |

---

## 🔍 Filters

### Filter Definitions

```php
->filters([
    // 1. 日期範圍篩選
    Tables\Filters\Filter::make('salesperson_applied_at')
        ->form([
            Forms\Components\DatePicker::make('applied_from')
                ->label('申請日期從'),
            Forms\Components\DatePicker::make('applied_until')
                ->label('申請日期至'),
        ])
        ->query(function (Builder $query, array $data): Builder {
            return $query
                ->when(
                    $data['applied_from'],
                    fn (Builder $query, $date): Builder => $query->whereDate('salesperson_applied_at', '>=', $date),
                )
                ->when(
                    $data['applied_until'],
                    fn (Builder $query, $date): Builder => $query->whereDate('salesperson_applied_at', '<=', $date),
                );
        })
        ->indicateUsing(function (array $data): array {
            $indicators = [];

            if ($data['applied_from'] ?? null) {
                $indicators[] = Tables\Filters\Indicator::make('申請日期從 ' . \Carbon\Carbon::parse($data['applied_from'])->toFormattedDateString())
                    ->removeField('applied_from');
            }

            if ($data['applied_until'] ?? null) {
                $indicators[] = Tables\Filters\Indicator::make('申請日期至 ' . \Carbon\Carbon::parse($data['applied_until'])->toFormattedDateString())
                    ->removeField('applied_until');
            }

            return $indicators;
        }),

    // 2. 等待時間篩選
    Tables\Filters\SelectFilter::make('waiting_time')
        ->label('等待時間')
        ->options([
            '24h' => '24 小時內',
            '1-3d' => '1-3 天',
            '3-7d' => '3-7 天',
            '7d+' => '超過 7 天',
        ])
        ->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            return match ($value) {
                '24h' => $query->where('salesperson_applied_at', '>=', now()->subHours(24)),
                '1-3d' => $query->whereBetween('salesperson_applied_at', [now()->subDays(3), now()->subDays(1)]),
                '3-7d' => $query->whereBetween('salesperson_applied_at', [now()->subDays(7), now()->subDays(3)]),
                '7d+' => $query->where('salesperson_applied_at', '<', now()->subDays(7)),
                default => $query,
            };
        }),

    // 3. 重置按鈕
    Tables\Filters\TernaryFilter::make('has_company')
        ->label('是否有公司')
        ->placeholder('全部')
        ->trueLabel('有公司')
        ->falseLabel('無公司')
        ->queries(
            true: fn (Builder $query) => $query->whereHas('salespersonProfile', fn ($q) => $q->whereNotNull('company_id')),
            false: fn (Builder $query) => $query->whereHas('salespersonProfile', fn ($q) => $q->whereNull('company_id')),
        ),
])
```

---

## ⚡ Actions

### Row Actions

```php
->actions([
    // 1. 查看詳情
    Tables\Actions\ViewAction::make()
        ->label('查看')
        ->icon('heroicon-m-eye')
        ->color('info'),

    // 2. 批准
    Tables\Actions\Action::make('approve')
        ->label('批准')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->requiresConfirmation()
        ->modalHeading('批准業務員申請')
        ->modalDescription(fn (User $record) => "確定要批准 {$record->salespersonProfile?->full_name} ({$record->email}) 的業務員申請嗎?")
        ->modalSubmitActionLabel('確定批准')
        ->action(function (User $record) {
            // 業務邏輯
            $record->approveSalesperson();

            // 成功通知
            Notification::make()
                ->success()
                ->title('已批准業務員申請')
                ->body("已批准 {$record->salespersonProfile?->full_name} 的業務員申請")
                ->send();
        })
        ->after(function () {
            // 刷新列表
            redirect()->route('filament.admin.resources.salesperson-applications.index');
        })
        ->visible(fn (User $record): bool => $record->salesperson_status === User::STATUS_PENDING),

    // 3. 拒絕
    Tables\Actions\Action::make('reject')
        ->label('拒絕')
        ->icon('heroicon-o-x-circle')
        ->color('danger')
        ->form([
            Forms\Components\Textarea::make('rejection_reason')
                ->label('拒絕原因')
                ->required()
                ->maxLength(500)
                ->rows(4)
                ->placeholder('請說明拒絕原因，例如：資料不完整、不符合資格等')
                ->helperText('此原因會顯示給申請者'),

            Forms\Components\TextInput::make('reapply_days')
                ->label('可重新申請天數')
                ->numeric()
                ->default(30)
                ->required()
                ->minValue(0)
                ->maxValue(365)
                ->suffix('天')
                ->helperText('設為 0 表示永久禁止申請'),
        ])
        ->modalHeading('拒絕業務員申請')
        ->modalDescription(fn (User $record) => "即將拒絕 {$record->salespersonProfile?->full_name} ({$record->email}) 的業務員申請")
        ->modalSubmitActionLabel('確定拒絕')
        ->action(function (User $record, array $data) {
            // 業務邏輯
            $record->rejectSalesperson(
                $data['rejection_reason'],
                $data['reapply_days'] ?? 30
            );

            // 成功通知
            Notification::make()
                ->warning()
                ->title('已拒絕業務員申請')
                ->body("已拒絕 {$record->salespersonProfile?->full_name} 的業務員申請")
                ->send();
        })
        ->after(function () {
            // 刷新列表
            redirect()->route('filament.admin.resources.salesperson-applications.index');
        })
        ->visible(fn (User $record): bool => $record->salesperson_status === User::STATUS_PENDING),
])
```

---

### Bulk Actions

```php
->bulkActions([
    // 1. 批量批准
    Tables\Actions\BulkAction::make('approve_all')
        ->label('批量批准')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->requiresConfirmation()
        ->modalHeading('批量批准業務員申請')
        ->modalDescription(fn (Collection $records) => "確定要批准這 {$records->count()} 個業務員申請嗎?")
        ->modalSubmitActionLabel('確定批准')
        ->action(function (Collection $records) {
            $count = 0;

            foreach ($records as $record) {
                if ($record->salesperson_status === User::STATUS_PENDING) {
                    $record->approveSalesperson();
                    $count++;
                }
            }

            // 成功通知
            Notification::make()
                ->success()
                ->title('批量批准完成')
                ->body("已批准 {$count} 個業務員申請")
                ->send();
        })
        ->deselectRecordsAfterCompletion(),

    // 2. 批量拒絕 (需要原因，先不實作)
    // Tables\Actions\BulkAction::make('reject_all') - Phase 2

    // 3. 匯出 (Phase 2+)
    // Tables\Actions\ExportBulkAction::make()
])
```

---

## 📄 Infolist (View Page)

### Schema Definition

```php
public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            // Section 1: 基本資料
            Infolists\Components\Section::make('基本資料')
                ->schema([
                    Infolists\Components\TextEntry::make('salespersonProfile.full_name')
                        ->label('姓名')
                        ->weight('bold')
                        ->size('lg'),

                    Infolists\Components\TextEntry::make('email')
                        ->label('Email')
                        ->icon('heroicon-m-envelope')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('salespersonProfile.phone')
                        ->label('電話')
                        ->icon('heroicon-m-phone')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('salesperson_applied_at')
                        ->label('申請時間')
                        ->dateTime('Y-m-d H:i:s')
                        ->since(),
                ])
                ->columns(2),

            // Section 2: 業務員資訊
            Infolists\Components\Section::make('業務員資訊')
                ->schema([
                    Infolists\Components\TextEntry::make('salespersonProfile.bio')
                        ->label('個人簡介')
                        ->columnSpanFull()
                        ->default('未填寫'),

                    Infolists\Components\TextEntry::make('salespersonProfile.specialties')
                        ->label('專長領域')
                        ->listWithLineBreaks()
                        ->bulleted()
                        ->default('未填寫'),

                    Infolists\Components\TextEntry::make('salespersonProfile.service_regions')
                        ->label('服務地區')
                        ->badge()
                        ->default('未填寫'),

                    Infolists\Components\TextEntry::make('salespersonProfile.company.name')
                        ->label('公司')
                        ->default('未填寫'),
                ])
                ->columns(2),

            // Section 3: 聯絡方式
            Infolists\Components\Section::make('聯絡方式')
                ->schema([
                    Infolists\Components\TextEntry::make('salespersonProfile.email_public')
                        ->label('公開 Email')
                        ->icon('heroicon-m-envelope')
                        ->copyable()
                        ->default('未填寫'),

                    Infolists\Components\TextEntry::make('salespersonProfile.line_id')
                        ->label('LINE ID')
                        ->icon('heroicon-m-chat-bubble-left')
                        ->copyable()
                        ->default('未填寫'),

                    Infolists\Components\TextEntry::make('salespersonProfile.wechat_id')
                        ->label('WeChat ID')
                        ->icon('heroicon-m-chat-bubble-left')
                        ->copyable()
                        ->default('未填寫'),

                    Infolists\Components\TextEntry::make('salespersonProfile.contact_preferences')
                        ->label('偏好聯絡方式')
                        ->badge()
                        ->default('未填寫'),
                ])
                ->columns(2),

            // Section 4: 審核狀態
            Infolists\Components\Section::make('審核狀態')
                ->schema([
                    Infolists\Components\TextEntry::make('salesperson_status')
                        ->label('狀態')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => '待審核',
                            'approved' => '已批准',
                            'rejected' => '已拒絕',
                            default => $state,
                        }),

                    Infolists\Components\TextEntry::make('salesperson_approved_at')
                        ->label('批准時間')
                        ->dateTime('Y-m-d H:i:s')
                        ->default('尚未批准')
                        ->visible(fn (User $record): bool => $record->salesperson_status === User::STATUS_APPROVED),

                    Infolists\Components\TextEntry::make('rejection_reason')
                        ->label('拒絕原因')
                        ->columnSpanFull()
                        ->color('danger')
                        ->visible(fn (User $record): bool => $record->salesperson_status === User::STATUS_REJECTED),

                    Infolists\Components\TextEntry::make('can_reapply_at')
                        ->label('可重新申請時間')
                        ->dateTime('Y-m-d')
                        ->visible(fn (User $record): bool => $record->salesperson_status === User::STATUS_REJECTED && $record->can_reapply_at),
                ])
                ->columns(2),

            // Section 5: 系統資訊
            Infolists\Components\Section::make('系統資訊')
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('帳號建立時間')
                        ->dateTime('Y-m-d H:i:s'),

                    Infolists\Components\TextEntry::make('updated_at')
                        ->label('最後更新時間')
                        ->dateTime('Y-m-d H:i:s')
                        ->since(),

                    Infolists\Components\TextEntry::make('role')
                        ->label('角色')
                        ->badge(),

                    Infolists\Components\TextEntry::make('status')
                        ->label('帳號狀態')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'active' => 'success',
                            'inactive' => 'danger',
                            default => 'gray',
                        }),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),
        ]);
}
```

---

## 📱 Page Classes

### List Page

**檔案**: `app/Filament/Resources/SalespersonApplicationResource/Pages/ListSalespersonApplications.php`

```php
<?php

namespace App\Filament\Resources\SalespersonApplicationResource\Pages;

use App\Filament\Resources\SalespersonApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalespersonApplications extends ListRecords
{
    protected static string $resource = SalespersonApplicationResource::class;

    protected static ?string $title = '業務員申請審核';

    protected function getHeaderActions(): array
    {
        return [
            // 可新增 Export Action (Phase 2)
        ];
    }

    /**
     * Get the polling interval.
     */
    protected function getPollingInterval(): ?string
    {
        return '30s';  // 每 30 秒自動刷新
    }
}
```

---

### View Page

**檔案**: `app/Filament/Resources/SalespersonApplicationResource/Pages/ViewSalespersonApplication.php`

```php
<?php

namespace App\Filament\Resources\SalespersonApplicationResource\Pages;

use App\Filament\Resources\SalespersonApplicationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSalespersonApplication extends ViewRecord
{
    protected static string $resource = SalespersonApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 批准 Action
            Actions\Action::make('approve')
                ->label('批准')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('批准業務員申請')
                ->modalDescription(fn () => "確定要批准 {$this->record->salespersonProfile?->full_name} 的業務員申請嗎?")
                ->action(function () {
                    $this->record->approveSalesperson();

                    Notification::make()
                        ->success()
                        ->title('已批准業務員申請')
                        ->send();

                    return redirect()->route('filament.admin.resources.salesperson-applications.index');
                })
                ->visible(fn (): bool => $this->record->salesperson_status === User::STATUS_PENDING),

            // 拒絕 Action
            Actions\Action::make('reject')
                ->label('拒絕')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('拒絕原因')
                        ->required()
                        ->maxLength(500)
                        ->rows(4),

                    Forms\Components\TextInput::make('reapply_days')
                        ->label('可重新申請天數')
                        ->numeric()
                        ->default(30)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->rejectSalesperson(
                        $data['rejection_reason'],
                        $data['reapply_days'] ?? 30
                    );

                    Notification::make()
                        ->warning()
                        ->title('已拒絕業務員申請')
                        ->send();

                    return redirect()->route('filament.admin.resources.salesperson-applications.index');
                })
                ->visible(fn (): bool => $this->record->salesperson_status === User::STATUS_PENDING),

            // 返回列表
            Actions\Action::make('back')
                ->label('返回列表')
                ->url(route('filament.admin.resources.salesperson-applications.index'))
                ->color('gray'),
        ];
    }
}
```

---

## 🔐 Permissions

### Permission Names

使用 Shield 自動生成的 permissions:

```
view_salesperson::application
view_any_salesperson::application
approve_salesperson::application
reject_salesperson::application
bulk_approve_salesperson::application
```

### Authorization

**在 Resource 中自動檢查**:

```php
// Filament 自動檢查這些方法
public static function canViewAny(): bool
{
    return auth()->user()->can('view_any_salesperson::application');
}

public static function canView(Model $record): bool
{
    return auth()->user()->can('view_salesperson::application');
}
```

---

## 🧪 Testing

### Feature Tests

**檔案**: `tests/Feature/Filament/SalespersonApplicationResourceTest.php`

```php
<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SalespersonApplicationResource;
use App\Filament\Resources\SalespersonApplicationResource\Pages\ListSalespersonApplications;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalespersonApplicationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 創建 super_admin role
        Role::create(['name' => 'super_admin', 'guard_name' => 'admin_session']);
    }

    /** @test */
    public function admin_can_view_salesperson_applications_list(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertSuccessful();
    }

    /** @test */
    public function list_shows_only_pending_applications(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('super_admin');

        // 創建不同狀態的業務員
        $pending = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $approved = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$approved]);
    }

    /** @test */
    public function admin_can_approve_application(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('super_admin');

        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->callTableAction('approve', $applicant);

        $applicant->refresh();

        $this->assertEquals(User::STATUS_APPROVED, $applicant->salesperson_status);
        $this->assertNotNull($applicant->salesperson_approved_at);
    }

    /** @test */
    public function admin_can_reject_application(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('super_admin');

        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->callTableAction('reject', $applicant, data: [
                'rejection_reason' => '資料不完整',
                'reapply_days' => 30,
            ]);

        $applicant->refresh();

        $this->assertEquals(User::STATUS_REJECTED, $applicant->salesperson_status);
        $this->assertEquals('資料不完整', $applicant->rejection_reason);
        $this->assertNotNull($applicant->can_reapply_at);
    }

    /** @test */
    public function admin_can_bulk_approve(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $admin->assignRole('super_admin');

        $applicants = User::factory()->count(3)->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin_session');

        Livewire::test(ListSalespersonApplications::class)
            ->callTableBulkAction('approve_all', $applicants);

        foreach ($applicants as $applicant) {
            $applicant->refresh();
            $this->assertEquals(User::STATUS_APPROVED, $applicant->salesperson_status);
        }
    }

    /** @test */
    public function non_admin_cannot_access_resource(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user, 'admin_session');

        $this->get('/filament/admin/salesperson-applications')
            ->assertForbidden();
    }
}
```

---

## ✅ Acceptance Criteria

### Functionality
- [ ] 列表頁面顯示所有待審核申請
- [ ] 只顯示 `salesperson_status = 'pending'` 的申請
- [ ] 姓名、Email、電話可搜尋
- [ ] 可按申請時間排序
- [ ] 日期範圍篩選正常運作
- [ ] 等待時間篩選正常運作
- [ ] 公司篩選正常運作

### Actions
- [ ] 查看 Action 正常運作
- [ ] 批准 Action 成功更新狀態
- [ ] 拒絕 Action 需要填寫原因
- [ ] 批量批准功能正常
- [ ] 批准/拒絕後顯示成功通知
- [ ] 批准/拒絕後自動刷新列表

### View Page
- [ ] 詳情頁面顯示完整資訊
- [ ] 基本資料正確顯示
- [ ] 業務員資訊正確顯示
- [ ] 聯絡方式正確顯示
- [ ] 審核狀態正確顯示
- [ ] Header Actions 正常運作

### UI/UX
- [ ] Navigation badge 顯示待審核數量
- [ ] Badge 顏色根據數量變化 (0=綠, >10=紅)
- [ ] 列表每 30 秒自動刷新
- [ ] Copyable 欄位可一鍵複製
- [ ] 等待時間 Badge 顏色正確 (<24h=綠, <72h=黃, >72h=紅)

### Permissions
- [ ] 需要 `view_any_salesperson::application` 權限才能訪問列表
- [ ] 需要 `approve_salesperson::application` 權限才能批准
- [ ] 需要 `reject_salesperson::application` 權限才能拒絕
- [ ] 需要 `bulk_approve_salesperson::application` 權限才能批量批准

### Performance
- [ ] 使用 Eager Loading (無 N+1 Query)
- [ ] 列表載入時間 < 500ms
- [ ] 操作回應時間 < 300ms
- [ ] 資料庫查詢數 < 15 queries/page

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
